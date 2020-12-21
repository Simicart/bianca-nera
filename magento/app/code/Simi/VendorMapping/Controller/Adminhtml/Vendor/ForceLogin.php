<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Simi\VendorMapping\Controller\Adminhtml\Vendor;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Url;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Customer\Model\CustomerFactory;
use Vnecoms\Vendors\Model\Vendor;

/**
 * Vendor login
 */
class ForceLogin extends Action
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Url
     */
    protected $url;

    protected $storeManager;

    protected $vendor;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        Context $context,
        Url $url,
        StoreManagerInterface $storeManager,
        CustomerTokenServiceInterface $customerTokenService,
        TokenFactory $tokenFactory,
        CustomerFactory $customerFactory,
        Vendor $vendor
    ) {
        $this->url = $url;
        $this->storeManager = $storeManager;
        $this->customerTokenService = $customerTokenService;
        $this->tokenFactory = $tokenFactory;
        $this->customerFactory = $customerFactory;
        $this->vendor = $vendor;

        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute(){
        // $vendorId = $this->getRequest()->getParam('id');
        // $vendor = $this->vendor->load($vendorId);

        // if (!$vendor || !$vendor->getId()) {
        //     $this->messageManager->addErrorMessage(__('Seller does not exist.'));
        //     return $this->_redirect('/');
        // }

        $customerId = $this->getRequest()->getParam('id');
        $customer = $this->customerFactory->create()->load($customerId);
        // $customer = $vendor->getCustomer();
        if (!$customer || !$customer->getId()) {
            $this->messageManager->addErrorMessage(__('Customer does not exist.'));
            return $this->_redirect('/');
        }

        $token = $this->tokenFactory->create()->createCustomerToken($customer->getId())->getToken();
        // $token = $this->customerTokenService->createCustomerAccessToken($customer->getEmail(), $customer->getPassword());
        $store = $this->storeManager->getDefaultStoreView();
        $loginUrl = $this->url->setScope($store)
            ->getUrl('simivendor/login/vendor', ['key' => $token, '_nosid' => true]);
        return $this->getResponse()->setRedirect($loginUrl);
    }
}
