<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Simi\Simicustomize\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\StoreManagerInterface;

class SalesQuoteLoadAfter implements ObserverInterface
{

    public $storeManager;

    /**
     * @var StoreManagerInterface
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    public function execute(Observer $observer) {
        $quote = $observer->getEvent()->getQuote();
        $storeId = $this->storeManager->getStore()->getId();
        $quote->setStoreId($storeId);
    }
}
