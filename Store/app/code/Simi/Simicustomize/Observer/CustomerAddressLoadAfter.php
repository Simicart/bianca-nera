<?php

namespace Simi\Simicustomize\Observer;

use Magento\Framework\Event\ObserverInterface;


class CustomerAddressLoadAfter implements ObserverInterface {
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
            $extensData = [];
            if ($customerAddress->getHouseNumber()) {
                $extensData['house_number'] = $customerAddress->getHouseNumber();
            }
            if ($customerAddress->getApartmentNumber()) {
                $extensData['apartment_number'] = $customerAddress->getApartmentNumber();
            }
            if ($customerAddress->getBlock()) {
                $extensData['block'] = $customerAddress->getBlock();
            }
            if ($extensionAttributes) {
                $extensData = array_merge($extensionAttributes, $extensData);
            }
            $customerAddress->setExtensionAttributes($extensData);
        } catch (\Exception $e) {}
    }
}