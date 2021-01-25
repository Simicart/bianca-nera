<?php

namespace Simi\Simicustomize\Plugin;

use Magento\Store\Model\StoreManagerInterface;

class PaymentMethodList
{
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }


    public function beforeGetActiveList(\Magento\Payment\Model\PaymentMethodList $subject, $storeId)
    {
        $storeId = $this->storeManager->getStore()->getId();
		return [$storeId];
    }
}
