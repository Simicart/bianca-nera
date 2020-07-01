<?php

namespace Simi\VendorMapping\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Config\Model\Config\Source\Locale\Timezone as TimezoneSource;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;

class SimiGraphqProductDetailExtraFieldAfter implements ObserverInterface {
    protected $_objectManager;

    /**
     * @var TimezoneSource
     */
    protected $timezoneSource;

     /**
     * @var MediaConfig
     */
    protected $mediaConfig;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        TimezoneSource $timezoneSource,
        MediaConfig $mediaConfig
    ) {
        $this->_objectManager = $objectManager;
        $this->timezoneSource = $timezoneSource;
        $this->mediaConfig = $mediaConfig;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        // add giftcard product timezone options
        $object = $observer->getObject();
        if (isset($object->extraFields['attribute_values']['type_id']) && $object->extraFields['attribute_values']['type_id'] == 'aw_giftcard') {
            $object->extraFields['aw_gc_timezones'] = $this->getTimezones();
            $object->extraFields['aw_gc_template_image_url_path'] = $this->mediaConfig->getTmpMediaUrl('');
        } else {
            $object->extraFields['aw_gc_timezones'] = [];
            $object->extraFields['aw_gc_template_image_url_path'] = '';
        }
        return $this;
    }

    /**
     * Retrieve timezones
     *
     * @return string[]
     */
    public function getTimezones()
    {
        return $this->timezoneSource->toOptionArray();
    }
}