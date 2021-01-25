<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class Customer extends AbstractModel
{
    const DIR_WEB_TO_OCEAN = 'website_to_ocean';
    const DIR_OCEAN_TO_WEB = 'ocean_to_website';

    public function __construct(
        Context $context,
        Registry $registry
    ){
        parent::__construct($context, $registry);
    }

    protected function _construct()
    {
        $this->_init(\Simi\Simiocean\Model\ResourceModel\Customer::class);
    }
}