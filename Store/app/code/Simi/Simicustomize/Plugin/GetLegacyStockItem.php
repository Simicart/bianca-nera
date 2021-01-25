<?php

namespace Simi\Simicustomize\Plugin;

class GetLegacyStockItem
{
    public function aroundExecute($subject, $proceed, $sku)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $existingStockForSku = $objectManager->get('\Magento\Framework\Registry')->registry('simi_existing_stock_for_sku' . $sku);
        if ($existingStockForSku)
            return $existingStockForSku;
        $result = $proceed($sku);
        $objectManager->get('\Magento\Framework\Registry')->register('simi_existing_stock_for_sku' . $sku, $result);
        return $result;
    }
}
