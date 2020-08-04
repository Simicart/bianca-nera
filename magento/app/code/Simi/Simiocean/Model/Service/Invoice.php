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
    protected $oceanCustomerResource;
    protected $oceanProductResource;
    protected $productService;
    
    protected $invoiceFactory;
    protected $invoiceItemRepository;
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
        \Simi\Simiocean\Model\Ocean\Invoice $invoiceApi,
        \Simi\Simiocean\Model\ResourceModel\Customer $oceanCustomerResource,
        \Simi\Simiocean\Model\ResourceModel\Product $oceanProductResource,
        \Magento\Sales\Model\Order\InvoiceFactory $invoiceFactory,
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
        $this->oceanCustomerResource = $oceanCustomerResource;
        $this->oceanProductResource = $oceanProductResource;
        
        $this->invoiceFactory = $invoiceFactory;
        $this->invoiceItemRepository = $invoiceItemRepository;
        $this->productRepository = $productRepository;
        $this->productService = $productService;

        $registry->register('isSecureArea', true);
        parent::__construct($context, $registry);
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
        foreach($oceanInvoices as $oInvoice){
            if (!$oInvoice->getInvoiceId()) {
                continue;
            }
            $result = '';
            try{
                $customerId = $this->getOceanCustomerId($oInvoice->getMcustomerId());
                $total = 0.0;
                $totalQty = 0;
                $invoiceItems = array();
                $invoice = $this->invoiceFactory->create()->load($oInvoice->getInvoiceId());
                $items = $invoice->getItems(); /** @var \Magento\Sales\Api\Data\InvoiceItemInterface[] */
                foreach($items as $item){
                    // For configurable product
                    if ($this->isOceanProduct($item->getProductId())) {
                        $baseDiscountAmount = max((float) $item->getBaseDiscountAmount(), 0);
                        $baseDiscount = min($baseDiscountAmount, $item->getBaseRowTotal());
                        $total += ((float) $item->getBaseRowTotal() - (float) $baseDiscount);
                    }
                    // For simple product
                    $oProductData = $this->getOceanProductData($item->getProductId());
                    if (isset($oProductData['sku']) && isset($oProductData['barcode'])) {
                        $qty = $item->getQty();
                        $totalQty += $qty;
                        $invoiceItems[] = array(
                            'SKU' => (string) $oProductData['sku'],
                            'BarCode' => (string) $oProductData['barcode'],
                            'IsOutput' => true,
                            'Quantity' => (int) $qty,
                            'Price' => (float) $item->getBasePrice(),
                            'CurrentPrice' => (float) $item->getBasePrice(),
                            'FinalPrice' => (float) $item->getBaseRowTotal(),
                        );
                        $baseDiscountAmount = max((float) $item->getBaseDiscountAmount(), 0);
                        $baseDiscount = min($baseDiscountAmount, $item->getBaseRowTotal());
                        $total += ((float) $item->getBaseRowTotal() - (float) $baseDiscount);
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
                    $orderId = $oInvoice->getOrder()->getIncrementId();
                } catch(\Exception $e) {
                    $this->logger->debug(array(
                        'Warning! Save ocean invoice failed. ', $e->getMessage()
                    ));
                }

                $data = array(
                    'CustomerID' => $customerId ?: null,
                    'CustomerName' => $oInvoice->getCustomerName(),
                    'CurrencyRate' => 1.0,
                    'Notes' =>  'Order #'.$orderId . ' ' . $oInvoice->getNotes(),
                    'TotalVal' => $total,
                    'FinalValue' => $total,
                    'TotalQty' => (int) $totalQty,
                    'IsCustomerPoint' => $customerId ? true : false,
                    'Tax' => (float) $oInvoice->getTax(),
                    'SalesInvoiceItems' => $invoiceItems,
                );

                $oInvoice->setCustomerId($customerId);
                $oInvoice->setTotal($total);
                $oInvoice->setItemsQty($totalQty);
                $oInvoice->setHit($oInvoice->getHit() + 1);

                $result = $this->invoiceApi->addInvoice($data);
                $invoiceNo = (int) $result;
            }catch(\Exception $e){
                $invoiceNo = false;
                $result = $e->getMessage();
                $isSyncSuccess = false;
            }
            // Skipping error message by check strlen((string)$invoiceNo) == strlen((string)$result)
            if ((int)$invoiceNo && strlen((string)$invoiceNo) == strlen((string)$result)) {
                try{
                    $datetime = gmdate('Y-m-d H:i:s');
                    $oInvoice->setSyncTime($datetime);
                    $oInvoice->setInvoiceNo($invoiceNo);
                    $oInvoice->setStatus(\Simi\Simiocean\Model\SyncStatus::SUCCESS);
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
                if (strpos($result, 'Invoice text message') != false) {
                    $oInvoice->setStatus(\Simi\Simiocean\Model\SyncStatus::FAILED);
                    $oInvoice->save();
                } else {
                    $oInvoice->setStatus(\Simi\Simiocean\Model\SyncStatus::FAILED);
                    $oInvoice->save();
                }
                $isSyncSuccess = false;
            }
        }

        if ($hasSyncSuccess) {
            $this->productService->syncUpdateStock();
        }

        return $isSyncSuccess;
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