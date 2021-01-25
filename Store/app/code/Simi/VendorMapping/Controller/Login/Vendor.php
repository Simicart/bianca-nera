<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Simi\VendorMapping\Controller\Login;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Integration\Helper\Oauth\Data as OauthHelper;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as Date;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Url;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Vendor login
 */
class Vendor extends Action
{
    /**
     * @var OauthHelper
     */
    private $oauthHelper;

    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Date
     */
    private $date;

    /**
     * @var SessionFactory
     */
    protected $session;

    protected $url;
    protected $storeManager;

    /**
     */
    public function __construct(
        Context $context,
        OauthHelper $oauthHelper,
        TokenFactory $tokenFactory,
        DateTime $dateTime,
        Date $date,
        SessionFactory $customerSession,
        Url $url,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->oauthHelper = $oauthHelper;
        $this->tokenFactory = $tokenFactory;
        $this->dateTime = $dateTime;
        $this->date = $date;
        $this->session = $customerSession;
        $this->url = $url;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function execute(){
        $token = $this->getRequest()->getParam('key');
        $token = $this->tokenFactory->create()->loadByToken($token);
        $tokenTtl = $this->oauthHelper->getCustomerTokenLifetime();
        if ($this->dateTime->strToTime($token->getCreatedAt()) < ($this->date->gmtTimestamp() - $tokenTtl * 3600)) {
            $this->messageManager->addError(
                __('This token is not valid.')
            );
            return true;
        }

        $store = $this->storeManager->getDefaultStoreView();
        $session = $this->session->create();

        if ($session->isLoggedIn()) {
            $session->logout();
        } 

        try {
            $session->loginById($token->getCustomerId());
            $session->regenerateId();

            $vendorUrl = $this->url->setScope($store)->getUrl('vendors/dashboard');
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setUrl($vendorUrl);
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('An unspecified error occurred. Please contact us for assistance.')
            );
        }

        return $this->getResponse()->setRedirect($store->getBaseUrl());
    }
}
