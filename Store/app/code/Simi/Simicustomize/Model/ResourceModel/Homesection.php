<?php

namespace Simi\Simicustomize\Model\ResourceModel;

/**
 * Simicustomize Resource Model
 */
class Homesection extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('simiconnector_homesection', 'id');
    }
}
