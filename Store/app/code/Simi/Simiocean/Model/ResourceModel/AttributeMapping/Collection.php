<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model\ResourceModel\AttributeMapping;

use Simi\Simiocean\Model\ResourceModel\AttributeMapping as Resource;
use Simi\Simiocean\Model\AttributeMapping;

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
        $this->_init(AttributeMapping::class, Resource::class);
    }
}