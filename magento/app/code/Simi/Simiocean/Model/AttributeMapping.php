<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Simi\Simiocean\Helper\Config;

class AttributeMapping extends AbstractModel
{
    public function __construct(
        Context $context,
        Registry $registry,
        Config $config
    ){
        $this->config = $config;
        parent::__construct($context, $registry);
    }

    protected function _construct()
    {
        $this->_init(\Simi\Simiocean\Model\ResourceModel\AttributeMapping::class);
    }

    /**
     * @param $value Ocean attribute value
     * @param $attrCode attribute code
     * @param $typeId entity type id (4 is catalog_product)
     * @return $this
     */
    public function getByOceanValue($value, $attrCode, $typeId){
        $collection = $this->getCollection();
        $collection->addFieldToFilter('ocean_value', $value)
            ->addFieldToFilter('attribute_code', $attrCode)
            ->addFieldToFilter('type_id', $typeId);
        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }
        return $this;
    }
}