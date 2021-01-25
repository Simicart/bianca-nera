<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model\ResourceModel\Customer;

use Simi\Simiocean\Model\Customer;
use Simi\Simiocean\Model\ResourceModel\Customer as CustomerResource;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Customer::class, CustomerResource::class);
    }
}