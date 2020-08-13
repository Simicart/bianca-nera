<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model\Service;

use Magento\Eav\Model\Config as EavConfig;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\framework\Api\ObjectFactory;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Customer\Model\CustomerFactory;

use Simi\Simiocean\Api\Data\CustomerInterface as OceanCustomerInterface;
use Simi\Simiocean\Model\CustomerFactory as OceanCustomerFactory;
use Simi\Simiocean\Model\ResourceModel\Customer as OceanCustomerResource;


class Customer extends \Magento\Framework\Model\AbstractModel
{
    const CUSTOMER_SAVED = 'simi_ocean_customer_saved'; /** Flag customer saved by Simiocean */
    const LIMIT = 100;

    protected $helper;
    protected $config;
    /**
     * @var Simi\Simiocean\Model\SyncTable
     */
    protected $syncTable;
    protected $syncTableFactory;
    protected $syncTablePush;
    protected $syncTablePushFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface,
     */
    protected $storeManager;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var CustomerRepositoryInterface $customerRepository
     */
    protected $customerRepository;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $customerResource;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var OceanCustomerFactory
     */
    protected $oceanCustomerFactory;

    /** @var OceanCustomerResource */
    protected $oceanCustomerResource;

    /**
     * @var \Simi\Simiocean\Model\Ocean\Customer
     */
    protected $customerApi;

    /** @var Simi\Simiocean\Model\Logger */
    protected $logger;
    protected $registry;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Simi\Simiocean\Helper\Data $helper
     * @param \Simi\Simiocean\Model\Ocean\Customer $customerApi,
     * @param DataObjectHelper $dataObjectHelper,
     * @param DataObjectProcessor $dataObjectProcessor
     * @param MethodsMap $methodsMapProcessor
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Simi\Simiocean\Helper\Data $helper,
        \Simi\Simiocean\Helper\Config $config,
        \Simi\Simiocean\Model\Ocean\Customer $customerApi,
        \Simi\Simiocean\Model\SyncTable $syncTable,
        \Simi\Simiocean\Model\SyncTableFactory $syncTableFactory,
        \Simi\Simiocean\Model\SyncTablePush $syncTablePush,
        \Simi\Simiocean\Model\SyncTablePushFactory $syncTablePushFactory,
        \Simi\Simiocean\Model\Logger $logger,
        Encryptor $encryptor,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        CustomerResource $customerResource,
        CustomerFactory $customerFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        ObjectFactory $objectFactory,
        DataObjectFactory $dataObjectFactory,
        OceanCustomerFactory $oceanCustomerFactory,
        OceanCustomerResource $oceanCustomerResource
    ){
        $this->helper = $helper;
        $this->config = $config;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->customerApi = $customerApi;
        $this->encryptor = $encryptor;
        $this->customerRepository = $customerRepository;
        $this->accountManagement = $accountManagement;
        $this->customerResource = $customerResource;
        $this->customerFactory = $customerFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->objectFactory = $objectFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->syncTable = $syncTable;
        $this->syncTableFactory = $syncTableFactory;
        $this->syncTablePush = $syncTablePush;
        $this->syncTablePushFactory = $syncTablePushFactory;
        $this->oceanCustomerFactory = $oceanCustomerFactory;
        $this->oceanCustomerResource = $oceanCustomerResource;
        $this->registry = $registry;
        $registry->register('isSecureArea', true);
        parent::__construct($context, $registry);
    }

    public function process(){
        return true;
    }

    /**
     * Sync pull customer in processing
     */
    public function syncPull(){
        // Check what is next page to get
        $page = 1;
        $size = self::LIMIT;
        if ($this->config->getCustomerSyncNumber() != null) {
            $size = (int)$this->config->getCustomerSyncNumber();
        }
        $lastSyncTable = $this->syncTable->getLastSync(\Simi\Simiocean\Model\SyncTable\Type::TYPE_CUSTOMER);
        if ($lastSyncTable->getId() && $lastSyncTable->getPageNum()) {
            $page = $lastSyncTable->getPageNum() + 1; //increment 1 page
        }

        // Get customers from ocean with limited
        $isCustomerSync = false;
        $customers = $this->customerApi->getCustomers($page, $size);

        $count = count($customers);
        if ($customers && $count) {
            $datetime = gmdate('Y-m-d H:i:s');
            foreach($customers as $oceanCustomer){
                if (isset($oceanCustomer['CustomerID']) && $oceanCustomer['CustomerID']
                    && isset($oceanCustomer['MobilePhone']) && $oceanCustomer['MobilePhone']
                ) {
                    $customerModel = $this->convertCustomerData($oceanCustomer); // convert data array to product object model
                    if($customer = $this->createCustomer($customerModel)){
                        try{
                            $oceanCustomerModel = $this->oceanCustomerFactory->create();
                            $oceanCustomerModel->setCustomerId($oceanCustomer['CustomerID']);
                            $oceanCustomerModel->setFirstName(isset($oceanCustomer['FirstName']) ? $oceanCustomer['FirstName'] : null);
                            $oceanCustomerModel->setLastName(isset($oceanCustomer['LastName']) ? $oceanCustomer['LastName'] : null);
                            $oceanCustomerModel->setHomePhone(isset($oceanCustomer['HomePhone']) ? $oceanCustomer['HomePhone'] : null);
                            $oceanCustomerModel->setMobilePhone($oceanCustomer['MobilePhone']);
                            $oceanCustomerModel->setAreaCode( isset($oceanCustomer['AreaCode']) ? $oceanCustomer['AreaCode'] : null );
                            $oceanCustomerModel->setBirthDate(isset($oceanCustomer['BirthDate']) ? $oceanCustomer['BirthDate'] : null);
                            $oceanCustomerModel->setEmail(isset($oceanCustomer['Email']) ? $oceanCustomer['Email'] : null);
                            $oceanCustomerModel->setPoints(isset($oceanCustomer['Points']) ? (float)$oceanCustomer['Points'] : null);
                            $oceanCustomerModel->setCustomerSize(isset($oceanCustomer['CustomerSize']) ? $oceanCustomer['CustomerSize'] : null);
                            $oceanCustomerModel->setMCustomerId($customer->getId());
                            $oceanCustomerModel->setSyncTime($datetime);
                            $oceanCustomerModel->setCreatedAt($datetime);
                            $oceanCustomerModel->setDirection('ocean_to_website');
                            $oceanCustomerModel->setStatus(\Simi\Simiocean\Model\SyncStatus::SUCCESS);
                            $oceanCustomerModel->save();
                        }catch(\Exception $e){
                            $this->logger->debug(array(
                                'Warning! Save ocean customer failed. CustomerID: '.$oceanCustomerModel->getCustomerId(), 
                                $e->getMessage()
                            ));
                        }
                        // save customer Arab store
                        // try{
                        //     if ($this->config->getArStore() != null) {
                        //         $arStoreIds = explode(',', $this->config->getArStore());
                        //         /** @var Magento\Customer\Model\Data\AddressInterface */
                        //         $address = $this->convertAddress($customerModel, $oceanCustomer, true);
                        //         $customer->setAddresses(array($address));
                        //         $this->registry->register(self::CUSTOMER_SAVED, true, true);
                        //         foreach($arStoreIds as $storeId){
                        //             $customer->setStoreId($storeId);
                        //             $customer->save();
                        //         }
                        //     }
                        // }catch(\Exception $e){}
                        $isCustomerSync = true;
                    }
                }
            }

            if ($isCustomerSync) {
                $syncTable = $this->syncTableFactory->create(); /** @var object Simi\Simiocean\Model\SyncTable */
                $syncTable->setType(\Simi\Simiocean\Model\SyncTable\Type::TYPE_CUSTOMER)
                    ->setPageNum($page)
                    ->setPageSize($size)
                    ->setRecordNumber(count($customers))
                    ->setCreatedAt($datetime)
                    ->save();
                return true;
            }
        }
        return false;
    }

    /**
     * Sync push customer to the ocean system
     * @return boolean
     */
    public function syncPush(){
        // Check what is next page to get
        $tryNumberMax = 2; //max try times to push again
        $page = 1;
        $size = self::LIMIT;
        if ($this->config->getCustomerSyncNumber() != null) {
            $size = (int)$this->config->getCustomerSyncNumber();
        }
        $lastSyncTable = $this->syncTablePush->getLastSync(\Simi\Simiocean\Model\SyncTablePush\Type::TYPE_CUSTOMER);
        if ($lastSyncTable->getId() && $lastSyncTable->getPageNum()) {
            if ($lastSyncTable->getTryNumber() < $tryNumberMax) {
                $page = $lastSyncTable->getPageNum(); //increment 1 page
            } else {
                $page = $lastSyncTable->getPageNum() + 1; //increment 1 page
            }
        }

        // Get customers with limited
        $isCustomerSync = false;
        $searchCriteria = $this->objectFactory->create(\Magento\Framework\Api\SearchCriteriaInterface::class, []);;
        $sortOrder = new \Magento\Framework\Api\SortOrder();
        $sortOrder->setField('entity_id')->setDirection(\Magento\Framework\Api\SortOrder::SORT_ASC);
        $searchCriteria
            ->setPageSize($size)
            ->setCurrentPage($page)
            ->setSortOrders(array($sortOrder));
        $searchResult = $this->customerRepository->getList($searchCriteria);
        $customers = $searchResult->getItems();
        $datetime = gmdate('Y-m-d H:i:s');
        $isSyncedFromOcean = false;
        foreach($customers as $customer){
            if ($customer->getId() && !$this->isOceanCustomerExists($customer->getId())) {
                $debug = '';
                try{
                    $oceanCustomerModel = $this->oceanCustomerFactory->create();
                    $oceanData = $this->customerToOcean($customer);
                    // Save customer info to sync table
                    try{
                        $oceanCustomerModel->setFirstName( isset($oceanData['FirstName']) ? $oceanData['FirstName'] : null );
                        $oceanCustomerModel->setLastName( isset($oceanData['LastName']) ? $oceanData['LastName'] : null );
                        $oceanCustomerModel->setHomePhone( isset($oceanData['HomePhone']) ? $oceanData['HomePhone'] : null );
                        $oceanCustomerModel->setMobilePhone( isset($oceanData['MobilePhone']) ? $oceanData['MobilePhone'] : null );
                        $oceanCustomerModel->setAreaCode( isset($oceanData['AreaCode']) ? $oceanData['AreaCode'] : null );
                        $oceanCustomerModel->setBirthDate( isset($oceanData['BirthDate']) ? $oceanData['BirthDate'] : null );
                        $oceanCustomerModel->setEmail( isset($oceanData['Email']) ? $oceanData['Email'] : null );
                        $oceanCustomerModel->setPoints( isset($oceanData['Points']) ? (float)$oceanData['Points'] : null );
                        $oceanCustomerModel->setCustomerSize( isset($oceanData['CustomerSize']) ? $oceanData['CustomerSize'] : null );
                        $oceanCustomerModel->setMCustomerId($customer->getId());
                        $oceanCustomerModel->setSyncTime($datetime);
                        $oceanCustomerModel->setCreatedAt($datetime);
                        $oceanCustomerModel->setDirection(\Simi\Simiocean\Model\Customer::DIR_WEB_TO_OCEAN);
                        $oceanCustomerModel->setStatus(\Simi\Simiocean\Model\SyncStatus::SYNCING);
                        $oceanCustomerModel->save();
                    }catch(\Exception $e){
                        $this->logger->debug(array(
                            'Warning! Save ocean customer failed. Magento customer id: '.$oceanCustomerModel->getMCustomerId(), 
                            $e->getMessage()
                        ));
                    }
                    $result = $this->customerApi->addCustomer($oceanData);
                    $customerID = (int) $result;
                    $debug = $result;
                }catch(\Exception $e){
                    $customerID = false;
                    $debug = $e->getMessage();
                    // $oceanCustomerModel->setSyncTime($datetime);
                    $oceanCustomerModel->setStatus(\Simi\Simiocean\Model\SyncStatus::FAILED);
                    $oceanCustomerModel->setMessage($debug);
                    $oceanCustomerModel->save();
                }
                // Skipping error message by check strlen((string)$customerID) == strlen((string)$result)
                if ($customerID && (int)$customerID && strlen((string)$customerID) == strlen((string)$result)) {
                    try{
                        // save customerID to sync table
                        // $oceanCustomerModel->setSyncTime($datetime);
                        $oceanCustomerModel->setCustomerId((int)$customerID);
                        $oceanCustomerModel->setStatus(\Simi\Simiocean\Model\SyncStatus::SUCCESS);
                        $oceanCustomerModel->save();
                        $isCustomerSync = true;
                    }catch(\Exception $e){
                        $isCustomerSync = false;
                        $this->logger->debug(array(
                            'Warning! Save ocean customer failed. CustomerID: '.$oceanCustomerModel->getCustomerId(), 
                            $e->getMessage()
                        ));
                    }
                } else {
                    // var_dump($oceanData['MobilePhone'].' - '.$customer->getId());
                    // var_dump($debug);
                    if (isset($oceanData['MobilePhone']) && $oceanData['MobilePhone'] 
                        && strpos($debug, 'There is a registerd customer with this mobile number') !== false
                    ) {
                        $oceanCustomerModel->setStatus(\Simi\Simiocean\Model\SyncStatus::CONFLICT);
                        $oceanCustomerModel->save();
                    } else {
                        $oceanCustomerModel->setStatus(\Simi\Simiocean\Model\SyncStatus::FAILED);
                        $oceanCustomerModel->setMessage($debug);
                        $oceanCustomerModel->save();
                    }
                }
            } else {
                // Exists the m customer id in the sync ocean table
                $isSyncedFromOcean = true;
            }
        }
        
        // Check and go to next step
        if ($isSyncedFromOcean) {
            $syncTable = $this->syncTablePushFactory->create(); /** @var Simi\Simiocean\Model\SyncTable */
            $syncTable->setType(\Simi\Simiocean\Model\SyncTablePush\Type::TYPE_CUSTOMER)
                ->setPageNum($page)
                ->setPageSize($size)
                ->setRecordNumber(count($customers))
                ->setTryNumber($tryNumberMax)
                ->setCreatedAt($datetime)
                ->save();
            return true;
        } elseif (!$isCustomerSync && ($lastSyncTable->getId() && $lastSyncTable->getTryNumber() < $tryNumberMax)) {
            $lastSyncTable->setTryNumber((int)$lastSyncTable->getTryNumber() + 1);
            $lastSyncTable->save();
        } elseif ($isCustomerSync) {
            $syncTable = $this->syncTablePushFactory->create(); /** @var Simi\Simiocean\Model\SyncTable */
            $syncTable->setType(\Simi\Simiocean\Model\SyncTablePush\Type::TYPE_CUSTOMER)
                ->setPageNum($page)
                ->setPageSize($size)
                ->setRecordNumber(count($customers))
                ->setTryNumber($tryNumberMax)
                ->setCreatedAt($datetime)
                ->save();
            return true;
        } else {
            $syncTable = $this->syncTablePushFactory->create(); /** @var Simi\Simiocean\Model\SyncTable */
            $syncTable->setType(\Simi\Simiocean\Model\SyncTablePush\Type::TYPE_CUSTOMER)
                ->setPageNum($page)
                ->setPageSize($size)
                ->setRecordNumber(count($customers))
                ->setTryNumber(1)
                ->setCreatedAt($datetime)
                ->save();
        }

        return false;
    }

    /**
     * Get modified customer from ocean system and update it to website.
     * Limited by system config settings.
     * Paging when the list too long.
     */
    public function syncUpdateFromOcean(){
        $page = 1;
        $size = self::LIMIT;
        $lastDays = 1; // 1 day ago from now

        if ($this->config->getCustomerSyncNumber() != null) {
            $size = (int)$this->config->getCustomerSyncNumber();
        }
       
        // Get time and page number from last synced
        $timeFrom = 'now';
        $timeTo = 'now';
        $lastSyncTable = $this->syncTable->getLastSyncByTime(\Simi\Simiocean\Model\SyncTable\Type::TYPE_CUSTOMER_UPDATE);
        if ($lastSyncTable->getId() && $lastSyncTable->getPageNum()) {
            if ($lastSyncTable->getPageSize() && $lastSyncTable->getRecordNumber() >= $lastSyncTable->getPageSize()) {
                $page = $lastSyncTable->getPageNum() + 1; //increment 1 page
                $timeTo = $lastSyncTable->getUpdatedTo();
                $timeFrom = $lastSyncTable->getUpdatedFrom();
            } else {
                $timeFrom = $lastSyncTable->getUpdatedTo();
            }
        }

        // ToDate
        $dateTo = new \DateTime($timeTo, new \DateTimeZone('UTC'));
        $dateToGmt = $dateTo->format('Y-m-d H:i:s');
        $dateToParam = $dateTo->getTimestamp();

        // FromDate
        $dateFrom = new \DateTime($timeFrom, new \DateTimeZone('UTC'));
        if ($timeFrom == 'now') {
            $dateFrom->setTimestamp($dateFrom->getTimestamp() - ($lastDays * 86400));
        }
        if (($dateToParam - $dateFrom->getTimestamp()) > ($lastDays * 86400)) {
            $dateFrom->setTimestamp($dateToParam - ($lastDays * 86400));
        }
        $dateFromGmt = $dateFrom->format('Y-m-d H:i:s');
        $dateFromParam = $dateFrom->getTimestamp();

        try{
            $oCustomers = $this->customerApi->getFilterCustomers($dateFromParam, $dateToParam, $page, $size);
        }catch(\Exception $e){
            $this->logger->debug(array(
                'Error: Get ocean customers updated error. Page = '.$page.', Size = '.$size.', from = '.$dateFromGmt.', to = '.$dateToGmt, 
                $e->getMessage()
            ));
            return false;
        }
        if (is_array($oCustomers)) {
            $hasUpdate = false;
            $records = count($oCustomers);
            foreach($oCustomers as $oCustomer){
                if (isset($oCustomer['CustomerID']) && $oCustomer['CustomerID']
                    && isset($oCustomer['MobilePhone']) && $oCustomer['MobilePhone'] 
                    && isset($oCustomer['ModificationDate']) && $oCustomer['ModificationDate']
                ) {
                    $customerData = $this->convertCustomerData($oCustomer);
                    $customer = $this->getCustomerExists('', $oCustomer['MobilePhone']);
                    if (!$customer) {
                        /** 
                         * Uncomment this block if want to create new customer when ocean 
                         * update a customer but it does not existing in website 
                         * */
                        // $mobileEmail = str_replace(array('+', '-', '_', '.', ' '), '', $oCustomer['MobilePhone']);
                        // $mobileEmail = (int) $mobileEmail ? $mobileEmail : substr(md5($mobileEmail), 0, 15);
                        // $email = $mobileEmail.'@bianca-nera-ocean.com';
                        // $password = md5($oCustomer['MobilePhone'].$email);
                        // $password = substr(str_shuffle("BIANCANERA"), 0, 1).substr($password, 1, 10).'123@';
                        // $passwordHash = $this->createPasswordHash($password);
                        // $dataCustomer = $this->customerRepository->save($customerData, $passwordHash);
                        // /** @var \Magento\Customer\Model\Customer $savedCustomer */
                        // $savedCustomer = $this->customerFactory->create()->load($dataCustomer->getId());
                        // $this->saveOceanCustomer($oCustomer, $savedCustomer->getId());
                    } else {
                        $oCustomerObject = $this->getOceanCustomer($oCustomer['CustomerID']);
                        if ($oCustomerObject->getId()) {
                            $syncTime = new \DateTime($oCustomerObject->getSyncTime(), new \DateTimeZone('UTC'));
                            $modifyTime = new \DateTime(gmdate('Y-m-d H:i:s', $oCustomer['ModificationDate']), new \DateTimeZone('UTC'));
                            if ($modifyTime > $syncTime) {
                                // update customer if existed
                                $this->updateCustomer($customer, $customerData);
                                $this->saveOceanCustomer($oCustomer, $customer->getId());
                                $hasUpdate = true;
                            }
                        }
                    }
                }
            }

            $lastSyncTable->setId(null);
            $lastSyncTable->setType(\Simi\Simiocean\Model\SyncTable\Type::TYPE_CUSTOMER_UPDATE);
            $lastSyncTable->setPageNum($page);
            $lastSyncTable->setPageSize($size);
            $lastSyncTable->setRecordNumber($records);
            $lastSyncTable->setUpdatedFrom($dateFromGmt);
            $lastSyncTable->setUpdatedTo($dateToGmt);
            $lastSyncTable->setCreatedAt(gmdate('Y-m-d H:i:s'));
            $lastSyncTable->save();
            return $hasUpdate;
        } else {
            if ($lastSyncTable->getId()){
                $lastSyncTable->setRecordNumber(0);
                $lastSyncTable->save();
            }
            $this->logger->debug(array(
                'Error: Get ocean customers updated error. Page = '.$page.', Size = '.$size.', from = '.$dateFromGmt.', to = '.$dateToGmt, 
                'Server: '.$oCustomers
            ));
        }
        return false;
    }

    /**
     * Get modified customer from website system and update/create it to ocean.
     * Status pending or missing and direction website_to_ocean
     * Limited by system config settings.
     */
    public function syncUpdateFromWebsite(){
        $isSynced = false;
        $oceanCustomerCollection = $this->getPendingUpdate();
        foreach($oceanCustomerCollection as $oceanCustomer){
            if ($oceanCustomer->getMCustomerId() && $oceanCustomer->getMobilePhone()) {
                $customer = $this->customerRepository->getById($oceanCustomer->getMCustomerId());
                $isSyncedFromOcean = false;
                if ($customer && $customer->getId()) {
                    $result = '';
                    try{
                        // Save customer info to sync table
                        // $oceanCustomer->setDirection(\Simi\Simiocean\Model\Customer::DIR_WEB_TO_OCEAN);
                        // $oceanCustomer->setStatus(\Simi\Simiocean\Model\SyncStatus::SYNCING);
                        // $oceanCustomer->save();

                        $oceanData = $this->customerToOcean($customer);
                        if ($oceanCustomer->getCustomerId() && $oceanCustomer->getStatus() == \Simi\Simiocean\Model\SyncStatus::PENDING) {
                            $oceanData['CustomerID'] = $oceanCustomer->getCustomerId();
                            $oceanData['AreaCode'] = $oceanCustomer->getAreaCode();
                            // Remove phone code
                            if (isset($oceanData['MobilePhone']) && $oceanCustomer->getAreaCode() &&
                                strpos($oceanData['MobilePhone'], $oceanCustomer->getAreaCode()) === 0) {
                                $oceanData['MobilePhone'] = substr($oceanData['MobilePhone'], strlen($oceanCustomer->getAreaCode()));
                            }
                            $result = $this->customerApi->updateCustomer($oceanData);
                        } elseif ($oceanCustomer->getStatus() == \Simi\Simiocean\Model\SyncStatus::MISSING) {
                            $result = $this->customerApi->addCustomer($oceanData);
                        }
                        $customerID = (int) $result;
                    }catch(\Exception $e){
                        $customerID = false;
                        $result = $e->getMessage();
                    }

                    // Skipping error message by check strlen((string)$customerID) == strlen((string)$result)
                    if ($customerID && (int)$customerID && strlen((string)$customerID) == strlen((string)$result)) {
                        $oceanCustomer->setCustomerId($customerID);
                        $oceanCustomer->setSyncTime(gmdate('Y-m-d H:i:s'));
                        $oceanCustomer->setStatus(\Simi\Simiocean\Model\SyncStatus::SUCCESS);
                        $oceanCustomer->save();
                        $isSynced = true;
                    } else {
                        if (isset($oceanData['MobilePhone']) && $oceanData['MobilePhone'] 
                            && strpos($result, 'There is a registerd customer with this mobile number') !== false
                        ) {
                            $oceanCustomer->setStatus(\Simi\Simiocean\Model\SyncStatus::CONFLICT);
                            $oceanCustomer->save();
                        } else {
                            $oceanCustomer->setStatus(\Simi\Simiocean\Model\SyncStatus::FAILED);
                            $oceanCustomer->setMessage($result);
                            $oceanCustomer->save();
                        }
                    }
                }
            }
        }
        return $isSynced;
    }

    /**
     * Resync push failed item to ocean customer
     * Maximum customer number by config
     * Auto delete after 3 hit (failed count > 5)
     * Map customer_id, m_customer_id
     * Sync direction website_to_ocean
     */
    public function resyncPush(){
        $size = 10;
        /* if ($this->config->getCustomerSyncNumber() != null) {
            $size = (int)$this->config->getCustomerSyncNumber();
        } */
        $oCollection = $this->getFailedPush('DESC', $size); // last failed push
        $isCustomerSync = $this->resyncPushProcess($oCollection);
        $oCollection2 = $this->getFailedPush('ASC', $size); // head failed push
        $isCustomerSync2 = $this->resyncPushProcess($oCollection2);
        return $isCustomerSync && $isCustomerSync2;
    }

    protected function resyncPushProcess($oCollection){
        $isCustomerSync = true;
        $datetime = gmdate('Y-m-d H:i:s');
        foreach($oCollection as $oCustomer){
            $isCustomerSync = false;
            if ((int) $oCustomer->getHit() > 3) {
                $oCustomer->delete();
                continue;
            }
            if ($oCustomer->getMCustomerId()) {
                $customer = $this->customerRepository->getById($oCustomer->getMCustomerId());
                if ($customer->getId()) {
                    $oceanData = $this->customerToOcean($customer);
                    // correct data
                    if(!isset($oceanData['FirstName'])) $oceanData['FirstName'] = null;
                    if(!isset($oceanData['LastName'])) $oceanData['LastName'] = null;
                    if(!isset($oceanData['HomePhone'])) $oceanData['HomePhone'] = null;
                    if(!isset($oceanData['MobilePhone'])) $oceanData['MobilePhone'] = null;
                    if(!isset($oceanData['AreaCode'])) $oceanData['AreaCode'] = null;
                    if(!isset($oceanData['BirthDate'])) $oceanData['BirthDate'] = null;
                    if(!isset($oceanData['Email'])) $oceanData['Email'] = null;

                    if (!$oceanData['MobilePhone']) continue; // not allowed missing phone number

                    $oCustomer->setMCustomerId($customer->getId());
                    $oCustomer->setFirstName($oceanData['FirstName']);
                    $oCustomer->setLastName($oceanData['LastName']);
                    $oCustomer->setMobilePhone($oceanData['MobilePhone']);
                    $oCustomer->setAreaCode($oceanData['AreaCode']);
                    $oCustomer->setBirthDate($oceanData['BirthDate']);
                    $oCustomer->setEmail($oceanData['Email']);
                    $oCustomer->setDirection(\Simi\Simiocean\Model\Customer::DIR_WEB_TO_OCEAN);
                    $oCustomer->setSyncTime($datetime);
                    $oCustomer->setHit((int) $oCustomer->getHit() + 1);
                    
                    $customerID = false;
                    try{
                        $result = $this->customerApi->addCustomer($oceanData);
                        $customerID = (int) $result;
                        $debug = $result;
                    }catch(\Exception $e){
                        $this->logger->debug(array(
                            'Error! Save ocean customer failed. Magento customer id: '.$oCustomer->getMCustomerId(), 
                            'Ocean Data: '.json_encode($oceanData),
                            $e->getMessage()
                        ));
                        $debug = $e->getMessage();
                    }
                    if ($customerID !== false && (int)$customerID && strlen((string)$customerID) == strlen((string)$result)) {
                        // save ocean customer id to table
                        try{
                            // save customerID to sync table
                            $oCustomer->setSyncTime($datetime);
                            $oCustomer->setCustomerId((int)$customerID);
                            $oCustomer->setStatus(\Simi\Simiocean\Model\SyncStatus::SUCCESS);
                            $oCustomer->save();
                            $isCustomerSync = true;
                        }catch(\Exception $e){
                            $debug = $e->getMessage();
                            $this->logger->debug(array(
                                'Warning! Save ocean customer failed. CustomerID: '.$oceanCustomerModel->getCustomerId(), 
                                $debug
                            ));
                            if ((string) strpos($debug, 'Unique constraint violation found') !== false) {
                                $oCustomerExists = $this->oceanCustomerFactory->create()->load($customerID, 'customer_id');
                                if ($oCustomerExists->getId()) {
                                    $oCustomerExists->delete();
                                    $oCustomer->save(); // save again
                                }
                            }
                        }
                    } elseif (isset($oceanData['MobilePhone']) && $oceanData['MobilePhone'] 
                        && strpos($debug, 'There is a registerd customer with this mobile number') !== false
                    ) {
                        try {
                            $oceanDataExists = $this->customerApi->getCustomer($oceanData['MobilePhone']);
                            $oceanData = array_merge($oceanDataExists, $oceanData);
                            $oceanData['CustomerID'] = $oceanDataExists['CustomerID'];
                            $oceanData['FirstName'] = substr((string) $oceanData['FirstName'], 0, 30);
                            $oceanData['LastName'] = substr((string) $oceanData['LastName'], 0, 30);
                            $result = $this->customerApi->updateCustomer($oceanData); // update exist customer
                            $customerID = (int) $result;
                            $debug = $result;
                        } catch (\Exception $e){
                            $this->logger->debug(array(
                                'Error! Update failed customer: '.$oCustomer->getMCustomerId(), 
                                'Ocean Data: '.json_encode($oceanData),
                                $e->getMessage()
                            ));
                            $debug = $e->getMessage();
                        }
                        if ($customerID !== false && (int)$customerID && strlen((string)$customerID) == strlen((string)$result)) {
                            try{
                                $oCustomer->setSyncTime($datetime);
                                $oCustomer->setCustomerId((int)$customerID);
                                $oCustomer->setStatus(\Simi\Simiocean\Model\SyncStatus::SUCCESS);
                                $oCustomer->save();
                                $isCustomerSync = true;
                            }catch(\Exception $e){
                                $debug = $e->getMessage();
                                if ((string) strpos($debug, 'Unique constraint violation found') !== false) {
                                    $oCustomerExists = $this->oceanCustomerFactory->create()->load($customerID, 'customer_id');
                                    if ($oCustomerExists->getId()) {
                                        $oCustomerExists->delete();
                                        $oCustomer->save(); // save again
                                    }
                                }
                            }
                        } else {
                            $oCustomer->setMessage($debug);
                            $oCustomer->save();
                        }
                    } else {
                        $oCustomer->setMessage($debug);
                        $oCustomer->save();
                    }
                }
            } else {
                $oCustomer->delete(); // delete failed no m_customer_id
            }
        }
        return $isCustomerSync;
    }

    /**
     * Convert customer data to ocean
     * @param \Magento\Customer\Api\Data\CustomerInterface $customerData
     * @return array
     */
    protected function customerToOcean(\Magento\Customer\Api\Data\CustomerInterface $customerData){
        $date = new \DateTime($customerData->getDob(), new \DateTimeZone('UTC'));
        $date2 = new \DateTime('0001-01-01', new \DateTimeZone('UTC')); //0001-01-01T00:00:00+00:00
        $birthDate = ($date < $date2) ? $date->format('Y-m-d\TH:i:s') : $date2->format('Y-m-d\TH:i:s');

        $address = $this->objectFactory->create(AddressInterface::class, []);
        $addresses = $customerData->getAddresses();
        if ($addresses && count($addresses)) {
            $address = $addresses[0]; // get first address item
        }
        $street = $address->getStreet();

        $mobilePhone = $customerData->getCustomAttribute('mobilenumber');
        $mobilePhone = $mobilePhone ? $mobilePhone->getValue() : null;
        $branchEnNames = $customerData->getCustomAttribute('branch_en_names');
        $branchEnNames = $branchEnNames ? $branchEnNames->getValue() : null;
        $branchArNames = $customerData->getCustomAttribute('branch_ar_names');
        $branchArNames = $branchArNames ? $branchArNames->getValue() : null;

        $data = array(
            'FirstName' => $customerData->getFirstname(),
            'LastName' => $customerData->getLastname(),
            'MobilePhone' => $mobilePhone,
            'BirthDate' => $birthDate,
            'Email' => $customerData->getEmail(),
            'StreetEnName' => is_array($street) && !empty($street) ? $street[0] : null,
            'StateEnName' => $address->getCity(),
            'AreaCode' => 0, // AreaCode must replace with ocean AreaCode, the customer from magento has code 0
        );

        if ($branchEnNames) $data['BranchEnNames'] = $branchEnNames;
        if ($branchArNames) $data['BranchArNames'] = $branchArNames;

        return $data;
    }

    /**
     * Convert to magento customer data model from ocean raw data array
     * @return CustomerInterface
     */
    public function convertCustomerData($data, $storeId = 0, $isArStore = false){
        $dataObject = $this->objectFactory->create(OceanCustomerInterface::class, []);
        $dataObject->setData($data);
        // Convert to OceanCustomer data array
        $dataOcean = $this->dataObjectProcessor->buildOutputDataArray($dataObject, OceanCustomerInterface::class);
        $dataOcean = array_merge_recursive($dataOcean, $data);

        /** @var \Magento\Customer\Model\Data\Customer */
        $customerModel = $this->objectFactory->create(CustomerInterface::class, []);
        $this->dataObjectHelper->populateWithArray($customerModel, $dataOcean, CustomerInterface::class);

        if (!$customerModel->getFirstname() && $customerModel->getLastname()) {
            $customerModel->setFirstname($customerModel->getLastname());
            $customerModel->setLastname('');
        }
        if (!$customerModel->getLastname()) {
            $cusname = explode(' ', $customerModel->getFirstname());
            $firstname = array_shift($cusname);
            $customerModel->setFirstname($firstname);
            $customerModel->setLastname(isset($cusname[0]) ? implode(' ', $cusname) : $firstname);
        }

        /** @var Magento\Customer\Model\Data\AddressInterface */
        $address = $this->convertAddress($customerModel, $dataObject, $isArStore);
        $customerModel->setAddresses(array($address));

        if ($dataObject->getData('AreaCode')) {
            $customerModel->setCustomAttribute('mobilenumber', $dataObject->getData('AreaCode') . $dataObject->getPhone());
        } else {
            $customerModel->setCustomAttribute('mobilenumber', $dataObject->getPhone());
        }
        $customerModel->setCustomAttribute('branch_en_names', $dataObject->getData('BranchEnNames'));
        $customerModel->setCustomAttribute('branch_ar_names', $dataObject->getData('BranchArNames'));

        if ($customerModel->getDob()) {
            $date = new \DateTime($customerModel->getDob(), new \DateTimeZone('UTC')); // Not Asia/Kuwait skip date
            $customerModel->setDob(gmdate('Y-m-d H:i:s', $date->getTimestamp()));
        }

        if ($storeId) {
            $customerModel->setStoreId($storeId);
        } else {
            $store = $this->storeManager->getWebsite()->getDefaultStore();
            $customerModel->setWebsiteId($store->getWebsiteId());
        }
        return $customerModel;
    }

    /**
     * Convert address from Ocean address
     * @param object $customerModel
     * @param mixed $oceanData
     * @return Magento\Customer\Model\Data\Address
     */
    protected function convertAddress($customerModel, $oceanData, $isArStore = false){
        if (is_array($oceanData)) {
            $dataObject = $this->objectFactory->create(OceanCustomerInterface::class, []);
            $dataObject->setData($oceanData);
        } else {
            $dataObject = $oceanData;
        }
        $address = $this->objectFactory->create(AddressInterface::class, []);
        // $address->setIsDefaultShipping(true);
        // $address->setIsDefaultBilling(true);
        $address->setFirstname($customerModel->getFirstname());
        $address->setLastname($customerModel->getLastname());
        // $address->setPostcode($dataObject->getData('AreaCode'));
        $streetName = $isArStore && $dataObject->getData('StreetArName') ? 
            $dataObject->getData('StreetArName') : $dataObject->getData('StreetEnName') ?: 'NA';
        $address->setStreet(array($streetName));
        $stateName = $isArStore && $dataObject->getData('StateArName') ? 
            $dataObject->getData('StateArName') : $dataObject->getData('StateEnName') ?: 'NA';
        $address->setCity($stateName);

        if ($dataObject->getData('AreaCode')) {
            $address->setTelephone($dataObject->getData('AreaCode') . $dataObject->getPhone());
        } else {
            $address->setTelephone($dataObject->getPhone());
        }

        // $address->setCountryId($dataObject->getData('NationalityID'));
        $address->setCountryId('KW');
        if ($dataObject->getData('StateID') && $dataObject->getData('StateArName')) {
            $region = $this->objectFactory->create(\Magento\Customer\Api\Data\RegionInterface::class, []);
            $stateArName = $dataObject->getData('StateArName');
            $region->setRegion($stateArName);
            // $region->setRegionId($dataObject->getData('StateID'));
            // $region->setRegionCode($dataObject->getData('StateID'));
            $address->setRegion($region);
        }
        return $address;
    }

    /**
     * Add new customer to magento
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return \Magento\Customer\Model\Customer|false
     */
    protected function createCustomer($customer){
        $createdAt = gmdate("Y-m-d H:i:s");
        $customer->setCreatedAt($createdAt);
        $customer->setUpdatedAt($createdAt);
        $mobileAttr = $customer->getCustomAttribute('mobilenumber');
        $mobile = $mobileAttr ? $mobileAttr->getValue() : '';
        if (!$customer->getEmail()) {
            $mobileEmail = str_replace(array('+', '-', '_', '.', ' '), '', $mobile);
            $mobileEmail = (int) $mobileEmail ? $mobileEmail : substr(md5($mobileEmail), 0, 15);
            $customer->setEmail($mobileEmail.'@bianca-nera-ocean.com');
        }
        try{
            $customer->setData('is_ocean', 1); // If this attribute has created in database
            if (!$savedCustomer = $this->getCustomerExists($customer->getEmail(), $mobile)) {
                $password = md5($mobile.$customer->getEmail());
                $password = substr(str_shuffle("BIANCANERA"), 0, 1).substr($password, 1, 10).'123@';
                $passwordHash = $this->createPasswordHash($password);
                $this->registry->register(self::CUSTOMER_SAVED, true, true);
                $dataCustomer = $this->customerRepository->save($customer, $passwordHash);
                /** @var \Magento\Customer\Model\Customer $savedCustomer */
                $savedCustomer = $this->customerFactory->create()->load($dataCustomer->getId());
            } else {
                //TODO: If override old customer then write code here
            }
            return $savedCustomer;
        } catch(\Exception $e) {
            $this->logger->debug(array('Sync pull: Save customer error '.$e->getMessage(), 'Email: '.$customer->getEmail(), 'Phone: '.$mobile));
            return false;
        }
        return false;
    }

    /**
     * Update customer existed in website
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Customer\Api\Data\CustomerInterface $customerData
     * @return boolean
     */
    protected function updateCustomer(\Magento\Customer\Model\Customer $customer, $customerData = null){
        try{
            if (is_object($customerData)) {
                $dataArray = $this->dataObjectProcessor->buildOutputDataArray($customerData, \Magento\Customer\Api\Data\CustomerInterface::class);
                $this->dataObjectHelper->populateWithArray($customer, $dataArray, \Magento\Customer\Api\Data\CustomerInterface::class);
            }
            $this->registry->register(self::CUSTOMER_SAVED, true, true);
            $customer->save();
            return true;
        }catch(\Exception $e){
            return false;
        }
        return false;
    }

    /**
     * Check customer exists by email or phone
     * @param string $email
     * @param string $phone
     * @return \Magento\Customer\Model\Customer|false
     */
    protected function getCustomerExists($email, $phone = ''){
        try {
            $store = $this->storeManager->getWebsite()->getDefaultStore();
            if ($phone) {
                $customer = $this->customerFactory->create();
                $connection = $this->customerResource->getConnection();
                $select = $customer->getCollection()
                    ->addAttributeToFilter('mobilenumber', $phone) // see module Magecomp_Mobilelogin if exists
                    ->getSelect();
                $bind = array('website_id' => (int)$store->getWebsiteId());
                $select->where('website_id = :website_id');
                $customerId = $connection->fetchOne($select, $bind);
                if ($customerId) {
                    $customer->load($customerId);
                }
            }
            elseif ($email) {
                $customer = $this->customerFactory->create();
                $customer->setWebsiteId($store->getWebsiteId());
                $customer->getResource()->loadByEmail($customer, $email);
            }
            if ($customer && $customer->getEntityId()) {
                return $customer;
            }
        } catch (\Exception $e){
            return false;
        }
        return false;
    }

    

    /**
     * @param int $mCustomerId id of magento customer entity
     * @return boolean
     */
    protected function isOceanCustomerExists($mCustomerId){
        $connection = $this->oceanCustomerResource->getConnection();
        $bind = ['customer_id' => $mCustomerId];
        $select = $connection->select()
            ->from($this->oceanCustomerResource->getTable('simiocean_customer'), 'customer_id')
            ->where('m_customer_id = :customer_id')
            ->where('customer_id IS NOT NULL');
        if ($connection->fetchOne($select, $bind)) {
            return true;
        }
        return false;
    }

    /**
     * Get ocean customer existed in synced
     * @param string $customerId
     * @return \Simi\Simiocean\Model\Customer|null
     */
    protected function getOceanCustomer($customerId){
        $model = $this->oceanCustomerFactory->create();
        $this->oceanCustomerResource->load($model, $customerId, 'customer_id');
        if ($model) {
            return $model;
        }
        return null;
    }

    /**
     * Get ocean customer pending update from website to ocean (status pending or missing)
     * @return \Simi\Simiocean\Model\ResourceModel\Customer\Collection
     */
    protected function getPendingUpdate(){
        $size = self::LIMIT;
        if ($this->config->getCustomerSyncNumber() != null) {
            $size = (int)$this->config->getCustomerSyncNumber();
        }
        $model = $this->oceanCustomerFactory->create();
        $collection = $model->getCollection();
        $collection->addFieldToFilter('status', array('in' => array(
                \Simi\Simiocean\Model\SyncStatus::PENDING, 
                \Simi\Simiocean\Model\SyncStatus::MISSING
            )))
            ->addFieldToFilter('direction', \Simi\Simiocean\Model\Customer::DIR_WEB_TO_OCEAN)
            ->getSelect()
            // ->where('customer_id IS NOT NULL')
            ->order('sync_time asc')
            ->limit($size);
        return $collection;
    }

    /**
     * Get ocean customer failed push from website to ocean (status failed)
     * @return \Simi\Simiocean\Model\ResourceModel\Customer\Collection
     */
    protected function getFailedPush($order = 'ASC', $limit = 10){
        $model = $this->oceanCustomerFactory->create();
        $collection = $model->getCollection();
        $collection->addFieldToFilter('status', \Simi\Simiocean\Model\SyncStatus::FAILED)
            ->addFieldToFilter('direction', \Simi\Simiocean\Model\Customer::DIR_WEB_TO_OCEAN)
            ->getSelect()
            ->where('mobile_phone IS NOT NULL') // ocean required phone number
            ->order("id $order")
            ->limit($limit);
        return $collection;
    }

    /**
     * Create or update ocean customer data to ocean customer sync table
     * @param array $oCustomer
     * @param int $mCustomerId of magento customer
     * @param string $status
     * @param string $direction one of ocean_to_website, website_to_ocean
     * @return boolean
     */
    public function saveOceanCustomer($oCustomer, $mCustomerId, $status = \Simi\Simiocean\Model\SyncStatus::SUCCESS, $direction = 'ocean_to_website'){
        $object = $this->dataObjectFactory->create();
        $object->setData($oCustomer);
        try{
            if ($oCustomerObject = $this->getOceanCustomer($object->getCustomerID())) {
                $datetime = gmdate('Y-m-d H:i:s');
                $oCustomerObject->setCustomerId($object->getCustomerID());
                $oCustomerObject->setFirstName($object->getFirstName());
                $oCustomerObject->setLastName($object->getLastName());
                $oCustomerObject->setHomePhone($object->getHomePhone());
                $oCustomerObject->setMobilePhone($object->getMobilePhone());
                $oCustomerObject->setAreaCode($object->getAreaCode());
                $oCustomerObject->setBirthDate($object->getBirthDate());
                $oCustomerObject->setEmail($object->getEmail());
                $oCustomerObject->setPoints((float)$object->getPoints());
                $oCustomerObject->setCustomerSize($object->getCustomerSize());
                $oCustomerObject->setMCustomerId($mCustomerId);
                $oCustomerObject->setSyncTime($datetime);
                if ($oCustomerObject->getId()) {
                    $oCustomerObject->setCreatedAt($datetime);
                }
                $oCustomerObject->setDirection($direction);
                $oCustomerObject->setStatus($status);
                $oCustomerObject->save();
            }
        }catch(\Exception $e){
            $this->logger->debug(array(
                'Warning! Save ocean customer failed. CustomerID: '.$object->getCustomerID(), 
                $e->getMessage()
            ));
        }
        return false;
    }

    /**
     * Get Ocean Customer synced from table simiocean_customer synced by customer_id
     * For now, the customer_id column is not unique
     * @param int $customerId of magento customer
     * @return \Simi\Simiocean\Model\Customer|null
     */
    /* protected function getOceanCustomerByMcustomerId($customerId){
        $model = $this->oceanCustomerFactory->create();
        $this->oceanCustomerResource->load($model, $customerId, 'm_customer_id');
        if ($model && $model->getId()) {
            return $model;
        }
        return null;
    } */

    /**
     * Create a hash for the given password
     *
     * @param string $password
     * @return string
     */
    protected function createPasswordHash($password)
    {
        return $this->encryptor->getHash($password, true);
    }
}