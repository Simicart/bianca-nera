<?php

namespace Simi\Simiocean\Controller\Invoice;

use Magento\Framework\App\Action\Context;

class Cancel extends \Magento\Framework\App\Action\Action
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
        echo 'Cancel invoice..';
        // $data = $this->serviceInvoice->testpayment();
        $data = $this->serviceInvoice->syncCancel();
        var_dump($data);
        exit;
    }
}
