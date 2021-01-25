<?php

namespace Simi\VendorMapping\Config\Backend;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Model\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\ResourceModel\AbstractResource;

class HomeVendors extends ConfigValue
{
    protected $serializer;

    public function __construct(
        SerializerInterface $serializer,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->serializer = $serializer;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }
    public function beforeSave()
    {
        $value = $this->getValue();
        unset($value['__empty']);
        $encoded = $this->serializer->serialize($value);
        $this->setValue($encoded);
    }
    protected function _afterLoad()
    {
        $value = $this->getValue();
        if ($value) {
            $decoded = $this->serializer->unserialize($value);
            $this->setValue($decoded);
        }
    }
}
