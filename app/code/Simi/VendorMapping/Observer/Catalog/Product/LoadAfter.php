<?php

namespace Simi\VendorMapping\Observer\Catalog\Product;

class LoadAfter implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        if (!$product->getVendorId()) {
            $product->setVendorId('default');
        }
    }
}
