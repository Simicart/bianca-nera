<?php

namespace Simi\Simiocean\Controller\Customer;

use Magento\Framework\App\Action\Context;

class Updatepush extends \Magento\Framework\App\Action\Action
{
    protected $helper;
    protected $serviceCustomer;

    public function __construct(
        Context $context,
        \Simi\Simiocean\Helper\Data $helper,
        \Simi\Simiocean\Model\Service\Customer $serviceCustomer
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->serviceCustomer = $serviceCustomer;
        $this->objectManager = $context->getObjectManager();
    }

    public function execute()
    {
        // echo '<pre>';
        $data = $this->serviceCustomer->syncUpdateFromWebsite();
        var_dump($data);
        exit;
    }
}
