<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Observer;

use Magento\Framework\Event\Observer;

class SalesOrderInvoiceRefund implements \Magento\Framework\Event\ObserverInterface
{
    protected $oceanInvoiceCancelFactory;
    protected $invoiceService;

    /** @var \Simi\Simiocean\Model\Logger */
    protected $logger;

    public function __construct(
        \Simi\Simiocean\Model\InvoiceCancelFactory $oceanInvoiceCancelFactory,
        \Simi\Simiocean\Model\Service\Invoice $invoiceService,
        \Simi\Simiocean\Model\Logger $logger
    ){
        $this->oceanInvoiceCancelFactory = $oceanInvoiceCancelFactory;
        $this->invoiceService = $invoiceService;
        $this->logger              = $logger;
    }

    public function execute(Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        if ($creditmemo->getState() != \Magento\Sales\Model\Order\Creditmemo::STATE_REFUNDED) return;

        try{
            $order = $creditmemo->getOrder();
            if (!$this->invoiceService->hasOceanItem($order)) return;

            $oceanInvoice = $this->oceanInvoiceCancelFactory->create();
            $datetime = gmdate('Y-m-d H:i:s');
            $oceanInvoice->setData(
                array(
                    'invoice_id' => $creditmemo->getInvoiceId(),
                    'creditmemo_id' => $creditmemo->getId(),
                    'mcustomer_id' => $order->getCustomerId(),
                    'customer_name' => $order->getCustomerName(),
                    'items' => '',
                    'items_qty' => '',
                    'total' => $creditmemo->getBaseGrandTotal(),
                    'tax' => $creditmemo->getBaseTaxAmount(),
                    'notes' => $creditmemo->getCustomerNote(),
                    'created_at' => $datetime,
                    'direction' => 'website_to_ocean',
                    'status' => \Simi\Simiocean\Model\SyncStatus::PENDING,
                    'type' => 'cancel',
                )
            );
            $oceanInvoice->save();
        } catch (\Exception $e) {
            $this->logger->debug(array('Simiocean Observer: Invoice refund event register error.', $e->getMessage()));
        }
    }
}