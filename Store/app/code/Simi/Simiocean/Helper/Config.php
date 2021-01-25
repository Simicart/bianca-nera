<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_SYNC_ENABLED   = 'simiocean/general/enabled';
    const CONFIG_AR_STORE       = 'simiocean/general/arstore';

    const CONFIG_OPTIONS_PRODUCT_NUMBER = 'simiocean/options/product_number';
    const CONFIG_OPTIONS_CUSTOMER_NUMBER = 'simiocean/options/customer_number';

    /*
     * Magento\Framework\App\Config\ScopeConfig
     */
    public $scopeConfig;

    protected $isSyncEnabled;
    protected $arStore;
    protected $isDebug;
    protected $productSynNumber;
    protected $customerSynNumber;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get config value with config path.
     */
    public function getValue($path){
        return $this->scopeConfig->getValue($path);
    }

    /**
     * Get is sync enabled in config
     */
    public function isSyncEnabled(){
        if ($this->isSyncEnabled === null) {
            $this->isSyncEnabled = $this->scopeConfig->getValue(self::CONFIG_SYNC_ENABLED);
        }
        return $this->isSyncEnabled;
    }

    /**
     * Get store id for Arabic language
     */
    public function getArStore(){
        if (!$this->arStore) {
            $this->arStore = $this->scopeConfig->getValue(self::CONFIG_AR_STORE);
        }
        return $this->arStore;
    }

    /**
     * Check debug status
     */
    public function isDebugOn(){
        if (!$this->isDebug) {
            $this->isDebug = $this->scopeConfig->getValue(\Simi\Simiocean\Model\Logger::CONF_DEBUG);
        }
        return $this->isDebug;
    }
    
    /**
     * Get the product sync number
     * @return int
     */
    public function getProductSyncNumber(){
        if (!$this->productSynNumber) {
            $this->productSynNumber = $this->getValue(self::CONFIG_OPTIONS_PRODUCT_NUMBER);
        }
        return $this->productSynNumber;
    }

    /**
     * Get the customer sync number
     * @return int
     */
    public function getCustomerSyncNumber(){
        if (!$this->customerSynNumber) {
            $this->customerSynNumber = $this->getValue(self::CONFIG_OPTIONS_CUSTOMER_NUMBER);
        }
        return $this->customerSynNumber;
    }
}