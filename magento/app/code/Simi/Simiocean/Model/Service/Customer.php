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
    const LIMIT = 100;

    protected $helper;
    protected $config;
    /**
     * @var Simi\Simiocean\Model\SyncTable
     */
    protected $syncTable;
    protected $syncTableFactory;

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
        $this->oceanCustomerFactory = $oceanCustomerFactory;
        $this->oceanCustomerResource = $oceanCustomerResource;
        $registry->register('isSecureArea', true);
        parent::__construct($context, $registry);
    }

    /**
     * Sync pull products in processing
     */
    public function process(){
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
                        $oceanCustomerModel = $this->oceanCustomerFactory->create();
                        $oceanCustomerModel->setCustomerId($oceanCustomer['CustomerID']);
                        $oceanCustomerModel->setFirstName($oceanCustomer['FirstName']);
                        $oceanCustomerModel->setLastName($oceanCustomer['LastName']);
                        $oceanCustomerModel->setHomePhone($oceanCustomer['HomePhone']);
                        $oceanCustomerModel->setMobilePhone($oceanCustomer['MobilePhone']);
                        $oceanCustomerModel->setBirthDate($oceanCustomer['BirthDate']);
                        $oceanCustomerModel->setEmail($oceanCustomer['Email']);
                        $oceanCustomerModel->setPoints((float)$oceanCustomer['Points']);
                        $oceanCustomerModel->setCustomerSize($oceanCustomer['CustomerSize']);
                        $oceanCustomerModel->setMCustomerId($customer->getId());
                        $oceanCustomerModel->setSyncTime($datetime);
                        $oceanCustomerModel->setCreatedAt($datetime);
                        try{
                            $oceanCustomerModel->save();
                        }catch(\Exception $e){
                            $this->logger->debug(array(
                                'Warning! Save ocean customer failed. CustomerID: '.$oceanCustomerModel->getCustomerId(), 
                                $e->getMessage()
                            ));
                        }
                        // save customer Arab store
                        try{
                            if ($this->config->getArStore() != null) {
                                $arStoreId = $this->config->getArStore();
                                if ($arStoreId) {
                                    $customer->setStoreId($arStoreId);
                                }
                                /** @var Magento\Customer\Model\Data\AddressInterface */
                                $address = $this->convertAddress($customerModel, $oceanCustomer, true);
                                $customer->setAddresses(array($address));
                                $customer->save();
                            }
                        }catch(\Exception $e){}
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
     * Get Ocean Customer synced from table simiocean_customer synced by customer_id
     * For now, the customer_id column is not unique
     * @param int $customerId of magento customer
     * @return \Simi\Simiocean\Model\Customer|null
     */
    /* public function getOceanCustomerSynced($customerId){
        $model = $this->oceanCustomerFactory->create();
        $this->oceanCustomerResource->load($model, $customerId, 'customer_id');
        if ($model && $model->getId()) {
            return $model;
        }
        return null;
    } */

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

        $customerModel->setCustomAttribute('mobilenumber', $dataObject->getPhone());

        if ($isArStore) {
            $branchArNames = $dataObject->getData('BranchArNames');
            $customerModel->setData('branch', $branchArNames ? $branchArNames : $dataObject->getData('BranchEnNames'));
        }

        if ($customerModel->getDob()) {
            $date = new \DateTime($customerModel->getDob(), new \DateTimeZone('Asia/Kuwait'));
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
        $address->setTelephone($dataObject->getPhone());
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
            $customer->setEmail($mobile.'@bianca-nera-ocean.com');
        }
        try{
            $customer->setData('is_ocean', 1); // If this attribute has created in database
            if (!$savedCustomer = $this->getCustomerExists($customer->getEmail(), $mobile)) {
                $password = md5($mobile.$customer->getEmail());
                $password = substr(str_shuffle("BIANCANERA"), 0, 1).substr($password, 1, 10).'123@';
                // $dataCustomer = $this->accountManagement->createAccount($customer, $password);
                $passwordHash = $this->createPasswordHash($password);
                $dataCustomer = $this->customerRepository->save($customer, $passwordHash);
                /** @var \Magento\Customer\Model\Customer $savedCustomer */
                $savedCustomer = $this->customerFactory->create()->load($dataCustomer->getId());
            } else {
                //TODO: If override old customer then write code here
            }
            return $savedCustomer;
        } catch(\Exception $e) {
            $this->logger->debug(array('Save customer error: '.$e->getMessage(), 'CustomerID: '.$customer->getEmail(), 'Phone: '.$mobile));
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