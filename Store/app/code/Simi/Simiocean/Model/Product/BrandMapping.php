<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model\Product;

class BrandMapping extends OptionMapping
{
    /**
     * Simi\Simiocean\Helper\Data
     */
    protected $helper;

    /**
     * Get the id of brand matching in system by brand name or create a new one
     * @return int|null
     */
    public function getMatchingBrand($brandId, $brandName, $brandArName = ''){
        if ($brandId && $brandName) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $attributeMapping = $objectManager->get('Simi\Simiocean\Model\AttributeMapping');
            $attributeMapping = $attributeMapping->getByOceanValue($brandId, 'brand', 4);

            $optionId = '';
            if ($attributeMapping->getId() && $attributeMapping->getOptionValue() !== null) {
                $optionId = $attributeMapping->getOptionValue();
            }

            if (!$optionId) {
                $attribute = $this->getAttribute('brand');
                $optionId = $this->getOptionIdByLabel($attribute, $brandName);
            }
            
            if (!$optionId) {
                $attribute = $this->getAttribute('brand');
                $optionId = $this->addAttributeOption($attribute, ['label' => $brandName, 'label_ar' => $brandArName]);
            }

            if ($optionId) {
                $attributeMapping->setId(null)
                    ->setTypeId(4) //catalog_product
                    ->setAttributeCode('brand')
                    ->setOptionValue($optionId)
                    ->setOceanValue($brandId)
                    ->save();
            }
                
            return $optionId;
        }
        return '';
    }
}