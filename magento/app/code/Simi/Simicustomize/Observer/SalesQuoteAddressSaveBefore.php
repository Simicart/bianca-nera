<?php

namespace Simi\Simicustomize\Observer;

use Magento\Framework\Event\ObserverInterface;


class SalesQuoteAddressSaveBefore implements ObserverInterface {
    public $simiObjectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $simiObjectManager
    ) {
        $this->simiObjectManager = $simiObjectManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        try {
            $quoteAddress = $observer->getEvent()->getQuoteAddress();
            $extensionAttributes = $quoteAddress->getExtensionAttributes();
            if ($extensionAttributes) {
                // set attribute from extension attributes
                if (is_object($extensionAttributes)) {
                    if ($extensionAttributes->getHouseNumber() !== null) {
                        $quoteAddress->setHouseNumber($extensionAttributes->getHouseNumber());
                    }
                    if ($extensionAttributes->getApartmentNumber() !== null) {
                        $quoteAddress->setApartmentNumber($extensionAttributes->getApartmentNumber());
                    }
                    if ($extensionAttributes->getBlock() !== null) {
                        $quoteAddress->setBlock($extensionAttributes->getBlock());
                    }
                } elseif (is_array($extensionAttributes)) {
                    foreach($extensionAttributes as $code => $value){
                        if ($code == 'house_number') {
                            die('ok');
                            $quoteAddress->setHouseNumber($value);
                        }
                        if ($code == 'apartment_number') {
                            $quoteAddress->setApartmentNumber($value);
                        }
                        if ($code == 'block') {
                            $quoteAddress->setBlock($value);
                        }
                    }
                }
            }
        } catch (\Exception $e) {}
    }
}