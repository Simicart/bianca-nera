<?php

namespace Simi\Simicustomize\Controller\Test;

use Magento\Framework\ObjectManagerInterface;
use Mageplaza\GeoIP\Helper\Address as GeoIpAddress;
use Magento\Framework\App\Action\Context;


class Yourgeoip extends \Magento\Framework\App\Action\Action
{
    protected $objectManager;
    protected $_geoIp;

    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        GeoIpAddress $geoIp
    ) {
        parent::__construct($context);
        $this->_geoIp                 = $geoIp;
        $this->objectManager = $objectManager;
    }

    public function execute()
    {
        $geoIpData = $this->_geoIp->getGeoIpData();
        echo json_encode($geoIpData);
        exit();
    }
}
