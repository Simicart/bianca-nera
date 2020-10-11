<?php

/**
 * Created by PhpStorm.
 * User: trueplus
 * Date: 4/19/16
 * Time: 08:52
 */

namespace Simi\Simicustomize\Observer;

use Magento\Framework\Event\ObserverInterface;

class SimiconnectorModelServerInitialize implements ObserverInterface {

    public $simiObjectManager;
    public $helper;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $simiObjectManager,
        \Simi\Simicustomize\Helper\Data $helper
    ) {
        $this->simiObjectManager = $simiObjectManager;
        $this->helper = $helper;
    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $object = $observer->getObject();
        $data = $object->getData();
        $className = 'Simi\Simicustomize\Model\Api\\' . ucfirst($data['resource']);
        if (class_exists($className)) {
            $data['module'] = "simicustomize";
            $object->setData($data);
        }

        $this->helper->convertBearerToCustomerLoggedIn();
    }

}
