<?php

namespace Simi\Simiocean\Controller\Invoice;

use Magento\Framework\App\Action\Context;

class Push extends \Magento\Framework\App\Action\Action
{
    protected $helper;
    protected $serviceInvoice;

    public function __construct(
        Context $context,
        \Simi\Simiocean\Helper\Data $helper,
        \Simi\Simiocean\Model\Service\Invoice $serviceInvoice
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->serviceInvoice = $serviceInvoice;
        $this->objectManager = $context->getObjectManager();
    }

    public function execute()
    {
        echo '<pre>';
        $data = $this->serviceInvoice->syncPush();
        var_dump($data);
        exit;
    }
}
