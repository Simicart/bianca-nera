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
    public function getMatchingBrand($brandName, $brandArName = ''){
        if ($brandName) {
            $attribute = $this->getAttribute('brand');
            if ($optionId = $this->getOptionIdByLabel($attribute, $brandName)) {
                return $optionId;
            }
            return $this->addAttributeOption($attribute, ['label' => $brandName, 'label_ar' => $brandArName]);
        }
        return '';
    }
}