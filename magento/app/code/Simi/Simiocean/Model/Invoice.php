<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model;

use Magento\Framework\Model\AbstractModel;

class Invoice extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Simi\Simiocean\Model\ResourceModel\Invoice::class);
    }
}