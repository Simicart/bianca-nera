<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model\Service;

use Magento\Eav\Model\Config as EavConfig;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\AttributeFactory as ConfigurableAttributeFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ProductTypeConfigurable;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Model\Product\Attribute\SetRepository;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\framework\Api\ObjectFactory;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Reflection\MethodsMap;
use Simi\Simiocean\Api\Data\ProductInterface as SimioceanProductInterface;
use Simi\Simiocean\Model\ProductFactory as SimioceanProductFactory;
use Simi\Simiocean\Model\ResourceModel\Product as SimioceanProductResourceModel;
use Simi\Simiocean\Model\SyncTable\Type;
use Magento\Framework\Exception\NoSuchEntityException;

class Product extends \Magento\Framework\Model\AbstractModel
{
    const LIMIT = 10;

    protected $helper;
    protected $config;
    protected $colorMapping;
    protected $sizeMapping;
    protected $brandMapping;
    protected $vendorMapping;

    protected $messages = []; // message texts

    protected $categoryService;

    /**
     * @var Simi\Simiocean\Model\SyncTable
     */
    protected $syncTable;
    protected $syncTableFactory;

    /**
     * object Simi\Simiocean\Model\Ocean\Product
     */
    protected $productApi;

    /**
     * @var Magento\Eav\Model\Config
     */
    protected $eavConfig;
    
    /**
     * @var ConfigurableAttributeFactory
     */
    protected $configurableAttributeFactory;

    /**
     * @var Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $productTypeConfigurable;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var ProductResourceModel
     */
    protected $productResourceModel;

    /**
     * object Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * object ProductInterfaceFactory
     */
    protected $productInterfaceFactory;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var AttributeSetRepository
     */
    protected $attributeSetRepository;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Set
     */
    protected $attributeSet;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var MethodsMap
     */
    private $methodsMapProcessor;

    protected $simioceanProductFactory;

    /** @var SimioceanProductResourceModel */
    protected $simioceanProductResourceModel;

    /** @var Simi\Simiocean\Model\Logger */
    protected $logger;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    protected $linkManagement;

    /**
     * @param Magento\Framework\Model\Context $context
     * @param Magento\Framework\Registry $registry
     * @param Simi\Simiocean\Helper\Data $helper
     * @param Simi\Simiocean\Model\Ocean\Product $productApi
     * @param ProductRepository $productRepository,
     * @param ProductInterfaceFactory $productInterfaceFactory,
     * @param DataObjectHelper $dataObjectHelper,
     * @param DataObjectProcessor $dataObjectProcessor
     * @param MethodsMap $methodsMapProcessor
     */
    public function __construct(
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\CategoryLinkManagementInterface $linkManagement,
        \Simi\Simiocean\Helper\Data $helper,
        \Simi\Simiocean\Helper\Config $config,
        \Simi\Simiocean\Model\Ocean\Product $productApi,
        \Simi\Simiocean\Model\Product\ColorMapping $colorMapping,
        \Simi\Simiocean\Model\Product\SizeMapping $sizeMapping,
        \Simi\Simiocean\Model\Product\BrandMapping $brandMapping,
        \Simi\Simiocean\Model\Product\VendorMapping $vendorMapping,
        \Simi\Simiocean\Model\Service\Category $categoryService, 
        \Simi\Simiocean\Model\SyncTable $syncTable,
        \Simi\Simiocean\Model\SyncTableFactory $syncTableFactory,
        \Simi\Simiocean\Model\Logger $logger,
        EavConfig $eavConfig,
        ConfigurableAttributeFactory $configurableAttributeFactory,
        ProductTypeConfigurable $productTypeConfigurable,
        ProductFactory $productFactory,
        ProductResourceModel $productResourceModel,
        ProductRepository $productRepository,
        ProductInterfaceFactory $productInterfaceFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        ObjectFactory $objectFactory,
        DataObjectFactory $dataObjectFactory,
        MethodsMap $methodsMapProcessor,
        SetRepository $attributeSetRepository,
        SimioceanProductFactory $simioceanProductFactory,
        SimioceanProductResourceModel $simioceanProductResource
    ){
        $this->helper = $helper;
        $this->config = $config;
        $this->productApi = $productApi;
        $this->eavConfig = $eavConfig;
        $this->configurableAttributeFactory = $configurableAttributeFactory;
        $this->productTypeConfigurable = $productTypeConfigurable;
        $this->productFactory = $productFactory;
        $this->productResourceModel = $productResourceModel;
        $this->productRepository = $productRepository;
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->objectFactory = $objectFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->methodsMapProcessor = $methodsMapProcessor;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->colorMapping = $colorMapping;
        $this->sizeMapping = $sizeMapping;
        $this->brandMapping = $brandMapping;
        $this->vendorMapping = $vendorMapping;
        $this->syncTable = $syncTable;
        $this->syncTableFactory = $syncTableFactory;
        $this->simioceanProductFactory = $simioceanProductFactory;
        $this->simioceanProductResource = $simioceanProductResource;
        $this->logger = $logger;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->storeManager = $storeManager;
        $this->linkManagement = $linkManagement;
        $this->categoryService = $categoryService;
        parent::__construct($context, $registry);
    }

    /**
     * Sync pull: update changeds data
     * Note: Brand -> Designer, Fabric -> Brand, all other data
     */
    public function syncUpdatePull(){
        $page = 1;
        $size = self::LIMIT;
        $lastDays = 1; // 1 day ago from now

        if ($this->config->getProductSyncNumber() != null) {
            $size = (int)$this->config->getProductSyncNumber();
        }
        // Get time and page number from last synced
        $timeFrom = 'now';
        $timeTo = 'now';
        $lastSyncTable = $this->syncTable->getLastSyncByTime(Type::TYPE_PRODUCT_UPDATE);
        if ($lastSyncTable->getId() && $lastSyncTable->getPageNum()) {
            if ($lastSyncTable->getRecordNumber() > 0) {
                $page = $lastSyncTable->getPageNum() + 1; //increment 1 page
                $timeTo = $lastSyncTable->getUpdatedTo();
                $timeFrom = $lastSyncTable->getUpdatedFrom();
            } else {
                $timeFrom = $lastSyncTable->getUpdatedTo();
            }
        }

        // ToDate
        $dateTo = new \DateTime($timeTo, new \DateTimeZone('UTC'));
        $dateToGmt = $dateTo->format('Y-m-d H:i:s');
        $dateToParam = $dateTo->getTimestamp();

        // FromDate
        $dateFrom = new \DateTime($timeFrom, new \DateTimeZone('UTC'));
        if ($timeFrom == 'now') {
            $dateFrom->setTimestamp($dateFrom->getTimestamp() - ($lastDays * 86400));
        }
        if (($dateToParam - $dateFrom->getTimestamp()) > ($lastDays * 86400)) {
            $dateFrom->setTimestamp($dateToParam - ($lastDays * 86400));
        }
        $dateFromGmt = $dateFrom->format('Y-m-d H:i:s');
        $dateFromParam = $dateFrom->getTimestamp();

        try{
            $oProducts = $this->productApi->getProductFilter($dateFromParam, $dateToParam, $page, $size);
        }catch(\Exception $e){
            $this->logger->debug(array(
                'Error: Get ocean products updated error. Page = '.$page.', Size = '.$size.', from = '.$dateFromGmt.', to = '.$dateToGmt, 
                $e->getMessage()
            ));
            return false;
        }

        // testing
        // $oProducts = $this->productApi->getProductSku('20132005'); // Get products from ocean with sku
        // $oProducts = $this->productApi->getProductSku('19121011'); // new
        // var_dump($oProducts);die;

        if (is_array($oProducts)) {
            $hasUpdate = false;
            $records = count($oProducts);
            $oceanConfigurable = array();
            foreach ($oProducts as $oProduct) {

                if (isset($oProduct['SKU']) && isset($oProduct['BarCode'])
                    && $oProduct['SKU'] && $oProduct['BarCode']
                ) {
                    $magentoSku = $oProduct['SKU'].'_'.$oProduct['BarCode'];
                    $oceanObject = $this->dataObjectFactory->create();
                    $oceanObject->setData($oProduct);
                    
                    try {
                        $productData = $this->convertProductData($oProduct); //convert data array to product object model
                        $productData->setSku($magentoSku);
                        $productData->setName($productData->getName().'-'. $oceanObject->getData('ColorEnName') .'-'.$oceanObject->getData('SizeName'));
                        $productData->setUrlKey(str_replace(' ', '-', strtolower($productData->getName())).'-'.$oceanObject->getData('BarCode'));

                        // sync brand
                        $brandId = $this->brandMapping->getMatchingBrand(
                            $oceanObject->getData('FabricID'),
                            $oceanObject->getData('FabricEnName'), 
                            $oceanObject->getData('FabricArName')
                        );
                        $productData->setBrand($brandId);
                        $productData->setCustomAttribute('brand', $brandId);
                        // sync vendor (designer)
                        $vendorId = $this->vendorMapping->getMatching(
                            $oceanObject->getData('BrandID'), 
                            $oceanObject->getData('BrandEnName'),
                            $oceanObject->getData('BrandArName')
                        );
                        $productData->setVendorId($vendorId);

                        // Add to config product data before save simple product cause NoSuchEntityException
                        $productData->setName($oProduct['ProductEnName'] ?? ''); // pass ocean data
                        $productData->setArName($oProduct['ProductArName'] ?? '');
                        $oceanConfigurable[$oProduct['SKU']] = array(
                            'product_data' => $productData,
                            'ocean_data' => $oProduct
                        );

                        if ($product = $this->updateProduct($productData)) {
                            try{
                                // save product Arab store
                                if ($this->config->getArStore() != null 
                                    && isset($oProduct['ProductArName']) && $oProduct['ProductArName']) 
                                {
                                    $arStoreIds = explode(',', $this->config->getArStore());
                                    $product->setName($oProduct['ProductArName'].'-'.$oceanObject->getData('ColorArName').'-'.$oceanObject->getData('SizeName'));
                                    foreach($arStoreIds as $storeId){
                                        $product->setStoreId($storeId);
                                        $product->save();
                                    }
                                }
                            }catch(\Exception $e){}

                            // Assign product to category
                            try{
                                if ($categoryId = $this->categoryService->getMagentoCategoryId($oProduct['SubcategoryId'], $oProduct['CategoryId'])) {
                                    $this->linkManagement->assignProductToCategories($product->getSku(), array($categoryId));
                                }
                            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                            } catch (\Exception $e) {
                            }
                        }

                        $hasUpdate = true;

                    } catch (NoSuchEntityException $e) {
                        continue;
                    } catch (\Exception $e) {
                        $this->logger->debug($e->getMessage());
                        continue;
                    }
                }
            }

            // Update configurable product
            if (!empty($oceanConfigurable)) {
                $storeId = (int)$this->storeManager->getStore()->getId();
                foreach ($oceanConfigurable as $sku => $configData) {
                    $productData = $configData['product_data'];
                    $oProduct = $configData['ocean_data'];
                    $configProduct = $this->updateConfigurableProduct($sku, $productData, $productData->getArName());
                    if ($configProduct) {
                        // Assign product to category
                        try{
                            if ($categoryId = $this->categoryService->getMagentoCategoryId($oProduct['SubcategoryId'], $oProduct['CategoryId'])) {
                                $this->linkManagement->assignProductToCategories($configProduct->getSku(), array($categoryId));
                            }
                        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                        } catch (\Exception $e) {
                        }
                    }
                }
            }

            $lastSyncTable->setId(null);
            $lastSyncTable->setType(\Simi\Simiocean\Model\SyncTable\Type::TYPE_PRODUCT_UPDATE);
            $lastSyncTable->setPageNum($page);
            $lastSyncTable->setPageSize($size);
            $lastSyncTable->setRecordNumber($records);
            $lastSyncTable->setUpdatedFrom($dateFromGmt);
            $lastSyncTable->setUpdatedTo($dateToGmt);
            $lastSyncTable->setCreatedAt(gmdate('Y-m-d H:i:s'));
            $lastSyncTable->save();
            
            return $hasUpdate;
        } else {
            if ($lastSyncTable->getId()){
                $lastSyncTable->setRecordNumber(0);
                $lastSyncTable->save();
            }
            $this->logger->debug(array(
                'Error: Get ocean products updated error. Page = '.$page.', Size = '.$size.', from = '.$dateFromGmt.', to = '.$dateToGmt, 
                'Server: '.$oProducts
            ));
        }
        return false;
    }

    /**
     * Sync pull:
     * Note: Brand -> Designer, Fabric -> Brand
     */
    public function syncUpdatePullCustom(){
        // Get time and page number from last synced
        $lastSyncTable = $this->syncTable->getLastSyncByTime(Type::TYPE_PRODUCT_UPDATE_CUSTOM);
        // get magento product is ocean product
        $sku = '';
        $productCollection = $this->productFactory->create()->getCollection();
        $productCollection->addAttributeToFilter('is_ocean', true)
            ->getSelect()->order('updated_at ASC')->limit(1);
        if ($productCollection->getSize()) {
            $magentoProductOcean = $productCollection->getFirstItem();
            if ($magentoProductOcean && $magentoProductOcean->getSku()) {
                $sku = $magentoProductOcean->getSku();
                $sku = explode('_', $sku);
                if (isset($sku[0])) {
                    $sku = $sku[0];
                    $updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
                    $updatedAtToGmt = $updatedAt->format('Y-m-d H:i:s');
                    $magentoProductOcean->setUpdatedAt($updatedAtToGmt); // save that updated
                    $magentoProductOcean->save();
                }
            }
        }

        if ($sku) {

            try{
                $oProducts = $this->productApi->getProductSku($sku); // Get products from ocean with sku
            }catch(\Exception $e){
                $this->logger->debug(array(
                    'Error: Get ocean products updated error. Sku = '.$sku, 
                    $e->getMessage()
                ));
                return false;
            }
    
            // testing
            // $oProducts = $this->productApi->getProductSku('20132005'); // Get products from ocean with sku
            // $oProducts = $this->productApi->getProductSku('20180009'); // new
            // var_dump($oProducts);die;
    
            if (is_array($oProducts)) {
                $hasUpdate = false;
                $records = count($oProducts);
                $oceanConfigurable = array();
                foreach ($oProducts as $oProduct) {
    
                    if (isset($oProduct['SKU']) && isset($oProduct['BarCode'])
                        && $oProduct['SKU'] && $oProduct['BarCode']
                    ) {
                        $magentoSku = $oProduct['SKU'].'_'.$oProduct['BarCode'];
                        $oceanObject = $this->dataObjectFactory->create();
                        $oceanObject->setData($oProduct);
                        
                        try {
                            $productData = $this->convertProductData($oProduct); //convert data array to product object model
                            $productData->setSku($magentoSku);
                            $productData->setName($productData->getName().'-'. $oceanObject->getData('ColorEnName') .'-'.$oceanObject->getData('SizeName'));
                            $productData->setUrlKey(str_replace(' ', '-', strtolower($productData->getName())).'-'.$oceanObject->getData('BarCode'));
    
                            // $product = $this->productRepository->get($magentoSku, true);
    
                            // sync brand
                            $brandId = $this->brandMapping->getMatchingBrand(
                                $oceanObject->getData('FabricID'),
                                $oceanObject->getData('FabricEnName'), 
                                $oceanObject->getData('FabricArName')
                            );
                            $productData->setBrand($brandId);
                            $productData->setCustomAttribute('brand', $brandId);
                            // sync vendor (designer)
                            $vendorId = $this->vendorMapping->getMatching(
                                $oceanObject->getData('BrandID'), 
                                $oceanObject->getData('BrandEnName'),
                                $oceanObject->getData('BrandArName')
                            );
                            $productData->setVendorId($vendorId);
    
                            // Add to config product data before save simple product cause NoSuchEntityException
                            $productData->setName($oProduct['ProductEnName'] ?? ''); // pass ocean data
                            $productData->setArName($oProduct['ProductArName'] ?? '');
                            $oceanConfigurable[$oProduct['SKU']] = array(
                                'product_data' => $productData,
                                'ocean_data' => $oProduct
                            );
    
                            if ($product = $this->updateProduct($productData)) {
                                try{
                                    // save product Arab store
                                    if ($this->config->getArStore() != null 
                                        && isset($oProduct['ProductArName']) && $oProduct['ProductArName']) 
                                    {
                                        $arStoreIds = explode(',', $this->config->getArStore());
                                        $product->setName($oProduct['ProductArName'].'-'.$oceanObject->getData('ColorArName').'-'.$oceanObject->getData('SizeName'));
                                        foreach($arStoreIds as $storeId){
                                            $product->setStoreId($storeId);
                                            $product->save();
                                        }
                                    }
                                }catch(\Exception $e){
                                }
    
                                // Assign product to category
                                try{
                                    if ($categoryId = $this->categoryService->getMagentoCategoryId($oProduct['SubcategoryId'], $oProduct['CategoryId'])) {
                                        $this->linkManagement->assignProductToCategories($product->getSku(), array($categoryId));
                                    }
                                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                                } catch (\Exception $e) {
                                }
                            }
    
                            $hasUpdate = true;
    
                        } catch (NoSuchEntityException $e) {
                            continue;
                        } catch (\Exception $e) {
                            $this->logger->debug($e->getMessage());
                            continue;
                        }
                    }
                }
    
                // Update configurable product
                if (!empty($oceanConfigurable)) {
                    $storeId = (int)$this->storeManager->getStore()->getId();
                    foreach ($oceanConfigurable as $sku => $configData) {
                        $productData = $configData['product_data'];
                        $oProduct = $configData['ocean_data'];
                        $configProduct = $this->updateConfigurableProduct($sku, $productData, $productData->getArName());
                        if ($configProduct) {
                            // Assign product to category
                            try{
                                if ($categoryId = $this->categoryService->getMagentoCategoryId($oProduct['SubcategoryId'], $oProduct['CategoryId'])) {
                                    $this->linkManagement->assignProductToCategories($configProduct->getSku(), array($categoryId));
                                }
                            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                            } catch (\Exception $e) {
                            }
                        }
                    }
                }
                $gmtDate = gmdate('Y-m-d H:i:s');
                $lastSyncTable->setId(null);
                $lastSyncTable->setType(\Simi\Simiocean\Model\SyncTable\Type::TYPE_PRODUCT_UPDATE_CUSTOM);
                $lastSyncTable->setPageNum(1);
                $lastSyncTable->setPageSize(1);
                $lastSyncTable->setRecordNumber($records);
                $lastSyncTable->setUpdatedFrom($gmtDate);
                $lastSyncTable->setUpdatedTo($gmtDate);
                $lastSyncTable->setCreatedAt($gmtDate);
                $lastSyncTable->save();
                
                return $hasUpdate;
            } else {
                if ($lastSyncTable->getId()){
                    $lastSyncTable->setRecordNumber(0);
                    $lastSyncTable->save();
                }
            }
        }
        return false;
    }

    /**
     * Update stock qty from Ocean
     */
    public function syncUpdateStock(){
        $page = 1;
        $size = self::LIMIT;
        $lastDays = 1; // 1 day ago from now

        if ($this->config->getProductSyncNumber() != null) {
            $size = (int)$this->config->getProductSyncNumber();
        }
        // Get time and page number from last synced
        $timeFrom = 'now';
        $timeTo = 'now';
        $lastSyncTable = $this->syncTable->getLastSyncByTime(\Simi\Simiocean\Model\SyncTable\Type::TYPE_PRODUCT_UPDATE_STOCK);
        if ($lastSyncTable->getId() && $lastSyncTable->getPageNum()) {
            if ($lastSyncTable->getRecordNumber() > 0) {
                $page = $lastSyncTable->getPageNum() + 1; //increment 1 page
                $timeTo = $lastSyncTable->getUpdatedTo();
                $timeFrom = $lastSyncTable->getUpdatedFrom();
            } else {
                $timeFrom = $lastSyncTable->getUpdatedTo();
            }
        }

        // ToDate
        $dateTo = new \DateTime($timeTo, new \DateTimeZone('UTC'));
        $dateToGmt = $dateTo->format('Y-m-d H:i:s');
        $dateToParam = $dateTo->getTimestamp();

        // FromDate
        $dateFrom = new \DateTime($timeFrom, new \DateTimeZone('UTC'));
        if ($timeFrom == 'now') {
            $dateFrom->setTimestamp($dateFrom->getTimestamp() - ($lastDays * 86400));
        }
        if (($dateToParam - $dateFrom->getTimestamp()) > ($lastDays * 86400)) {
            $dateFrom->setTimestamp($dateToParam - ($lastDays * 86400));
        }
        $dateFromGmt = $dateFrom->format('Y-m-d H:i:s');
        $dateFromParam = $dateFrom->getTimestamp();

        try{
            $oProducts = $this->productApi->getProductStockUpdate($dateFromParam, $dateToParam, $page, $size);
        }catch(\Exception $e){
            $this->logger->debug(array(
                'Error: Get ocean products updated error. Page = '.$page.', Size = '.$size.', from = '.$dateFromGmt.', to = '.$dateToGmt, 
                $e->getMessage()
            ));
            return false;
        }

        // var_dump($oProducts);die; // testing

        if (is_array($oProducts)) {
            $configurables = array();
            $hasUpdate = false;
            $records = count($oProducts);
            foreach($oProducts as $oProduct){
                if (isset($oProduct['SKU']) && isset($oProduct['BarCode'])
                    && $oProduct['SKU'] && $oProduct['BarCode']
                ) {
                    $productSku = $oProduct['SKU'].'_'.$oProduct['BarCode'];
                    $product = $this->getProductExists($productSku);
                    if ($product && isset($oProduct['StockQuantity'])) {
                        // Not prorected for loop update
                        $product->setStockData(
                            array(
                                'use_config_manage_stock' => 1,
                                'manage_stock' => 1, // manage stock
                                'is_in_stock' => ((int) $oProduct['StockQuantity'] > 0) ? 1 : 0, // Stock Availability of product
                                'qty' => (int) $oProduct['StockQuantity'] // qty of product
                            )
                        );
                        $product->save();
                        // save product Arab store
                        try{
                            if ($this->config->getArStore() != null){
                                $arStoreIds = explode(',', $this->config->getArStore());
                                foreach($arStoreIds as $storeId){
                                    $product->setStoreId($storeId);
                                    $product->save();
                                }
                            }
                        }catch(\Exception $e){}

                        $hasUpdate = true;
                    }
                }
            }

            $lastSyncTable->setId(null);
            $lastSyncTable->setType(\Simi\Simiocean\Model\SyncTable\Type::TYPE_PRODUCT_UPDATE_STOCK);
            $lastSyncTable->setPageNum($page);
            $lastSyncTable->setPageSize($size);
            $lastSyncTable->setRecordNumber($records);
            $lastSyncTable->setUpdatedFrom($dateFromGmt);
            $lastSyncTable->setUpdatedTo($dateToGmt);
            $lastSyncTable->setCreatedAt(gmdate('Y-m-d H:i:s'));
            $lastSyncTable->save();
            return $hasUpdate;
        } else {
            if ($lastSyncTable->getId()){
                $lastSyncTable->setRecordNumber(0);
                $lastSyncTable->save();
            }
            $this->logger->debug(array(
                'Error: Get ocean products updated stock error. Page = '.$page.', Size = '.$size.', from = '.$dateFromGmt.', to = '.$dateToGmt, 
                'Server: '.$oProducts
            ));
        }
        return false;
    }


    /* Old methods bellow */

    /**
     * Sync pull products in processing
     */
    public function syncPull(){
        // Check what is next page to get
        $page = 1;
        $size = self::LIMIT;
        if ($this->config->getProductSyncNumber() != null) {
            $size = (int)$this->config->getProductSyncNumber();
        }
        $lastSyncTable = $this->syncTable->getLastSync(\Simi\Simiocean\Model\SyncTable\Type::TYPE_PRODUCT);
        if ($lastSyncTable->getId() && $lastSyncTable->getPageNum()) {
            $page = $lastSyncTable->getPageNum() + 1; //increment 1 page
        }

        // Get products from ocean with limited
        $isProductSync = false;
        $products = $this->productApi->getProductList($page, $size);

        if ($products && count($products)) {
            $datetime = gmdate('Y-m-d H:i:s');
            $skuGroup = array();
            foreach($products as $oceanProduct){
                if (isset($oceanProduct['SKU']) && isset($oceanProduct['BarCode'])
                    && $oceanProduct['SKU'] && $oceanProduct['BarCode']
                ) {
                    $productModel = $this->convertProductData($oceanProduct); // convert data array to product object model
                    $productModel->setSku($oceanProduct['SKU'].'_'.$oceanProduct['BarCode']);
                    $productModel->setName($productModel->getName().'-'.$oceanProduct['ColorEnName'].'-'.$oceanProduct['SizeName']);
                    $productModel->setUrlKey(str_replace(' ', '-', strtolower($productModel->getName())).'-'.$oceanProduct['BarCode']);
                    if($product = $this->createProduct($productModel)){
                        $simioceanProduct = $this->simioceanProductFactory->create();
                        $simioceanProduct->setSku($oceanProduct['SKU']);
                        $simioceanProduct->setBarcode($oceanProduct['BarCode']);
                        $simioceanProduct->setProductName($oceanProduct['ProductEnName'] ?: $oceanProduct['ProductArName']);
                        $simioceanProduct->setColorId($oceanProduct['ColorID']);
                        $simioceanProduct->setColorName($oceanProduct['ColorEnName'] ?: $oceanProduct['ColorArName']);
                        $simioceanProduct->setSize($oceanProduct['SizeName']);
                        $simioceanProduct->setPrice($oceanProduct['Price']);
                        $simioceanProduct->setQty($oceanProduct['StockQuantity']);
                        $simioceanProduct->setProductId($product->getId());
                        $simioceanProduct->setConfigurableId('');
                        $simioceanProduct->setSyncTime($datetime);
                        $simioceanProduct->setCreatedAt($datetime);
                        try{
                            $simioceanProduct->save();
                        }catch(\Exception $e){
                            $this->logger->debug(array(
                                'Warning! Save simiocean product failed. SKU: '.$simioceanProduct->getSku().', BarCode: '.$simioceanProduct->getBarcode(), 
                                $e->getMessage()
                            ));
                        }
                        // save product Arab store
                        try{
                            if ($this->config->getArStore() != null 
                                && isset($oceanProduct['ProductArName']) && $oceanProduct['ProductArName']) 
                            {
                                $arStoreIds = explode(',', $this->config->getArStore());
                                $product->setName($oceanProduct['ProductArName'].'-'.$oceanProduct['ColorArName'].'-'.$oceanProduct['SizeName']);
                                foreach($arStoreIds as $storeId){
                                    $product->setStoreId($storeId);
                                    $product->save();
                                }
                            }
                        }catch(\Exception $e){}
                        $skuGroup[$oceanProduct['SKU']] = $oceanProduct; // It means only when product created then go to create the configurable product
                        $isProductSync = true;
                    }
                }
            }
            
            // Create configurable products and setting Associated Products
            foreach($skuGroup as $sku => $oceanProduct){
                $configurableProductModel = $this->convertProductData($oceanProduct);
                $configurableProductModel->setSku($sku);
                $configurableProductModel->setUrlKey(str_replace(' ', '-', strtolower($configurableProductModel->getName())).'-'.$sku);
                $assocProductIds = $this->getProductIds($sku);
                if($savedProduct = $this->createConfigurableProduct($configurableProductModel, $assocProductIds)){
                    if (isset($oceanProduct['CategoryId']) && isset($oceanProduct['SubcategoryId'])) {
                        if ($categoryId = $this->categoryService->getMagentoCategoryId($oceanProduct['SubcategoryId'], $oceanProduct['CategoryId'])) {
                            try{
                                $this->linkManagement->assignProductToCategories($savedProduct->getSku(), array($categoryId));
                            } catch (\Exception $e) {
                                $this->logger->debug(array(
                                    'Product sync pull: Assign product to catalog error. Catalog Id: '.$categoryId,
                                    $e->getMessage()
                                ));
                            }
                        }
                    }
                    // Save parent product id for simiocean_product synced
                    foreach($assocProductIds as $productId){
                        $simioceanProduct = $this->getSimioceanSynced($productId);
                        $simioceanProduct->setParentId($savedProduct->getId());
                        try{
                            $simioceanProduct->save();
                        }catch(\Exception $e){
                            $this->logger->debug(
                                'Can not update parent product_id '.$savedProduct->getId()
                                .' to SimioceanProduct sync table. Message: '.$e->getMessage()
                            );
                        }
                    }
                }
            }

            if ($isProductSync) {
                $syncTable = $this->syncTableFactory->create(); /** @var object Simi\Simiocean\Model\SyncTable */
                $syncTable->setType(\Simi\Simiocean\Model\SyncTable\Type::TYPE_PRODUCT)
                    ->setPageNum($page)
                    ->setPageSize($size)
                    ->setRecordNumber(count($products))
                    ->setCreatedAt($datetime)
                    ->save();
                return true;
            }
        }
        return false;
    }

    /**
     * Sync pull product by skus
     */
    public function syncPullSku($sku){
        $isProductSync = false;
        $products = $this->productApi->getProductSku($sku); // Get products from ocean with sku
        if ($products && count($products)) {
            $this->addSyncMessages('synced by sku');
            $datetime = gmdate('Y-m-d H:i:s');
            $skuGroup = array();
            foreach($products as $oProduct){
                if (isset($oProduct['SKU']) && $oProduct['SKU'] &&
                    isset($oProduct['BarCode']) && $oProduct['BarCode']
                ) {
                    $productModel = $this->convertProductData($oProduct); // convert data array to product object model
                    $productModel->setSku($this->_getProductSku($oProduct));
                    $productModel->setName($this->_getProductName($oProduct));
                    $productModel->setUrlKey($this->_getProductUrlKey($oProduct));
                    if($product = $this->createProduct($productModel)){
                        try{
                            $tableData = $this->_getProductTableData($oProduct);
                            // check if exist in table
                            $oProductTable = $this->getOceanProductExist($oProduct['SKU'], $oProduct['BarCode']);
                            if ($oProductTable->getId()) {
                                unset($tableData['created_at']);
                                $oProductTable->addData($tableData);
                            } else {
                                $oProductTable = $this->simioceanProductFactory->create();
                                $oProductTable->setData($tableData);
                            }
                            $oProductTable->setProductId($product->getId());
                            $oProductTable->setMessage(implode(', ', $this->getSyncMessages()));
                            $oProductTable->save();
                        }catch(\Exception $e){
                            $this->logger->debug(array(
                                'Warning! Save simiocean product failed. SKU: '.$oProductTable->getSku().', BarCode: '.$oProductTable->getBarcode(), 
                                $e->getMessage()
                            ));
                        }
                        // save product Arab store
                        try{
                            if ($this->config->getArStore() != null && 
                                isset($oProduct['ProductArName']) && $oProduct['ProductArName']) 
                            {
                                $arStoreIds = explode(',', $this->config->getArStore());
                                $product->setName($oProduct['ProductArName'].'-'.$oProduct['ColorArName'].'-'.$oProduct['SizeName']);
                                foreach($arStoreIds as $storeId){
                                    $product->setStoreId($storeId);
                                    $product->save();
                                }
                            }
                        }catch(\Exception $e){}
                        $skuGroup[$oProduct['SKU']] = $oProduct; // It means only when product created then go to create the configurable product
                        $isProductSync = true;
                    }
                }
            }
            
            // Create configurable products and setting Associated Products
            foreach($skuGroup as $sku => $oProduct){
                $configurableProductModel = $this->convertProductData($oProduct);
                $configurableProductModel->setSku($sku);
                $configurableProductModel->setUrlKey(str_replace(' ', '-', strtolower($configurableProductModel->getName())).'-'.$sku);
                $assocProductIds = $this->getProductIds($sku);
                $arName = isset($oProduct['ProductArName']) ? $oProduct['ProductArName'] : $configurableProductModel->getName();
                if($configurableProduct = $this->createConfigurableProduct($configurableProductModel, $assocProductIds, $arName)){
                    if (isset($oProduct['CategoryId']) && isset($oProduct['SubcategoryId'])) {
                        if ($categoryId = $this->categoryService->getMagentoCategoryId($oProduct['SubcategoryId'], $oProduct['CategoryId'])) {
                            try{
                                $this->linkManagement->assignProductToCategories($configurableProduct->getSku(), array($categoryId));
                            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                                $this->categoryService->syncCategoryById($oProduct['CategoryId'], $oProduct['SubcategoryId']);
                                if ($categoryId = $this->categoryService->getMagentoCategoryId($oProduct['SubcategoryId'], $oProduct['CategoryId'])){
                                    $this->linkManagement->assignProductToCategories($configurableProduct->getSku(), array($categoryId));
                                }
                            } catch (\Exception $e) {
                                $this->logger->debug(array(
                                    'Product sync pull: Assign product to catalog error. Catalog Id: '.$categoryId,
                                    $e->getMessage()
                                ));
                            }
                        }
                    }
                    // Save parent product id for simiocean_product synced
                    foreach($assocProductIds as $productId){
                        try{
                            $oProductTable = $this->getSimioceanSynced($productId);
                            $oProductTable->setParentId($configurableProduct->getId());
                            $oProductTable->save();
                        }catch(\Exception $e){
                            $this->logger->debug(
                                'Can not update parent product_id '.$configurableProduct->getId()
                                .' to SimioceanProduct sync table. Message: '.$e->getMessage()
                            );
                        }
                    }
                }
            }

            if ($isProductSync) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get product name
     */
    protected function _getProductName($oProduct){
        if (!isset($oProduct['ProductEnName']) || !$oProduct['ProductEnName']) $oProduct['ProductEnName'] = __('No en name');
        if (!isset($oProduct['ColorEnName'])) $oProduct['ColorEnName'] = __('No color en name');
        if (!isset($oProduct['SizeName'])) $oProduct['SizeName'] = __('No size name');
        return $oProduct['ProductEnName'].'-'.$oProduct['ColorEnName'].'-'.$oProduct['SizeName'];
    }

    /**
     * Get product sku
     */
    protected function _getProductSku($oProduct){
        if (!isset($oProduct['SKU'])) $oProduct['SKU'] = 'No Sku';
        if (!isset($oProduct['BarCode'])) $oProduct['BarCode'] = 'No BarCode';
        return $oProduct['SKU'].'_'.$oProduct['BarCode'];
    }

    /**
     * Get product url key
     */
    protected function _getProductUrlKey($oProduct, $prefix = ''){
        if (!isset($oProduct['BarCode'])) $oProduct['BarCode'] = __('No barcode');
        if ($prefix) {
            return str_replace(' ', '-', strtolower($prefix) . '-' . $oProduct['BarCode']);
        }
        return str_replace(' ', '-', $this->_getProductName($oProduct) . '-' . $oProduct['BarCode']);
    }

    /**
     * Get product table data
     * @return array
     */
    protected function _getProductTableData($oProduct){
        $data = [];
        try{
            $datetime = gmdate('Y-m-d H:i:s');
            $data = [
                'sku' => $oProduct['SKU'],
                'barcode' => $oProduct['BarCode'],
                'product_name' => $oProduct['ProductEnName'] ?: $oProduct['ProductArName'],
                'color_id' => $oProduct['ColorID'],
                'color_name' => $oProduct['ColorEnName'] ?: $oProduct['ColorArName'],
                'size' => $oProduct['SizeName'],
                'price' => $oProduct['Price'],
                'qty' => $oProduct['StockQuantity'],
                'product_id' => '',
                'configurable_id' => '',
                'sync_time' => $datetime,
                'created_at' => $datetime,
            ];
        }catch(\Exception $e){}
        return $data;
    }

    /**
     * Sync pull product from ocean to website
     */
    /* public function syncUpdatePull(){
        $page = 1;
        $size = self::LIMIT;
        $lastDays = 1; // 1 day ago from now

        if ($this->config->getProductSyncNumber() != null) {
            $size = (int)$this->config->getProductSyncNumber();
        }
        // Get time and page number from last synced
        $timeFrom = 'now';
        $timeTo = 'now';
        $lastSyncTable = $this->syncTable->getLastSyncByTime(\Simi\Simiocean\Model\SyncTable\Type::TYPE_PRODUCT_UPDATE);
        if ($lastSyncTable->getId() && $lastSyncTable->getPageNum()) {
            if ($lastSyncTable->getRecordNumber() > 0) {
                $page = $lastSyncTable->getPageNum() + 1; //increment 1 page
                $timeTo = $lastSyncTable->getUpdatedTo();
                $timeFrom = $lastSyncTable->getUpdatedFrom();
            } else {
                $timeFrom = $lastSyncTable->getUpdatedTo();
            }
        }

        // ToDate
        $dateTo = new \DateTime($timeTo, new \DateTimeZone('UTC'));
        $dateToGmt = $dateTo->format('Y-m-d H:i:s');
        $dateToParam = $dateTo->getTimestamp();

        // FromDate
        $dateFrom = new \DateTime($timeFrom, new \DateTimeZone('UTC'));
        if ($timeFrom == 'now') {
            $dateFrom->setTimestamp($dateFrom->getTimestamp() - ($lastDays * 86400));
        }
        if (($dateToParam - $dateFrom->getTimestamp()) > ($lastDays * 86400)) {
            $dateFrom->setTimestamp($dateToParam - ($lastDays * 86400));
        }
        $dateFromGmt = $dateFrom->format('Y-m-d H:i:s');
        $dateFromParam = $dateFrom->getTimestamp();

        try{
            $oProducts = $this->productApi->getProductFilter($dateFromParam, $dateToParam, $page, $size);
        }catch(\Exception $e){
            $this->logger->debug(array(
                'Error: Get ocean products updated error. Page = '.$page.', Size = '.$size.', from = '.$dateFromGmt.', to = '.$dateToGmt, 
                $e->getMessage()
            ));
            return false;
        }

        if (is_array($oProducts)) {
            $hasUpdate = false;
            $records = count($oProducts);
            $lastOceanObject = array();
            foreach ($oProducts as $oProduct) {
                if (isset($oProduct['SKU']) && isset($oProduct['BarCode'])
                    && $oProduct['SKU'] && $oProduct['BarCode']
                ) {
                    $oceanObject = $this->dataObjectFactory->create();
                    $oceanObject->setData($oProduct);

                    $productData = $this->convertProductData($oProduct); // convert data array to product object model
                    $productData->setSku($oProduct['SKU'].'_'.$oProduct['BarCode']);
                    $productData->setName($productData->getName().'-'. $oceanObject->getData('ColorEnName') .'-'.$oceanObject->getData('SizeName'));
                    $productData->setUrlKey(str_replace(' ', '-', strtolower($productData->getName())).'-'.$oceanObject->getData('BarCode'));
                    
                    if ($oceanProduct = $this->getOceanProduct($oProduct['SKU'], $oProduct['BarCode'])) {
                        $syncTime = new \DateTime($oceanProduct->getSyncTime(), new \DateTimeZone('UTC'));
                        $modifyTime = new \DateTime(gmdate('Y-m-d H:i:s', $oceanObject->getData('SPECModificationDate')), new \DateTimeZone('UTC'));
                        if ($modifyTime > $syncTime) {
                            if ($product = $this->updateProduct($productData)) {
                                try{
                                    $oceanProduct->setSku($oProduct['SKU']);
                                    $oceanProduct->setBarcode($oProduct['BarCode']);
                                    $oceanProduct->setProductName($oceanObject->getData('ProductEnName') ?: $oceanObject->getData('ProductArName'));
                                    $oceanProduct->setColorId($oceanObject->getData('ColorID'));
                                    $oceanProduct->setColorName($oceanObject->getData('ColorEnName') ?: $oceanObject->getData('ColorArName'));
                                    $oceanProduct->setSize($oceanObject->getData('SizeName'));
                                    $oceanProduct->setPrice($oceanObject->getData('Price'));
                                    $oceanProduct->setQty($oceanObject->getData('StockQuantity'));
                                    $oceanProduct->setProductId($product->getId());
                                    $oceanProduct->setSyncTime(gmdate('Y-m-d H:i:s'));
                                    $oceanProduct->setDirection(\Simi\Simiocean\Model\Product::DIR_OCEAN_TO_WEB);
                                    $oceanProduct->setStatus(\Simi\Simiocean\Model\SyncStatus::SUCCESS);
                                    $oceanProduct->save();
                                }catch(\Exception $e){
                                    $this->logger->debug(array(
                                        'Warning! Save simiocean product failed. SKU: '.$oceanProduct->getSku().', BarCode: '.$oceanProduct->getBarcode(), 
                                        $e->getMessage()
                                    ));
                                }
                                // save product Arab store
                                try{
                                    if ($this->config->getArStore() != null 
                                        && isset($oProduct['ProductArName']) && $oProduct['ProductArName']) 
                                    {
                                        $arStoreIds = explode(',', $this->config->getArStore());
                                        $product->setName($oProduct['ProductArName'].'-'.$oceanObject->getData('ColorArName').'-'.$oceanObject->getData('SizeName'));
                                        foreach($arStoreIds as $storeId){
                                            $product->setStoreId($storeId);
                                            $product->save();
                                        }
                                    }
                                }catch(\Exception $e){}

                                $lastOceanObject[$oProduct['SKU']] = $oceanObject;
                                $hasUpdate = true;
                            }
                        }
                    }
                }
            }

            // Update configurable product name, category
            if (!empty($lastOceanObject)) {
                $storeId = (int)$this->storeManager->getStore()->getId();
                foreach ($lastOceanObject as $sku => $oObject) {
                    if ($configurableProduct = $this->getProductExists($sku, true, $storeId)) {
                        // Update category to change with ocean sub product's category
                        if ($oObject->getData('CategoryId') && $oObject->getData('SubcategoryId')) {
                            if ($categoryId = $this->categoryService->getMagentoCategoryId(
                                $oObject->getData('SubcategoryId'), $oObject->getData('CategoryId')
                            )) {
                                try{
                                    $this->linkManagement->assignProductToCategories($configurableProduct->getSku(), array($categoryId));
                                } catch (\Exception $e) {
                                    $this->logger->debug(array(
                                        'Product sync update pull: Assign configurable product to catalog error. Catalog Id: '.$categoryId,
                                        $e->getMessage()
                                    ));
                                }
                            }
                        }
                        // Update product name
                        $configurableProduct->setName($oObject->getData('ProductEnName'));
                        $urlKey = str_replace(' ', '-', strtolower($configurableProduct->getName())).'-'.$sku;
                        $configurableProduct->setUrlKey($urlKey);
                        $configurableProduct->setStoreId(0);
                        $configurableProduct->save();
                        // save product for Arab store
                        try{
                            if ($this->config->getArStore() != null && $oObject->getData('ProductArName')) {
                                $arStoreIds = explode(',', $this->config->getArStore());
                                $configurableProduct->setName($oObject->getData('ProductArName'));
                                foreach($arStoreIds as $storeId){
                                    $configurableProduct->setStoreId($storeId);
                                    $configurableProduct->save();
                                }
                            }
                        }catch(\Exception $e){}
                    }
                }
            }

            $lastSyncTable->setId(null);
            $lastSyncTable->setType(\Simi\Simiocean\Model\SyncTable\Type::TYPE_PRODUCT_UPDATE);
            $lastSyncTable->setPageNum($page);
            $lastSyncTable->setPageSize($size);
            $lastSyncTable->setRecordNumber($records);
            $lastSyncTable->setUpdatedFrom($dateFromGmt);
            $lastSyncTable->setUpdatedTo($dateToGmt);
            $lastSyncTable->setCreatedAt(gmdate('Y-m-d H:i:s'));
            $lastSyncTable->save();
            return $hasUpdate;
        } else {
            if ($lastSyncTable->getId()){
                $lastSyncTable->setRecordNumber(0);
                $lastSyncTable->save();
            }
            $this->logger->debug(array(
                'Error: Get ocean products updated error. Page = '.$page.', Size = '.$size.', from = '.$dateFromGmt.', to = '.$dateToGmt, 
                'Server: '.$oProducts
            ));
        }
        return false;
    } */

    /**
     * Get simple product ids created
     * @param string $sku of parent product
     * @return array
     */
    public function getProductIds($sku){
        $connection = $this->simioceanProductResource->getConnection();
        $tableName = $this->simioceanProductResource->getTable('simiocean_product');
        $bind = ['sku' => $sku];
        $select = $connection->select()
            ->from($tableName, 'product_id')
            ->where('sku = :sku');
        $data = $connection->fetchCol($select, $bind);
        return array_values($data);
    }

    /**
     * Get Simiocean product synced from table simiocean_product synced by productId
     * For now, the product_id column is not unique
     * @param int $productId of magento product
     * @return \Simi\Simiocean\Model\Product|null
     */
    public function getSimioceanSynced($productId){
        $model = $this->simioceanProductFactory->create();
        $this->simioceanProductResource->load($model, $productId, 'product_id');
        if ($model && $model->getId()) {
            return $model;
        }
        return null;
    }


    /**
     * Get item of the ocean product from table
     * @return object
     */
    public function getOceanProduct($sku, $barcode){
        if ($sku && $barcode) {
            $model = $this->simioceanProductFactory->create();
            $collection = $model->getCollection();
            $collection->addFieldToFilter('sku', $sku)
                ->addFieldToFilter('barcode', $barcode)
                ->getSelect()
                ->where('product_id IS NOT NULL')
                ->limit(1);
            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }
        return false;
    }

    /**
     * Get ocean product exist by sku and barcode
     * @param string $sku
     * @param string $barcode
     * @return object
     */
    public function getOceanProductExist($sku, $barcode){
        if ($sku && $barcode) {
            $model = $this->simioceanProductFactory->create();
            $collection = $model->getCollection();
            $collection->addFieldToFilter('sku', $sku)
                ->addFieldToFilter('barcode', $barcode)
                ->getSelect()
                ->limit(1);
            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }
        return false;
    }

    /**
     * Convert to magento product data model from ocean raw data array
     * @return ProductInterface
     */
    public function convertProductData($data){
        $dataObject = $this->objectFactory->create(SimioceanProductInterface::class, []);
        $dataObject->setData($data);
        // Convert to SimioceanProduct data array
        $data = $this->dataObjectProcessor->buildOutputDataArray($dataObject, SimioceanProductInterface::class);
        // Add data array to Magento Product object
        /** @var Magento\Catalog\Model\Product */
        $productModel = $this->productInterfaceFactory->create();
        $this->dataObjectHelper->populateWithArray($productModel, $data, ProductInterface::class);
        $productModel->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

        /* Convert custom_attributes (color, size, ...) */
        // $productModel->setCustomAttribute('tax_class_id', $taxClassId);
        // $productModel->setCustomAttribute('description', $productModel->getDescription());
        $productModel->setColor($this->colorMapping->getMatchingColor(
            $dataObject->getColorOnlineName() ?: $dataObject->getColorEnName(), $dataObject->getColorArName()
        ));
        $productModel->setSize($this->sizeMapping->getMatchingSize(
            $dataObject->getSizeName()
        ));
        $productModel->setCustomAttribute('color', $productModel->getColor());
        $productModel->setCustomAttribute('size', $productModel->getSize());

        if (!(int)$productModel->getSpecialPrice()) {
            $productModel->setSpecialPrice(''); //reset special price
            $productModel->setCustomAttribute('special_price', '');
        }
        
        return $productModel;
    }

    /**
     * Add new simple product to magento
     * @param ProductInterface $productModel
     * @return bool
     */
    protected function createProduct($productModel){
        $productModel->setStockData(
            array(
                'use_config_manage_stock' => 1,
                'manage_stock' => 1, // manage stock
                'is_in_stock' => ((int)$productModel->getQty() > 0) ? 1 : 0, // Stock Availability of product
                'qty' => (int)$productModel->getQty() // qty of product
            )
        );
        $createdAt = gmdate("Y-m-d H:i:s");
        $productModel->setCreatedAt($createdAt);
        $productModel->setUpdatedAt($createdAt);
        $productModel->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $productModel->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE);
        if (class_exists('\Vnecoms\VendorsProduct\Model\Source\Approval')) {
            $productModel->setApproval(\Vnecoms\VendorsProduct\Model\Source\Approval::STATUS_APPROVED);//Vnecoms attribute
        }
        //Find default attributeSet id and set attribute_set_id
        if ($attributeSet = $this->getAttributeSet()) {
            $productModel->setAttributeSetId($attributeSet->getId());
        }
        try{
            /**
             * @var $productModel ProductInterface 
             * @return \Magento\Catalog\Api\Data\ProductInterface
             */
            $productModel->setIsOcean(1);
            $product = $this->getProductExists($productModel->getSku());
            if (!$product) {
                $product = $this->productRepository->save($productModel);
            } else {
                // If override old product then write code here (this is for simple product)
                $data = $this->dataObjectProcessor->buildOutputDataArray($productModel, ProductInterface::class);
                /** @var Magento\Catalog\Model\Product */
                $this->dataObjectHelper->populateWithArray($product, $data, ProductInterface::class); // Add data array to Magento Product object
                $product = $this->productRepository->save($product);
            }
            return $product;
        } catch(\Exception $e) {
            $this->logger->debug(array('Save product error: '.$e->getMessage(), $productModel->getData()));
            return false;
        }
        return false;
    }

    /**
     * Add new configurable product to magento or update data to existing product
     * @param ProductInterface $productModel
     * @param string|array $associatedProductIds
     * @param string $arName in Arabic name
     * @return bool
     */
    protected function createConfigurableProduct($productModel, $associatedProductIds, $arName = ''){
        $productModel->setTypeId('configurable');
        $productModel->setStockData(
            array(
                'use_config_manage_stock' => 1,
                'manage_stock' => 1, // manage stock
                'is_in_stock' => 1, // Stock Availability of product
                'qty' => 999 // qty of product
            )
        );
        $createdAt = gmdate("Y-m-d H:i:s");
        $productModel->setCreatedAt($createdAt);
        $productModel->setUpdatedAt($createdAt);
        if (class_exists('\Vnecoms\VendorsProduct\Model\Source\Approval')) {
            $productModel->setApproval(\Vnecoms\VendorsProduct\Model\Source\Approval::STATUS_APPROVED);//Vnecoms attribute
        }
        //Find default attributeSet id and set attribute_set_id
        if ($attributeSet = $this->getAttributeSet()) {
            $productModel->setAttributeSetId($attributeSet->getId());
        }
        try{
            /**
             * @var $productModel ProductInterface 
             * @return \Magento\Catalog\Api\Data\ProductInterface
             */
            $productModel->setIsOcean(1);
            if ($savedProduct = $this->getProductExists($productModel->getSku())) {
                // Update data to existing product
                $data = $this->dataObjectProcessor->buildOutputDataArray($productModel, ProductInterface::class);
                $this->dataObjectHelper->populateWithArray($savedProduct, $data, ProductInterface::class);
                $savedProduct->setStoreId(0);
                $savedProduct->save(); // Save existing product
            } else {
                $productModel->setStoreId(0);
                $savedProduct = $this->productRepository->save($productModel);  // Save new product
            }
            
            // Save again with associated product Ids
            if ($savedProduct && $savedProduct->getId()) {
                $attributeIds = $this->getAttributeIds(array('color', 'size')); // Super Attribute Ids Used To Create Configurable Product
                try{
                    // Create attribute map with configurable product expect to skip error for already existed
                    $position = 0;
                    foreach ($attributeIds as $attributeId) {
                        $data = array(
                            'attribute_id' => $attributeId, 
                            'product_id' => $savedProduct->getId(),
                            'position' => $position
                        );
                        $position++;
                        $attributeModel = $this->configurableAttributeFactory->create();
                        $attributeModel->setData($data)->save();
                    }
                }catch(\Exception $e){}
                $this->productTypeConfigurable->setUsedProductAttributeIds($attributeIds, $savedProduct);
                $savedProduct->setNewVariationsAttributeSetId($attributeSet->getId()); // Setting Attribute Set Id
                if (!is_array($associatedProductIds)) $associatedProductIds = array($associatedProductIds);
                $savedProduct->setAssociatedProductIds($associatedProductIds);// Setting Associated Products
                $savedProduct->setCanSaveConfigurableAttributes(true);
                $configurableAttributesData = $this->productTypeConfigurable->getConfigurableAttributesAsArray($savedProduct);
                $savedProduct->setConfigurableAttributesData($configurableAttributesData);
                $savedProduct->save();

                // save product for Arab store
                try{
                    if ($this->config->getArStore() != null) {
                        $backupProduct = clone $savedProduct;
                        $arStoreIds = explode(',', $this->config->getArStore());
                        $savedProduct->setName($arName);
                        foreach($arStoreIds as $storeId){
                            $savedProduct->setStoreId($storeId);
                            $savedProduct->save();
                        }
                        $savedProduct = $backupProduct;
                    }
                }catch(\Exception $e){}
            }
            return $savedProduct;
        } catch(\Exception $e) {
            $data = array(
                'name' => $productModel->getName(),
                'sku' => $productModel->getSku(),
                'size' => $productModel->getSize(),
                'color' => $productModel->getColor(),
                'brand' => $productModel->getBrand()
            );
            $this->logger->debug(array('Save configurable product error: '.$e->getMessage(), $data));
            return false;
        }
        return false;
    }

    /**
     * Update configurable product to magento
     * @param ProductInterface $productModel
     * @param string|array $associatedProductIds
     * @param string $arName in Arabic name
     * @return Catalog\Product\Model\Product|bool
     */
    protected function updateConfigurableProduct($sku, $productModel, $arName = ''){
        if ($savedProduct = $this->getProductExists($sku)) {
            $id = $savedProduct->getId();
            // $savedProduct->setTypeId('configurable');
            // $savedProduct->setStockData(
            //     array(
            //         'use_config_manage_stock' => 1,
            //         'manage_stock' => 1, // manage stock
            //         'is_in_stock' => 1, // Stock Availability of product
            //         'qty' => 999 // qty of product
            //     )
            // );
            
            try{
                $dateTime = gmdate("Y-m-d H:i:s");
                /**
                 * @var $productModel ProductInterface 
                 * @return \Magento\Catalog\Api\Data\ProductInterface
                 */
                $productModel->setIsOcean(1);
                
                // Update data to existing product
                $data = $this->dataObjectProcessor->buildOutputDataArray($productModel, ProductInterface::class);
                $allowed = array(
                    'name' => 1,
                    'price' => 1
                );
                $filtered = array_filter(
                    $data,
                    function ($key) use ($allowed) { // N.b. $val, $key not $key, $val
                        return isset($allowed[$key]) && ($allowed[$key] === 1);
                    },
                    ARRAY_FILTER_USE_KEY
                );
                $this->dataObjectHelper->populateWithArray($savedProduct, $filtered, ProductInterface::class);

                $savedProduct->setId($id);
                $savedProduct->setSku($sku);

                $extensionAttributes = $data['extension_attributes'] ?? array();
                $categoryLinks = $extensionAttributes['category_links'] ?? array();
                $categoryIds = [];
                foreach($categoryLinks as $cate){
                    $categoryIds[] = $cate['category_id'];
                }
                try{
                    if (!empty($categoryIds)) { // need update true category
                        $this->linkManagement->assignProductToCategories($sku, $categoryIds);
                    }
                } catch (\Exception $e) {}

                if (class_exists('\Vnecoms\VendorsProduct\Model\Source\Approval')) {
                    $savedProduct->setApproval(\Vnecoms\VendorsProduct\Model\Source\Approval::STATUS_APPROVED);//Vnecoms attribute
                }

                $savedProduct->setUpdatedAt($dateTime);
                $savedProduct->setDescription($productModel->getDescription());
                $savedProduct->setBrand($productModel->getBrand());
                $savedProduct->setVendorId($productModel->getVendorId());
                $savedProduct->setStoreId(0);
                
                $savedProduct->setCanSaveConfigurableAttributes(true);
                $configurableAttributesData = $this->productTypeConfigurable->getConfigurableAttributesAsArray($savedProduct);
                $savedProduct->setConfigurableAttributesData($configurableAttributesData);
                $savedProduct->save();

                $website = $this->storeManager->getWebsite();
                $_storeIds = $website->getStoreIds();
                $arStores = $this->config->getArStore();
                $arStoreIds = explode(',', $arStores);

                // Save other not ar store
                foreach($_storeIds as $_storeId){
                    if ($_storeId != 0 && !in_array($_storeId, $arStoreIds)) {
                        $savedProduct->setStoreId((int)$_storeId);
                        // $savedProduct->setName(false);// set use_default value for name
                        // $savedProduct->addAttributeUpdate('name', false, $_storeId);
                        $savedProduct->save();
                        // Delete custom name in store view data
                        $resource = $savedProduct->getResource();
                        $connection = $resource->getConnection();
                        $eavAttr = $resource->getTable('eav_attribute');
                        $sql = "SELECT attribute_id FROM $eavAttr WHERE entity_type_id = 4 AND attribute_code = 'name'";
                        $attributeId = $connection->fetchOne($sql);
                        // var_dump($attributeId);die;
                        $entityId = $savedProduct->getId();
                        $varcharTable = $resource->getTable('catalog_product_entity_varchar');
                        $sql = "DELETE FROM $varcharTable WHERE entity_id = $entityId AND store_id = $_storeId AND attribute_id = $attributeId";
                        $connection->query($sql);
                    }
                }
    
                // save product for Arab store
                try{
                    if (!empty($arStoreIds)) {
                        $backupProduct = clone $savedProduct;
                        $savedProduct->setName($arName);
                        foreach($arStoreIds as $storeId){
                            $savedProduct->setStoreId($storeId);
                            $savedProduct->save();
                        }
                        $savedProduct = $backupProduct;
                    }
                }catch(\Exception $e){}
    
                return $savedProduct;

            } catch(\Exception $e) {
                $data = array(
                    'name' => $productModel->getName(),
                    'sku' => $productModel->getSku(),
                    'size' => $productModel->getSize(),
                    'color' => $productModel->getColor(),
                    'brand' => $productModel->getBrand()
                );
                $this->logger->debug(array('Save configurable product error: '.$e->getMessage(), $data));
            }
        }
        return false;
    }

    /**
     * Edit/update product to website
     * @param \Magento\Catalog\Api\Data\ProductInterface $productModel product model data to update
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function updateProduct($productModel){
        $store = (int)$this->storeManager->getStore()->getId();
        if ($existingProduct = $this->getProductExists($productModel->getSku(), true, 0)) {
            try{
                $productModel->setStockData(
                    array(
                        'use_config_manage_stock' => 1,
                        'manage_stock' => 1, // manage stock
                        'is_in_stock' => ((int)$productModel->getQty() > 0) ? 1 : 0, // Stock Availability of product
                        'qty' => (int)$productModel->getQty() // qty of product
                    )
                );
                $productModel->setUpdatedAt(gmdate("Y-m-d H:i:s"));
                $productModel->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
                $productModel->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE);
                if (class_exists('\Vnecoms\VendorsProduct\Model\Source\Approval')) {
                    $productModel->setApproval(\Vnecoms\VendorsProduct\Model\Source\Approval::STATUS_APPROVED);//Vnecoms attribute
                }
                //Find default attributeSet id and set attribute_set_id
                if ($attributeSet = $this->getAttributeSet()) {
                    $productModel->setAttributeSetId($attributeSet->getId());
                }
                $productModel->setIsOcean(1);

                $productModel->setData(
                    $this->productResourceModel->getLinkField(),
                    $existingProduct->getData($this->productResourceModel->getLinkField())
                );
                if (!$productModel->hasData(\Magento\Catalog\Model\Product::STATUS)) {
                    $productModel->setStatus($existingProduct->getStatus());
                }

                /** @var ProductExtension $extensionAttributes */
                $extensionAttributes = $productModel->getExtensionAttributes();
                if (empty($extensionAttributes->__toArray())) {
                    $productModel->setExtensionAttributes($existingProduct->getExtensionAttributes());
                }

                $productDataArray = $this->extensibleDataObjectConverter
                    ->toNestedArray($productModel, [], \Magento\Catalog\Api\Data\ProductInterface::class);
                $productDataArray = array_replace($productDataArray, $productModel->getData());

                $website = $this->storeManager->getWebsite();
                $_storeIds = $website->getStoreIds();
                // unset($productDataArray['media_gallery']);
                foreach ($productDataArray as $key => $value) {
                    if (!$key || !$value) continue;
                    $existingProduct->setData($key, $value);
                }
                // if (isset($productDataArray['media_gallery'])) {
                //     $this->processMediaGallery($product, $productDataArray['media_gallery']['images']);
                // }
                if (!$existingProduct->getOptionsReadonly()) {
                    $existingProduct->setCanSaveCustomOptions(true);
                }
                $arStoreIds = [];
                if ($this->config->getArStore() != null) {
                    $arStoreIds = explode(',', $this->config->getArStore());
                }
                $existingProduct->setStoreId(0);
                $existingProduct->save();
                foreach($_storeIds as $_storeId){
                    if ($_storeId != 0 && !in_array($_storeId, $arStoreIds)) {
                        $existingProduct->setStoreId((int)$_storeId);
                        // $existingProduct->setName(false);// set use_default value for name
                        // $existingProduct->addAttributeUpdate('name', false, $_storeId);
                        $existingProduct->save();
                        // Delete custom name in store view data
                        $resource = $existingProduct->getResource();
                        $connection = $resource->getConnection();
                        $eavAttr = $resource->getTable('eav_attribute');
                        $sql = "SELECT attribute_id FROM $eavAttr WHERE entity_type_id = 4 AND attribute_code = 'name'";
                        $attributeId = $connection->fetchOne($sql);
                        $entityId = $existingProduct->getId();
                        $varcharTable = $resource->getTable('catalog_product_entity_varchar');
                        $sql = "DELETE FROM $varcharTable WHERE entity_id = $entityId AND store_id = $_storeId AND attribute_id = $attributeId";
                        $connection->query($sql);
                    }
                }
                $existingProduct->setStoreId(0);
                return $existingProduct;
            } catch(\Exception $e) {
                $this->logger->debug(array('Save product error: '.$e->getMessage()));
                return false;
            }
        }
        return false;
    }

    /**
     * Check product sku exists
     * @param string $sku
     * @param boolean $editable
     * @param int $storeId
     * @param boolean $forceReload
     * @return \Magento\Catalog\Model\Product|false
     */
    public function getProductExists($sku, $editable = false, $storeId = null, $forceReload = false){
        try {
            $product = $this->productRepository->get($sku, $editable, $storeId, $forceReload);
            if ($product->getId()) {
                return $product;
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e){
            return false;
        }
        return false;
    }

    /**
     * Get product attribute ids by attribute codes, etc color, size
     * @param array $codes is array attribute code
     * @return array of int
     */
    protected function getAttributeIds($codes){
        if (!is_array($codes)) {
            $codes = array($codes);
        }
        $attrIds = array();
        foreach($codes as $attrCode){
            $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attrCode);
            if ($attribute && $attribute->getId()) {
                $attrIds[] = $attribute->getId();
            }
        }
        return $attrIds;
    }

    /**
     * Get attribute set id
     * @param string $name attribute set name
     * @return object|null
     */
    protected function getAttributeSet($name = 'default'){
        if (!$this->attributeSet) {
            $attributeSetSearchCriteria = $this->objectFactory->create(\Magento\Framework\Api\SearchCriteriaInterface::class, []);
            $attributeSetItems = $this->attributeSetRepository->getList($attributeSetSearchCriteria)->getItems();
            $attributeSet = $this->attributeSet;
            foreach($attributeSetItems as $attrSet){
                if (strpos(strtolower($attrSet->getAttributeSetName()), $name) !== FALSE) {
                    $attributeSet = $attrSet;
                } elseif(is_object($attributeSet) && $attrSet->getSortOrder() > $attributeSet->getSortOrder()){
                    $attributeSet = $attrSet;
                } elseif(!$attributeSet) {
                    $attributeSet = $attrSet;
                }
            }
            $this->attributeSet = $attributeSet;
        }
        return $this->attributeSet;
    }

    /**
     * Check if has new products and return them
     * @return false|array
     */
    protected function checkNewProducts(){
        return true;
    }

    /**
     * Resync all product from Ocean to Website
     */
    public function resyncAll(){
        $oProducts = $this->getOceanProductsSyncedBefore('2020-10-19 04:30:00', 10);
        $status = false;
        if ($oProducts) {
            foreach($oProducts as $item){
                $this->resetSyncMessages();
                $this->addSyncMessages('resync by sku '.$item['sku']);
                $status = $this->syncPullSku($item['sku']);
            }
        }
        return $status;
    }

    /**
     * Get ocean products synced before date
     * @param string $date the date after synced
     * @return object
     */
    public function getOceanProductsSyncedBefore($date = '', $limit = 10){
        if (!$date) $date = gmdate('Y-m-d H:i:s');
        $collection = $this->simioceanProductFactory->create()->getCollection();
        $collection->addFieldToFilter('sync_time', array('lt' => $date))
            ->getSelect()
            ->where('product_id IS NOT NULL')
            ->where('sku IS NOT NULL')
            ->group('sku')
            ->order('sync_time DESC')
            ->limit($limit);
        if ($collection->getSize()) {
            return $collection;
        }
        return false;
    }

    /**
     * @return self
     */
    public function resetSyncMessages(){
        $this->messages = [];
        return $this;
    }

    /**
     * @return self
     */
    public function addSyncMessages($messages){
        if (!is_array($this->messages)) {
            $this->messages = array($messages);
        }
        $this->messages[] = $messages;
        return $this;
    }

    /**
     * @return array
     */
    public function getSyncMessages(){
        return $this->messages;
    }
}