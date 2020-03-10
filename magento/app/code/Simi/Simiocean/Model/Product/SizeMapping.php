<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model\Product;

class SizeMapping extends OptionMapping
{
    /**
     * Simi\Simiocean\Helper\Data
     */
    protected $helper;

    /**
     * Get the id of size matching in system by size name or create a new one
     * @return int
     */
    public function getMatchingSize($size){
        if ($size) {
            $attribute = $this->getAttribute('size');
            if ($optionId = $this->getOptionIdByLabel($attribute, $size)) {
                return $optionId;
            }
            return $this->addAttributeOption($attribute, ['label' => $size]);
        }
        return '';
    }
}