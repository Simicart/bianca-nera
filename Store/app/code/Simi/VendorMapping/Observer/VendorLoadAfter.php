<?php

namespace Simi\VendorMapping\Observer;

use Magento\Framework\Event\ObserverInterface;

class VendorLoadAfter implements ObserverInterface {

    protected $vendorHelper;

    public function __construct(
        \Simi\VendorMapping\Helper\Vendor $vendorHelper
    ){
        $this->vendorHelper = $vendorHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $vendor = $observer->getEvent()->getVendor();
        if ($vendor->getId()) {
            $vendor->setLogo($this->vendorHelper->getLogoUrl($vendor->getId()));
            $vendor->setBanner($this->vendorHelper->getBannerUrl($vendor->getId()));
        }
    }
}