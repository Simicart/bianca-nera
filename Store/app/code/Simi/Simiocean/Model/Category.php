<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model;

class Category extends \Magento\Framework\Model\AbstractModel
{
    const DIR_WEB_TO_OCEAN = 'website_to_ocean';
    const DIR_OCEAN_TO_WEB = 'ocean_to_website';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ){
        parent::__construct($context, $registry);
    }

    protected function _construct()
    {
        $this->_init(\Simi\Simiocean\Model\ResourceModel\Category::class);
    }
}