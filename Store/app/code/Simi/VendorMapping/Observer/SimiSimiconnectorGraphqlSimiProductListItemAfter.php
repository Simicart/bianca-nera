<?php

namespace Simi\VendorMapping\Observer;

use Magento\Framework\Event\ObserverInterface;

class SimiSimiconnectorGraphqlSimiProductListItemAfter implements ObserverInterface {

    /**
     * Add vendor_name to SimiProductListItemExtraField
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $object = $observer->getObject();
        // $extraData = $observer->getExtraData();
        if (isset($object->productExtraData['attribute_values']['vendor_id'])) {
            $vendorId = $object->productExtraData['attribute_values']['vendor_id'];
            if (class_exists('Vnecoms\Vendors\Model\Vendor') && $vendorId != 0) {
                // $vendor = \Magento\Framework\App\ObjectManager::getInstance()
                //     ->get('\Vnecoms\Vendors\Model\Vendor')
                //     ->load((int)$vendorId);
                if ($vendorId) {
                    // productExtraData
                    $vendorHelper = \Magento\Framework\App\ObjectManager::getInstance()
                        ->get('Simi\Simicustomize\Helper\Vendor');
                    $object->productExtraData['attribute_values']['vendor_name'] = $vendorHelper->getStoreName($vendorId);
                }
            }
        }
        return $this;
    }
}