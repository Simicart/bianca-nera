<?php

namespace Simi\Simicustomize\Observer;

use Magento\Framework\Event\ObserverInterface;


class CustomerAddressSaveBefore implements ObserverInterface {
    public $simiObjectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $simiObjectManager
    ) {
        $this->simiObjectManager = $simiObjectManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        try {
            $customerAddress = $observer->getEvent()->getCustomerAddress();
            $extensionAttributes = $customerAddress->getExtensionAttributes();
            if ($extensionAttributes) {
                // set attribute from extension attributes
                foreach($extensionAttributes as $code => $value){
                    if ($code == 'house_number') {
                        $customerAddress->setHouseNumber($value);
                    }
                    if ($code == 'apartment_number') {
                        $customerAddress->setApartmentNumber($value);
                    }
                    if ($code == 'block') {
                        $customerAddress->setBlock($value);
                    }
                }
            }
        } catch (\Exception $e) {}
    }
}