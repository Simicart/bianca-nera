<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model\Service;

class Invoice extends \Magento\Framework\Model\AbstractModel
{
    const LIMIT = 10;
    
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
                $totalFinal = 0.0;
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
                $oceanItems = [];
                foreach($items as $item){
                    if ($this->isOceanProduct($item->getProductId())) {
                        $skuBarcode = explode('_', $item->getSku());
                        if (count($skuBarcode) == 2) {
                            list ($sku, $barcode) = $skuBarcode;

                            $baseDiscount = max((float) $item->getBaseDiscountAmount(), 0);
                            $baseDiscount = min($baseDiscount, $item->getBaseRowTotal());

                            $price = 0.0;
                            $currentPrice = 0.0;
                            $finalPrice = 0.0;

                            // For configurable product (or ocean parent product)
                            if (isset($parentOrderItems[$item->getOrderItemId()])) {
                                $parentBaseDiscount[$item->getOrderItemId()] = $baseDiscount; //this discount from parent item
                                $parentInvoiceItems[$item->getOrderItemId()] = $item;

                                $oceanItems[$item->getSku()] = [
                                    'SKU' => (string) $sku,
                                    'BarCode' => (string) $barcode,
                                    'IsOutput' => true,
                                    'Quantity' => (int) $item->getQty(),
                                    'DiscountVal' => (float) $baseDiscount,
                                    'Price' => max($price, (float) $item->getBasePrice()),
                                    'CurrentPrice' => max($currentPrice, (float) $item->getBasePrice()),
                                    'FinalPrice' => max($finalPrice, (float) $item->getBaseRowTotalInclTax() - (float) $baseDiscount),
                                ];
                                $totalVal += max(0.0, (float) $item->getBaseRowTotalInclTax());
                                continue;
                            }

                            if (isset($oceanItems[$item->getSku()])) {
                                $oItem = $oceanItems[$item->getSku()];
                                $oceanItems[$item->getSku()] = [
                                    'SKU' => $oItem['SKU'],
                                    'BarCode' => $oItem['BarCode'],
                                    'IsOutput' => $oItem['IsOutput'],
                                    'Quantity' => $oItem['Quantity'],
                                    'DiscountVal' => $oItem['DiscountVal'] + (float) $item->getBaseDiscountAmount(),
                                    'Price' => $oItem['Price'],
                                    'CurrentPrice' => $oItem['CurrentPrice'],
                                    'FinalPrice' => max($finalPrice, $oItem['FinalPrice'] + (float) $item->getBaseRowTotalInclTax() - (float) $baseDiscount),
                                ];
                                $totalVal += max(0.0, (float) $item->getBaseRowTotalInclTax());
                            } else {
                                $oceanItems[$item->getSku()] = [
                                    'SKU' => (string) $sku,
                                    'BarCode' => (string) $barcode,
                                    'IsOutput' => true,
                                    'Quantity' => (int) $item->getQty(),
                                    'DiscountVal' => (float) $baseDiscount,
                                    'Price' => max($price, (float) $item->getBasePrice()),
                                    'CurrentPrice' => max($currentPrice, (float) $item->getBasePrice()),
                                    'FinalPrice' => max($finalPrice, (float) $item->getBaseRowTotalInclTax() - (float) $baseDiscount),
                                ];
                                $totalVal += max(0.0, (float) $item->getBaseRowTotalInclTax());
                            }

                            $oItem = $oceanItems[$item->getSku()];

                            $totalQty += $oItem['Quantity'];
                            $totalDiscount += $oItem['DiscountVal'];
                            $totalFinal = $totalVal - $totalDiscount;
                        }
                    }
                }

                $invoiceItems = array_values($oceanItems);

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
                    'FinalValue' => $totalFinal,
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
                                "Value" => $totalFinal
                            ));
                        }
                    }
                }

                $oInvoice->setCustomerId($customerId);
                $oInvoice->setTotal($totalFinal);
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
                    return $invoiceNo;
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
                $totalFinal = 0.0;
                $totalVal = 0.0;
                $totalQty = 0;
                $totalDiscount = 0.0;
                // Get all invoice items data
                $invoiceItems = array();
                $creditmemo = $this->creditmemoRepository->get($cInvoice->getCreditmemoId());
                $order = $creditmemo->getOrder();

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
                $parentInvoiceItems = [];
                $oceanItems = [];

                $items = $creditmemo->getItems();
                foreach($items as $item){
                    if ($this->isOceanProduct($item->getProductId())) {
                        $skuBarcode = explode('_', $item->getSku());
                        if (count($skuBarcode) == 2) {
                            list ($sku, $barcode) = $skuBarcode;

                            $baseDiscount = max((float) $item->getBaseDiscountAmount(), 0);
                            $baseDiscount = min($baseDiscount, $item->getBaseRowTotal());

                            $price = 0.0;
                            $currentPrice = 0.0;
                            $finalPrice = 0.0;

                            // For configurable product (or ocean parent product)
                            if (isset($parentOrderItems[$item->getOrderItemId()])) {
                                $parentBaseDiscount[$item->getOrderItemId()] = $baseDiscount; //this discount from parent item
                                $parentInvoiceItems[$item->getOrderItemId()] = $item;

                                $oceanItems[$item->getSku()] = [
                                    'SKU' => (string) $sku,
                                    'BarCode' => (string) $barcode,
                                    'IsOutput' => true,
                                    'Quantity' => - (int) $item->getQty(),
                                    'DiscountVal' => - (float) $baseDiscount,
                                    'Price' => - max($price, (float) $item->getBasePrice()),
                                    'CurrentPrice' => - max($currentPrice, (float) $item->getBasePrice()),
                                    'FinalPrice' => - max($finalPrice, (float) $item->getBaseRowTotalInclTax() - (float) $baseDiscount),
                                ];

                                $totalVal += max(0.0, (float) $item->getBaseRowTotalInclTax());
                                continue;
                            }

                            if (isset($oceanItems[$item->getSku()])) {
                                $oItem = $oceanItems[$item->getSku()];
                                $oceanItems[$item->getSku()] = [
                                    'SKU' => $oItem['SKU'],
                                    'BarCode' => $oItem['BarCode'],
                                    'IsOutput' => $oItem['IsOutput'],
                                    'Quantity' => $oItem['Quantity'],
                                    'DiscountVal' => - (abs($oItem['DiscountVal']) + (float) $item->getBaseDiscountAmount()),
                                    'Price' => $oItem['Price'],
                                    'CurrentPrice' => $oItem['CurrentPrice'],
                                    'FinalPrice' => - max($finalPrice, abs($oItem['FinalPrice']) + (float) $item->getBaseRowTotalInclTax() - (float) $baseDiscount),
                                ];

                                $totalVal += max(0.0, (float) $item->getBaseRowTotalInclTax());
                            } else {
                                $oceanItems[$item->getSku()] = [
                                    'SKU' => (string) $sku,
                                    'BarCode' => (string) $barcode,
                                    'IsOutput' => true,
                                    'Quantity' => - (int) $item->getQty(),
                                    'DiscountVal' => - (float) $baseDiscount,
                                    'Price' => - max($price, (float) $item->getBasePrice()),
                                    'CurrentPrice' => - max($currentPrice, (float) $item->getBasePrice()),
                                    'FinalPrice' => - max($finalPrice, (float) $item->getBaseRowTotalInclTax() - (float) $baseDiscount),
                                ];

                                $totalVal += max(0.0, (float) $item->getBaseRowTotalInclTax());
                            }

                            $oItem = $oceanItems[$item->getSku()];

                            $totalQty += $oItem['Quantity'];
                            $totalDiscount += $oItem['DiscountVal'];
                            $totalFinal = $totalVal - $totalDiscount;
                        }
                    }
                }

                $invoiceItems = array_values($oceanItems);

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

                $notes = 'Cancel #'.$orderId;
                if ($cInvoice->getNotes()) {
                    $notes = $notes . ' ' . $cInvoice->getNotes();
                }
                $data = array(
                    'CustomerID' => $customerId ?: null,
                    'CustomerName' => $cInvoice->getCustomerName(),
                    'CurrencyRate' => 1.0,
                    'Notes' =>  $notes,
                    'TotalDiscount' => (float) -abs($totalDiscount),
                    'TotalVal' => (float) -$totalVal,
                    'FinalValue' => (float) -$totalFinal,
                    'ReturnCash' => (float) -$totalFinal,
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
                    return $invoiceNo;
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
    /* protected function getOceanProductBarcode($productId){
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
    } */

    /**
     * Get ocean product data by product id
     * @param int $productId
     * @return object|false
     */
    /* protected function getOceanProductData($productId){
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
    } */
    
    /**
     * Check is parent ocean product
     * @return boolean
     */
    /* protected function isOceanProduct($productId){
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
    } */
    /**
     * Check is ocean product
     * @return boolean
     */
    protected function isOceanProduct($productId){
        if ($productId) {
            $resource = $this->oceanInvoice->getResource();
            $connection = $this->oceanProductResource->getConnection();
            $bind = ['product_id' => $productId];
            $select = $connection->select()
                ->from(
                    ['v' => $resource->getTable('catalog_product_entity_int')],
                    "value"
                )
                ->joinLeft(
                    ['e' => $resource->getTable('catalog_product_entity')],
                    'v.entity_id = e.entity_id AND v.store_id = 0',
                    "entity_id"
                )
                ->joinLeft(
                    ['a' => $resource->getTable('eav_attribute')],
                    'a.attribute_id = v.attribute_id',
                    ""
                )
                ->where('e.entity_id = :product_id')
                ->where('a.attribute_code = "is_ocean"')
                ->where('v.value = 1')
                ->limit(1);
            return (bool) $connection->fetchOne($select, $bind);
        }
        return false;
    }

    /**
     * Check order has an ocean item
     */
    public function hasOceanItem($order){
        $items = $order->getItems(); /** @var \Magento\Sales\Api\Data\InvoiceItemInterface[] */
        foreach($items as $item){
            if ($this->isOceanProduct($item->getProductId())) {
                return true;
            }
        }
        return false;
    }
}