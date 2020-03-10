<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model\Product;

class ColorMapping extends OptionMapping
{
    /**
     * Simi\Simiocean\Helper\Data
     */
    protected $helper;

    /**
     * Get the id of color matching in system by color name or create a new one
     * @return int
     */
    public function getMatchingColor($colorName, $colorArName = ''){
        if ($colorName) {
            $attribute = $this->getAttribute('color');
            if ($optionId = $this->getOptionIdByLabel($attribute, $colorName)) {
                return $optionId;
            }
            return $this->addAttributeOption($attribute, ['label' => $colorName, 'label_ar'=> $colorArName]);
        }
        return '';
    }
}