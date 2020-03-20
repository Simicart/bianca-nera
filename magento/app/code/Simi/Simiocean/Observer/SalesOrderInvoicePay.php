<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Observer;

use Magento\Framework\Event\Observer;

class SalesOrderInvoicePay implements \Magento\Framework\Event\ObserverInterface
{
    protected $oceanInvoiceFactory;

    /** @var \Simi\Simiocean\Model\Logger */
    protected $logger;

    public function __construct(
        \Simi\Simiocean\Model\InvoiceFactory $oceanInvoiceFactory,
        \Simi\Simiocean\Model\Logger $logger
    ){
        $this->oceanInvoiceFactory = $oceanInvoiceFactory;
        $this->logger              = $logger;
    }

    public function execute(Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        if ($invoice->getState() != \Magento\Sales\Model\Order\Invoice::STATE_PAID) return;
        $order = $invoice->getOrder();
        $items = $invoice->getItems(); /** @var \Magento\Sales\Api\Data\InvoiceItemInterface[] */
        // $itemIds = array();
        // $itemIdsQty = array();
        // foreach($items as $invoiceItem){
        //     $itemIds[] = $invoiceItem->getEntityId();
        //     $itemIdsQty[] = $invoiceItem->getEntityId().':'.$invoiceItem->getQty();
        // }
        
        $oceanInvoice = $this->oceanInvoiceFactory->create();
        $datetime = gmdate('Y-m-d H:i:s');
        $oceanInvoice->setData(
            array(
                'invoice_id' => $invoice->getId(),
                'mcustomer_id' => $order->getCustomerId(),
                'customer_name' => $order->getCustomerName(),
                'items' => '',
                'items_qty' => '',
                // 'total' => $invoice->getBaseGrandTotal(),
                'tax' => $order->getTaxAmount(),
                'notes' => $invoice->getCustomerNote(),
                'created_at' => $datetime,
                'direction' => 'website_to_ocean',
                'status' => \Simi\Simiocean\Model\SyncStatus::PENDING,
            )
        );
        try{
            $oceanInvoice->save();
        } catch (\Exception $e) {
            $this->logger->debug(array('Simiocean Observer: Invoice event register ocean error.', $e->getMessage()));
        }
    }
}