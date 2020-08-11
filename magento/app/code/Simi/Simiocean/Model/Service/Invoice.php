<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model\Service;

class Invoice extends \Magento\Framework\Model\AbstractModel
{
    const LIMIT = 100;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface,
     */
    protected $storeManager;

    protected $helper;
    protected $config;
    protected $logger;
    protected $invoiceApi;
    protected $oceanInvoice;
    protected $oceanInvoiceCancel;
    protected $oceanCustomerResource;
    protected $oceanProductResource;
    protected $productService;
    
    protected $invoiceFactory;
    protected $invoiceItemRepository;
    protected $creditmemoRepository;
    protected $productRepository;
    

    /**
     *
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Simi\Simiocean\Helper\Data $helper,
        \Simi\Simiocean\Helper\Config $config,
        \Simi\Simiocean\Model\Logger $logger,
        \Simi\Simiocean\Model\Invoice $oceanInvoice,
        \Simi\Simiocean\Model\InvoiceCancel $oceanInvoiceCancel,
        \Simi\Simiocean\Model\Ocean\Invoice $invoiceApi,
        \Simi\Simiocean\Model\ResourceModel\Customer $oceanCustomerResource,
        \Simi\Simiocean\Model\ResourceModel\Product $oceanProductResource,
        \Magento\Sales\Model\Order\InvoiceFactory $invoiceFactory,
        \Magento\Sales\Model\Order\CreditmemoRepository $creditmemoRepository,
        \Simi\Simiocean\Model\Service\Product $productService,
        \Magento\Sales\Api\InvoiceItemRepositoryInterface $invoiceItemRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ){
        $this->helper = $helper;
        $this->config = $config;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->invoiceApi = $invoiceApi;
        $this->oceanInvoice = $oceanInvoice;
        $this->oceanInvoiceCancel = $oceanInvoiceCancel;
        $this->oceanCustomerResource = $oceanCustomerResource;
        $this->oceanProductResource = $oceanProductResource;
        
        $this->invoiceFactory = $invoiceFactory;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->invoiceItemRepository = $invoiceItemRepository;
        $this->productRepository = $productRepository;
        $this->productService = $productService;

        $registry->register('isSecureArea', true);
        parent::__construct($context, $registry);
    }


    public function testpayment(){
        $payments = $this->invoiceApi->getPayment();
        var_dump($payments);die;
        return true;
    }

    public function process(){
        return true;
    }

    /**
     * Sync push or create invoice to the ocean system
     * @return boolean
     */
    public function syncPush(){
        $size = self::LIMIT;
        // if ($this->config->getCustomerSyncNumber() != null) {
        //     $size = (int)$this->config->getCustomerSyncNumber();
        // }
        $oceanInvoices = $this->getInvoiceSync($size);
        $isSyncSuccess = true;
        $hasSyncSuccess = false;
        $paymentTypes = false;
        foreach($oceanInvoices as $oInvoice){
            if (!$oInvoice->getInvoiceId()) {
                continue;
            }
            $result = '';
            try{
                $customerId = $this->getOceanCustomerId($oInvoice->getMcustomerId());
                $total = 0.0;
                $totalVal = 0.0;
                $totalDiscount = 0.0;
                $totalQty = 0;
                $invoiceItems = array();
                $invoice = $this->invoiceFactory->create()->load($oInvoice->getInvoiceId());
                $order = $invoice->getOrder();
                $payment = $order->getPayment();
                $paymentMethod = $payment->getMethodInstance(); // to check is online
                $parentBaseDiscount = [];
                $parentOrderItems = [];
                $parentOrderItemIds = [];
                $items = $order->getItems(); /** @var \Magento\Sales\Api\Data\InvoiceItemInterface[] */
                foreach($items as $item){
                    if (!$item->getParentItemId()) {
                        $parentOrderItems[$item->getId()] = $item;
                    } else {
                        $parentOrderItemIds[$item->getId()] = $item->getParentItemId();
                    }
                }
                $items = $invoice->getItems(); /** @var \Magento\Sales\Api\Data\InvoiceItemInterface[] */
                $parentInvoiceItems = [];
                foreach($items as $item){
                    // For configurable product (or ocean parent product)
                    if ($this->isOceanProduct($item->getProductId())) {
                        $baseDiscountAmount = max((float) $item->getBaseDiscountAmount(), 0);
                        $baseDiscount = min($baseDiscountAmount, $item->getBaseRowTotal()); //this discount from parent item
                        if (isset($parentOrderItems[$item->getOrderItemId()])) {
                            $parentBaseDiscount[$item->getOrderItemId()] = $baseDiscount;
                            $parentInvoiceItems[$item->getOrderItemId()] = $item;
                        }
                        $totalDiscount += $baseDiscount;
                        $total += ((float) $item->getBaseRowTotal() - (float) $baseDiscount);
                        $totalVal += (float) $item->getBaseRowTotal();
                    }
                    // For simple product
                    $oProductData = $this->getOceanProductData($item->getProductId());
                    if (isset($oProductData['sku']) && isset($oProductData['barcode'])) {
                        $qty = $item->getQty();
                        $totalQty += $qty;
                        $baseDiscountAmount = max((float) $item->getBaseDiscountAmount(), 0);
                        $baseDiscount = min($baseDiscountAmount, $item->getBaseRowTotal());
                        $total += ((float) $item->getBaseRowTotal() - (float) $baseDiscount);
                        $discountVal = 0.0;
                        $price = 0.0;
                        $currentPrice = 0.0;
                        $finalPrice = 0.0;
                        if (isset($parentOrderItemIds[$item->getOrderItemId()])) {
                            $parentOrderItemId = $parentOrderItemIds[$item->getOrderItemId()];
                            if (isset($parentBaseDiscount[$parentOrderItemId])) {
                                $discountVal = max($baseDiscount, $parentBaseDiscount[$parentOrderItemId]);
                            }
                            if (isset($parentInvoiceItems[$parentOrderItemId])) {
                                $parentItem = $parentInvoiceItems[$parentOrderItemId];
                                $price = (float) $parentItem->getBasePrice();
                                $currentPrice = (float) $parentItem->getBasePrice();
                                $finalPrice = (float) $parentItem->getBaseRowTotal() - $discountVal;
                            }
                        }
                        $totalDiscount += $baseDiscount;
                        $totalVal += (float) $item->getBaseRowTotal();
                        $invoiceItems[] = array(
                            'SKU' => (string) $oProductData['sku'],
                            'BarCode' => (string) $oProductData['barcode'],
                            'IsOutput' => true,
                            'Quantity' => (int) $qty,
                            'DiscountVal' => (float) $discountVal,
                            'Price' => max($price, (float) $item->getBasePrice()),
                            'CurrentPrice' => max($currentPrice, (float) $item->getBasePrice()),
                            'FinalPrice' => max($finalPrice, (float) $item->getBaseRowTotal()),
                        );
                    }
                }

                if (empty($invoiceItems)) {
                    // This request push invoice does not has the ocean product item
                    $datetime = gmdate('Y-m-d H:i:s');
                    $oInvoice->setSyncTime($datetime);
                    $oInvoice->setHit($oInvoice->getHit() + 1);
                    $oInvoice->setStatus(\Simi\Simiocean\Model\SyncStatus::SUCCESS);
                    $oInvoice->save();
                    continue;
                }

                $orderId = '';
                try{
                    $orderId = $invoice->getOrder()->getIncrementId();
                } catch(\Exception $e) {
                    $this->logger->debug(array(
                        'Warning! Save ocean invoice failed. ', $e->getMessage()
                    ));
                }

                $totalDiscount = min($totalDiscount, $invoice->getBaseDiscountAmount());

                $data = array(
                    'CustomerID' => $customerId ?: null,
                    'CustomerName' => $oInvoice->getCustomerName(),
                    'CurrencyRate' => 1.0,
                    'Notes' =>  'Order #'.$orderId . ' ' . $oInvoice->getNotes(),
                    'TotalDiscount' => abs($totalDiscount),
                    'TotalVal' => $totalVal,
                    'FinalValue' => $total,
                    'TotalQty' => (int) $totalQty,
                    'IsCustomerPoint' => $customerId ? true : false,
                    'Tax' => (float) $oInvoice->getTax(),
                    'SalesInvoiceItems' => $invoiceItems,
                );

                // Add ocean payment type (Optional)
                if (!$paymentMethod->isOffline()) {
                    if (!$paymentTypes) {
                        $paymentTypes = $this->invoiceApi->getPayment();
                    }
                    if (is_array($paymentTypes) && count($paymentTypes)) {
                        $payType = false;
                        foreach($paymentTypes as $pType){
                            if (isset($pType['PaymentTypeEnName']) && strpos($pType['PaymentTypeEnName'], 'VISA') !== false) {
                                $payType = $pType;
                                break;
                            }
                        }
                        if ($payType && is_array($payType) && isset($payType['PaymentTypeID'])) {
                            $data['SalesInvoicePayments'] = array(array(
                                "PaymentTypeID" => $payType['PaymentTypeID'],
                                // "ApprovalNo" => "021722022921",
                                "Value" => $total
                            ));
                        }
                    }
                }

                $oInvoice->setCustomerId($customerId);
                $oInvoice->setTotal($total);
                $oInvoice->setItemsQty($totalQty);
                $oInvoice->setHit($oInvoice->getHit() + 1);

                // Force debug log
                $dataLog = array(
                    'message' => 'Forced log: Invoice prepare for order: #'.$orderId,
                    'data' => json_encode($data)
                );
                $this->logger->debug($dataLog, true);

                $result = $this->invoiceApi->addInvoice($data);
                $invoiceNo = (int) $result;
            }catch(\Exception $e){
                $invoiceNo = false;
                $result = $e->getMessage();
                $isSyncSuccess = false;
                $this->logger->debug($result);
            }
            // Skipping error message by check strlen((string)$invoiceNo) == strlen((string)$result)
            if ((int)$invoiceNo && strlen((string)$invoiceNo) == strlen((string)$result)) {
                try{
                    $datetime = gmdate('Y-m-d H:i:s');
                    $oInvoice->setSyncTime($datetime);
                    $oInvoice->setInvoiceNo($invoiceNo);
                    $oInvoice->setStatus(\Simi\Simiocean\Model\SyncStatus::SUCCESS);
                    $oInvoice->setStatusMessage(''); // clear message
                    $oInvoice->save();
                    $isSyncSuccess = true;
                    $hasSyncSuccess = true;
                }catch(\Exception $e){
                    $this->logger->debug(array(
                        'Warning! Save ocean invoice failed. InvoiceNo: '.$invoiceNo, 
                        $e->getMessage()
                    ));
                }
            } else {
                //TODO: check invoice text message from ocean
                $oInvoice->setStatus(\Simi\Simiocean\Model\SyncStatus::FAILED);
                $oInvoice->setStatusMessage($result); // Add log message to database
                $oInvoice->save();
                $isSyncSuccess = false;
            }
        }

        if ($hasSyncSuccess) {
            $this->productService->syncUpdateStock();
        }

        return $isSyncSuccess;
    }

    /**
     * Sync refund order from magento to ocean
     * @return boolean
     */
    public function syncCancel(){
        $size = self::LIMIT;
        $canceledInvoices = $this->getCanceledInvoice($size);
        $isSyncSuccess = true;
        $hasSyncSuccess = false;
        foreach($canceledInvoices as $cInvoice){
            if (!$cInvoice->getCreditmemoId()) {
                continue;
            }
            $result = '';
            try{
                $customerId = $this->getOceanCustomerId($cInvoice->getMcustomerId());
                $total = 0.0;
                $totalVal = 0.0;
                $totalQty = 0;
                $totalDiscount = 0.0;
                // Get all invoice items data
                $invoiceItems = array();
                $creditmemo = $this->creditmemoRepository->get($cInvoice->getCreditmemoId());
                $order = $creditmemo->getOrder();
                $items = $order->getItems(); /** @var \Magento\Sales\Api\Data\InvoiceItemInterface[] */
                $parentItems = array();

                $parentBaseDiscount = [];

                foreach($items as $item){
                    // Add parent item array
                    if (!$item->getParentItemId()) {
                        $parentItems[$item->getId()] = $item;
                    }
                    // For configurable product
                    if ($this->isOceanProduct($item->getProductId())) {
                        $baseDiscountAmount = max((float) $item->getBaseDiscountAmount(), 0);
                        $baseDiscount = min($baseDiscountAmount, $item->getBaseRowTotal()); //this discount from parent item
                        
                        if (!$item->getParentItemId()) {
                            $parentBaseDiscount[$item->getId()] = $baseDiscount;
                        }
                        $totalDiscount += (float) $baseDiscount;
                        $totalVal += (float) $item->getBaseRowTotal();
                        $total += ((float) $item->getBaseRowTotal() - (float) $baseDiscount);
                    }
                    // For simple product
                    $oProductData = $this->getOceanProductData($item->getProductId());
                    if (isset($oProductData['sku']) && isset($oProductData['barcode'])) {
                        $qty = $item->getQtyInvoiced();
                        $totalQty += $qty;

                        $discountVal = 0.0;
                        $price          = $item->getBasePrice();
                        $currentPrice   = $item->getBasePrice();
                        $finalPrice     = $item->getBaseRowTotal();
                        if ($item->getParentItemId() && isset($parentItems[$item->getParentItemId()])) {
                            $parentItem = $parentItems[$item->getParentItemId()];
                            $price          = $parentItem->getBasePrice();
                            $currentPrice   = $parentItem->getBasePrice();
                            $finalPrice     = $parentItem->getBaseRowTotal();
                            if (isset($parentBaseDiscount[$item->getParentItemId()])) {
                                $discountVal = (float) $parentBaseDiscount[$item->getParentItemId()];
                            }
                        }

                        $baseDiscountAmount = max((float) $item->getBaseDiscountAmount(), 0);
                        $baseDiscount = min($baseDiscountAmount, $item->getBaseRowTotal());
                        if (!$item->getParentItemId()) {
                            $parentBaseDiscount[$item->getId()] = $baseDiscount;
                        }
                        $totalDiscount += (float) $baseDiscount;
                        $totalVal += (float) $item->getBaseRowTotal();
                        $total += ((float) $item->getBaseRowTotal() - (float) $baseDiscount);

                        $invoiceItems[] = array(
                            'SKU' => (string) $oProductData['sku'],
                            'BarCode' => (string) $oProductData['barcode'],
                            'IsOutput' => false, // you should pass the[ReturnCash] property. And in the details make the [IsOutput] = false
                            'Quantity' => (int) $qty,
                            'DiscountVal' => (float) $discountVal,
                            'Price' => (float) $price,
                            'CurrentPrice' => (float) $currentPrice,
                            'FinalPrice' => (float) $finalPrice - (float) $discountVal,
                        );
                    }
                }

                $total = min($total, (float)$cInvoice->getTotal()); // creditmemo baseGrandTotal

                if (empty($invoiceItems)) {
                    // This request push invoice does not has the ocean product item
                    $datetime = gmdate('Y-m-d H:i:s');
                    $cInvoice->setSyncTime($datetime);
                    $cInvoice->setHit($cInvoice->getHit() + 1);
                    $cInvoice->setStatus(\Simi\Simiocean\Model\SyncStatus::SUCCESS);
                    $cInvoice->save();
                    continue;
                }

                $orderId = '';
                try{
                    $orderId = $order->getIncrementId();
                } catch(\Exception $e) {
                    $this->logger->debug(array(
                        'Warning! Save ocean invoice failed. ', $e->getMessage()
                    ));
                }

                $totalDiscount = min($totalDiscount, $order->getBaseDiscountAmount());

                $data = array(
                    'CustomerID' => $customerId ?: null,
                    'CustomerName' => $cInvoice->getCustomerName(),
                    'CurrencyRate' => 1.0,
                    'Notes' =>  'Magento Cancel Order #'.$orderId . ' ' . $cInvoice->getNotes(),
                    'TotalDiscount' => (float) -abs($totalDiscount),
                    'TotalVal' => (float) -$totalVal,
                    'FinalValue' => (float) -$total,
                    'ReturnCash' => (float) -$total,
                    'TotalQty' => (int) -$totalQty,
                    'IsCustomerPoint' => $customerId ? true : false,
                    'Tax' => (float) $cInvoice->getTax(),
                    'SalesInvoiceItems' => $invoiceItems,
                );

                $cInvoice->setCustomerId($customerId);
                // $cInvoice->setTotal($total); // total already set from observer
                $cInvoice->setItemsQty($totalQty);
                $cInvoice->setHit($cInvoice->getHit() + 1);

                // Force debug log
                $dataLog = array(
                    'message' => 'Forced log: Invoice cancel prepare for order: #'.$orderId,
                    'data' => json_encode($data)
                );
                $this->logger->debug($dataLog, true);

                $result = $this->invoiceApi->addInvoice($data);
                $invoiceNo = (int) $result;
            }catch(\Exception $e){
                $invoiceNo = false;
                $result = $e->getMessage();
                $isSyncSuccess = false;
                $cInvoice->setStatusMessage($result); // Add log message to database
                $this->logger->debug($result);
            }
            // Skipping error message by check strlen((string)$invoiceNo) == strlen((string)$result)
            if ((int)$invoiceNo && strlen((string)$invoiceNo) == strlen((string)$result)) {
                try{
                    $datetime = gmdate('Y-m-d H:i:s');
                    $cInvoice->setSyncTime($datetime);
                    $cInvoice->setInvoiceNo($invoiceNo);
                    $cInvoice->setStatus(\Simi\Simiocean\Model\SyncStatus::SUCCESS);
                    $cInvoice->setStatusMessage(''); // clear message
                    $cInvoice->save();
                    $isSyncSuccess = true;
                    $hasSyncSuccess = true;
                }catch(\Exception $e){
                    $this->logger->debug(array(
                        'Warning! Save ocean invoice failed. InvoiceNo: '.$invoiceNo, 
                        $e->getMessage()
                    ));
                }
            } else {
                //TODO: check invoice text message from ocean
                $cInvoice->setStatus(\Simi\Simiocean\Model\SyncStatus::FAILED);
                $cInvoice->setStatusMessage($result); // Add log message to database
                $cInvoice->save();
                $isSyncSuccess = false;
            }
        }
        // if ($hasSyncSuccess) {
        //     $this->productService->syncUpdateStock();
        // }

        return $isSyncSuccess;
    }

    /**
     * Get list of pending canceled invoice to sync
     * @param int $limit
     * @return Simi\Simiocean\Model\ResourceModel\InvoiceCancel\Collection
     */
    protected function getCanceledInvoice($limit = 100){
        $collection = $this->oceanInvoiceCancel->getCollection();
        $collection->addFieldToFilter('status', array('in' => array(
                \Simi\Simiocean\Model\SyncStatus::PENDING,
                \Simi\Simiocean\Model\SyncStatus::FAILED
            )))
            ->getSelect()
            ->order('created_at asc')
            ->order('hit desc')
            ->limit($limit);
        return $collection;
    }

    /**
     * Get list of pending invoice to sync
     * @param int $limit
     * @return Simi\Simiocean\Model\ResourceModel\Invoice\Collection
     */
    protected function getInvoiceSync($limit = 100){
        $collection = $this->oceanInvoice->getCollection();
        $collection->addFieldToFilter('status', array('in' => array(
                \Simi\Simiocean\Model\SyncStatus::PENDING,
                \Simi\Simiocean\Model\SyncStatus::FAILED
            )))
            ->getSelect()
            ->order('created_at asc')
            ->order('hit desc')
            ->limit($limit);
        return $collection;
    }

    /**
     * Get list of failed invoice to sync
     * @param int $limit
     * @return Simi\Simiocean\Model\ResourceModel\Invoice\Collection
     */
    protected function getInvoiceSyncFailed($limit = 10){
        $collection = $this->oceanInvoice->getCollection();
        $collection->addFieldToFilter('status', \Simi\Simiocean\Model\SyncStatus::FAILED)
            ->getSelect()
            ->order('created_at asc')
            ->limit($limit);
        return $collection;
    }

    /**
     * Get ocean customer by magento customer id
     * @param int $mCustomerId
     * @return int
     */
    protected function getOceanCustomerId($mCustomerId){
        if ($mCustomerId) {
            $connection = $this->oceanCustomerResource->getConnection();
            $bind = ['customer_id' => $mCustomerId];
            $select = $connection->select()
                ->from($this->oceanCustomerResource->getTable('simiocean_customer'), 'customer_id')
                ->where('m_customer_id = :customer_id')
                ->where('customer_id IS NOT NULL');
            return $connection->fetchOne($select, $bind);
        }
        return false;
    }

    /**
     * Check product id is existed ocean product
     * @param int $productId
     * @return string|boolean
     */
    protected function getOceanProductBarcode($productId){
        if ($productId) {
            $connection = $this->oceanProductResource->getConnection();
            $bind = ['product_id' => $productId];
            $select = $connection->select()
                ->from($this->oceanProductResource->getTable('simiocean_product'), array('barcode'))
                ->where('product_id = :product_id')
                ->where('sku IS NOT NULL')
                ->where('barcode IS NOT NULL');
            return $connection->fetchOne($select, $bind);
        }
        return false;
    }

    /**
     * Get ocean product data by product id
     * @param int $productId
     * @return object|false
     */
    protected function getOceanProductData($productId){
        if ($productId) {
            $connection = $this->oceanProductResource->getConnection();
            $bind = ['product_id' => $productId];
            $select = $connection->select()
                ->from($this->oceanProductResource->getTable('simiocean_product'), array('sku', 'barcode'))
                ->where('product_id = :product_id')
                ->where('sku IS NOT NULL')
                ->where('barcode IS NOT NULL')
                ->limit(1);
            return $connection->fetchRow($select, $bind);
        }
        return false;
    }
    
    /**
     * Check is parent ocean product
     * @return boolean
     */
    protected function isOceanProduct($productId){
        if ($productId) {
            $connection = $this->oceanProductResource->getConnection();
            $bind = ['product_id' => $productId];
            $select = $connection->select()
                ->from($this->oceanProductResource->getTable('simiocean_product'), 'parent_id')
                ->where('parent_id = :product_id')
                ->where('sku IS NOT NULL')
                ->where('barcode IS NOT NULL')
                ->limit(1);
            return (bool) $connection->fetchOne($select, $bind);
        }
        return false;
    }
}