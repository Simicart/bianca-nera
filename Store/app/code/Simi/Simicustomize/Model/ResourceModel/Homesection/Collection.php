<?php

/**
 * Simicustomize Resource Collection
 */

namespace Simi\Simicustomize\Model\ResourceModel\Homesection;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Resource initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Simi\Simicustomize\Model\Homesection', 'Simi\Simicustomize\Model\ResourceModel\Homesection');
    }
    
    public function applyAPICollectionFilter($visibilityTable, $typeID, $storeID)
    {
        $this->getSelect()
                ->join(
                    ['visibility' => $visibilityTable],
                    'visibility.item_id = main_table.id AND visibility.content_type = ' . $typeID
                    . ' AND visibility.store_view_id =' . $storeID
                );
        return $this;
    }
}
