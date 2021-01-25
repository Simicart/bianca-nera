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
use Simi\Simiocean\Model\SyncTable\Type;

class SyncTable extends AbstractModel
{
    /** Object Simi\Simiocean\Helper\Config */
    protected $config;

    protected $type; // Sync Type model

    public function __construct(
        Context $context,
        Registry $registry,
        Config $config,
        Type $type
    ){
        $this->config = $config;
        $this->type = $type;
        parent::__construct($context, $registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Simi\Simiocean\Model\ResourceModel\SyncTable::class);
    }

    public function getTypeName(){
        $types = $this->type->getOption();
        return isset($types[$this->getType()]) ? $types[$this->getType()] : '';
    }

    /**
     * Get last item synced by type id
     * @param string $typeId
     * @return this object
     */
    public function getLastSync($typeId){
        $collection = $this->getCollection();
        $collection->addFieldToFilter('type', $typeId)
            ->getSelect()
            ->order('page_num desc')
            ->limit(1);
        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }
        return $this;
    }

    /**
     * Get last item synced by type id and time update to
     * @param string $typeId
     * @param string $fromTime
     * @return this object
     */
    public function getLastSyncByTime($typeId, $fromTime = ''){
        $collection = $this->getCollection();
        if ($fromTime) $collection->addFieldToFilter('updated_from', array('gteq' => $fromTime));
        $collection->addFieldToFilter('type', $typeId)
            ->getSelect()
                ->order('updated_to desc')
                ->order('page_num desc')
                ->limit(1);
        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }
        return $this;
    }

    /**
     * clear old sync log
     * @param string $typeId
     * @param string $preserve number
     * @return boolean
     */
    public function cleanLog($typeId, $preserve = 100){
        if (!$typeId) return false;
        $collection = $this->getCollection();
        $collection->addFieldToFilter('type', $typeId)
            ->getSelect()
                ->order('updated_from ASC')
                ->limit($preserve);
        if ($collection->getSize() > $preserve) {
            $deleteRecords = $collection->getSize() - $preserve;
            $i = 0;
            foreach($collection as $item){
                if ($i < $deleteRecords) {
                    $item->delete();
                } else {
                    break;
                }
                $i++;
            }
            return true;
        }
        return false;
    }
}