<?php

namespace Simi\Simiocean\Controller\Index;

use Magento\Framework\App\Action\Context;


class InvoiceCancel extends \Magento\Framework\App\Action\Action
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
        $invoiceNo = $this->objectManager->get('\Simi\Simiocean\Model\Service\Invoice')->syncCancel();
        $invoice = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Invoice')->getInvoice($invoiceNo);
        var_dump($invoice);
        exit;
    }
}
