<?php

namespace Simi\Simiocean\Controller\Index;

use Magento\Framework\App\Action\Context;


class Decrypt extends \Magento\Framework\App\Action\Action
{
    protected $helper;
    protected $objectManager;

    public function __construct(
        Context $context,
        \Simi\Simiocean\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->objectManager = $context->getObjectManager();
    }

    public function execute()
    {
        echo '<pre>';
        $requestBody = $this->getRequest()->getContent();
        $data = $this->helper->decrypt($requestBody);
        var_dump($data);
        exit;
    }
}
