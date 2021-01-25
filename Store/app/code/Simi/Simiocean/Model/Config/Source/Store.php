<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Simi\Simiocean\Model\Config\Source;

class Store implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory
     */
    protected $_storesFactory;

    /**
     * @param \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storesFactory
     */
    public function __construct(\Magento\Store\Model\ResourceModel\Store\CollectionFactory $storesFactory)
    {
        $this->_storesFactory = $storesFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            /** @var $stores \Magento\Store\Model\ResourceModel\Store\Collection */
            $stores = $this->_storesFactory->create();
            $stores->addFieldToFilter('code', array('neq' => 'vendors'))
                ->addFieldToFilter('group_id', array('neq' => 0));
            $this->_options = $stores->load()->toOptionArray();
            array_unshift($this->_options, array('label'=> __('-- None Selected --'), 'value' => ''));
        }
        return $this->_options;
    }
}
