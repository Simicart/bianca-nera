<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model\Service;

class Category extends \Magento\Framework\Model\AbstractModel
{
    protected $config;

    /** @var Simi\Simiocean\Model\Ocean\Category */
    protected $categoryApi;

    protected $oceanCategoryFactory;

    protected $oceanCategoryCollectionFactory;
    protected $categoryRepository;
    protected $categoryResource;

    protected $storeManager;
    protected $objectManager;

    /** @var Simi\Simiocean\Model\Logger */
    protected $logger;

    /**
     * @param Magento\Framework\Model\Context $context
     * @param Magento\Framework\Registry $registry
     * @param Simi\Simiocean\Helper\Data $helper
     * @param Simi\Simiocean\Model\Ocean\Category $categoryApi
     * @param Simi\Simiocean\Model\Logger $logger
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Simi\Simiocean\Helper\Config $config,
        \Simi\Simiocean\Model\ResourceModel\Category\CollectionFactory $oceanCategoryCollectionFactory,
        \Simi\Simiocean\Model\Ocean\Category $categoryApi,
        \Simi\Simiocean\Model\CategoryFactory $oceanCategoryFactory,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Simi\Simiocean\Model\Logger $logger
    ){
        $this->config = $config;
        $this->logger = $logger;
        $this->categoryApi = $categoryApi;
        $this->oceanCategoryFactory = $oceanCategoryFactory;
        $this->oceanCategoryCollectionFactory = $oceanCategoryCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->categoryResource = $categoryResource;
        $this->storeManager = $storeManager;
        $this->objectManager = $objectManager;
        parent::__construct($context, $registry);
    }

    public function syncFromOcean(){
        try {
            $parentCates = $this->categoryApi->getCategory();
            if (is_array($parentCates)) {
                $scopeConfig = $this->objectManager->get('Magento\Framework\App\Config');
                $backupCategoryConfigFlat = $scopeConfig->getValue(
                    \Magento\Catalog\Model\Indexer\Category\Flat\State::INDEXER_ENABLED_XML_PATH, 
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                $configWriter = $this->objectManager->get('Magento\Framework\App\Config\Storage\Writer');
                $configWriter->save(\Magento\Catalog\Model\Indexer\Category\Flat\State::INDEXER_ENABLED_XML_PATH, 0);
                $scopeConfig->clean();
                $store = $this->storeManager->getStore();
                foreach ($parentCates as $oCate) {
                    if (isset($oCate['CategoryId'])) {
                        $subCates = $this->categoryApi->getSubCategory($oCate['CategoryId']);
                        $category = null;
                        $categoryId = null;
                        $oceanCategory = $this->getOceanCategory($oCate['CategoryId']);
                        if ($oceanCategory) {
                            $categoryId = $oceanCategory->getMagentoId();
                            if ($categoryId) {
                                try{
                                    $category = $this->categoryRepository->get($categoryId);
                                }catch(\Exception $e){}
                            }
                        }
                        if($oceanCategory && $categoryId && $category && $category->getId()){
                            $category->setName(isset($oCate['CategoryEnName']) ? $oCate['CategoryEnName'] : null);
                            // $category->setIsActive(true);
                            $category->setUpdatedAt(gmdate('Y-m-d H:i:s'));
                            $category->save();
                            if ($this->config->getArStore() != null){
                                $arStoreIds = explode(',', $this->config->getArStore());
                                foreach($arStoreIds as $storeId){
                                    $category->setStoreId($storeId);
                                    $category->setName(isset($oCate['CategoryArName']) ? $oCate['CategoryArName'] : null);
                                    $category->setUrlKey(urlencode(isset($oCate['CategoryEnName']) ? $oCate['CategoryEnName'] : ''));
                                    try{
                                        $category->save();
                                    }catch(\Exception $e){};
                                }
                            }
                            
                            $oceanCategory->setSyncTime(gmdate('Y-m-d H:i:s'));
                            $oceanCategory->setCategoryArName(isset($oCate['CategoryArName']) ? $oCate['CategoryArName'] : null);
                            $oceanCategory->setCategoryEnName(isset($oCate['CategoryEnName']) ? $oCate['CategoryEnName'] : null);
                            $oceanCategory->save();
                        } else {
                            $date = gmdate('Y-m-d H:i:s');
                            // $category = $this->objectManager->create(\Magento\Catalog\Model\Category::class);
                            $category = $this->objectManager->create(\Magento\Catalog\Api\Data\CategoryInterface::class);
                            $category->setName(isset($oCate['CategoryEnName']) ? $oCate['CategoryEnName'] : null);
                            $category->setIsActive(true);
                            $category->setCreatedAt($date);
                            $category->setUpdatedAt($date);
                            
                            // Add to root category
                            $rootId = $store->getRootCategoryId();
                            if (!$rootId) $rootId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
                            $rootCategory = $this->objectManager->get('Magento\Catalog\Model\Category')->load($rootId);
                            $category->setParentId($rootCategory->getId());
                            $category->setPath($rootCategory->getPath());
                            $category->setLevel(null);
                            $category->setPosition(1);
                            // $category->setAttributeSetId($rootCategory->getDefaultAttributeSetId());
                            $category->setStoreId($store->getId());
                            $category->setUrlKey(isset($oCate['CategoryEnName']) ? $oCate['CategoryEnName'] : '');
                            
                            if (is_array($subCates)) {
                                $category->setData('children_count', count($subCates));
                            } else {
                                $category->setData('children_count', 0);
                            }

                            try{
                                $this->categoryResource->save($category);
                                $categoryId = $category->getId();
                            }catch(\Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e){
                                $category->setUrlKey($category->getUrlKey().'-ocean');
                                $this->categoryResource->save($category);
                                $categoryId = $category->getId();
                            }catch(\Exception $e){}
                            
                            if ($this->config->getArStore() != null){
                                $arStoreIds = explode(',', $this->config->getArStore());
                                foreach($arStoreIds as $storeId){
                                    $category->setStoreId($storeId);
                                    $category->setName(isset($oCate['CategoryArName']) ? $oCate['CategoryArName'] : null);
                                    $category->setUrlKey(urlencode(isset($oCate['CategoryEnName']) ? $oCate['CategoryEnName'] : ''));
                                    try{
                                        $category->save();
                                    }catch(\Exception $e){};
                                }
                            }

                            if ($categoryId) {
                                if (!$oceanCategory) {
                                    $oceanCategory = $this->oceanCategoryFactory->create();
                                    $oceanCategory->setCreatedAt($date);
                                }
                                if ($oceanCategory) {
                                    $oceanCategory->setCategoryId($oCate['CategoryId'])
                                        ->setParentId(0) //Ocean root category id
                                        ->setMagentoId($categoryId)
                                        ->setSyncTime($date)
                                        ->setDirection(\Simi\Simiocean\Model\Category::DIR_OCEAN_TO_WEB)
                                        ->setStatus(\Simi\Simiocean\Model\SyncStatus::SUCCESS)
                                        ->setCategoryArName(isset($oCate['CategoryArName']) ? $oCate['CategoryArName'] : null)
                                        ->setCategoryEnName(isset($oCate['CategoryEnName']) ? $oCate['CategoryEnName'] : null)
                                        ->save();
                                }
                            }
                        }

                        // save sub categories
                        if (is_array($subCates) && count($subCates) && $categoryId) {
                            $date = gmdate('Y-m-d H:i:s');
                            foreach($subCates as $subCate){
                                if (isset($subCate['SubcategoryID'])) {
                                    $tailUrlKey = '-'.$subCate['SubcategoryID'].'-'.$oCate['CategoryId'];
                                    $oceanSubCategory = $this->getOceanCategory($subCate['SubcategoryID'], $oCate['CategoryId']);
                                    $subSategory = null;
                                    $subCategoryId = null;
                                    if ($oceanSubCategory) {
                                        $subCategoryId = $oceanSubCategory->getMagentoId();
                                        if ($subCategoryId) {
                                            try{
                                                $subSategory = $this->categoryRepository->get($subCategoryId);
                                            }catch(\Exception $e){}
                                        }
                                    }
                                    if ($oceanSubCategory && $subCategoryId && $subSategory && $subSategory->getId()) {
                                        // Update old subcategory
                                        $subSategory->setName(isset($subCate['SubcategoryEnName']) ? $subCate['SubcategoryEnName'] : null);
                                        // $subSategory->setIsActive(true);
                                        $subSategory->setUpdatedAt(gmdate('Y-m-d H:i:s'));
                                        $subSategory->save();
                                        if ($this->config->getArStore() != null){
                                            $arStoreIds = explode(',', $this->config->getArStore());
                                            foreach($arStoreIds as $storeId){
                                                $subSategory->setStoreId($storeId);
                                                $subSategory->setName(isset($subCate['SubcategoryArName']) ? $subCate['SubcategoryArName'] : null);
                                                $subSategory->setUrlKey(urlencode(isset($subCate['SubcategoryEnName']) ? $subCate['SubcategoryEnName'].$tailUrlKey : ''));
                                                try{
                                                    $subSategory->save();
                                                }catch(\Exception $e){};
                                            }
                                        }
                                        
                                        $oceanSubCategory->setSyncTime(gmdate('Y-m-d H:i:s'));
                                        $oceanSubCategory->setCategoryArName(isset($subCate['SubcategoryArName']) ? $subCate['SubcategoryArName'] : null);
                                        $oceanSubCategory->setCategoryEnName(isset($subCate['SubcategoryEnName']) ? $subCate['SubcategoryEnName'] : null);
                                        $oceanSubCategory->save();
                                    } else {
                                        // Create magento sub category
                                        $subSategory = $this->objectManager->create(\Magento\Catalog\Model\Category::class);
                                        $subSategory->setName(isset($subCate['SubcategoryEnName']) ? $subCate['SubcategoryEnName'] : null);
                                        $subSategory->setIsActive(true);
                                        $subSategory->setCreatedAt($date);
                                        $subSategory->setUpdatedAt($date);
                                        $subSategory->setParentId($categoryId); //set magento parent catetory id
                                        $subSategory->setPath($category->getPath());
                                        $subSategory->setLevel(null);
                                        $subSategory->setPosition(1);
                                        $subSategory->setData('children_count', 0);
                                        $subSategory->setUrlKey(isset($subCate['SubcategoryEnName']) ? $subCate['SubcategoryEnName'].$tailUrlKey : '');
                                        // $subSategory->setAttributeSetId($category->getDefaultAttributeSetId());
                                        $subSategory->setStoreId($store->getId());
                                        $subCategoryId = null;
                                        try{
                                            $this->categoryResource->save($subSategory);
                                            $subCategoryId = $subSategory->getId();
                                        }catch(\Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e){
                                            $subSategory->setUrlKey($subSategory->getUrlKey().'-ocean');
                                            $this->categoryResource->save($subSategory);
                                            $subCategoryId = $subSategory->getId();
                                        }catch(\Exception $e){}

                                        if ($this->config->getArStore() != null){
                                            $arStoreIds = explode(',', $this->config->getArStore());
                                            foreach($arStoreIds as $storeId){
                                                $subSategory->setStoreId($storeId);
                                                $subSategory->setName(isset($subCate['SubcategoryArName']) ? $subCate['SubcategoryArName'] : null);
                                                $subSategory->setUrlKey(urlencode(isset($subCate['SubcategoryEnName']) ? $subCate['SubcategoryEnName'].$tailUrlKey : ''));
                                                try{
                                                    $subSategory->save();
                                                }catch(\Exception $e){};
                                            }
                                        }
                                        
                                        // Create ocean sub category
                                        if ($subCategoryId) {
                                            if (!$oceanSubCategory) {
                                                $oceanSubCategory = $this->oceanCategoryFactory->create();
                                                $oceanSubCategory->setCreatedAt($date);
                                            }
                                            if ($oceanSubCategory) {
                                                $oceanSubCategory->setCategoryId($subCate['SubcategoryID'])
                                                    ->setParentId($oCate['CategoryId']) //Ocean sub category id
                                                    ->setMagentoId($subCategoryId)
                                                    ->setSyncTime($date)
                                                    ->setDirection(\Simi\Simiocean\Model\Category::DIR_OCEAN_TO_WEB)
                                                    ->setStatus(\Simi\Simiocean\Model\SyncStatus::SUCCESS)
                                                    ->setCategoryArName(isset($subCate['SubcategoryArName']) ? $subCate['SubcategoryArName'] : null)
                                                    ->setCategoryEnName(isset($subCate['SubcategoryEnName']) ? $subCate['SubcategoryEnName'] : null)
                                                    ->save();
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $configWriter->save(
                    \Magento\Catalog\Model\Indexer\Category\Flat\State::INDEXER_ENABLED_XML_PATH, 
                    $backupCategoryConfigFlat
                );
                $scopeConfig->clean();
            } else {
                throw new \Exception($parentCates);
            }
        } catch (\Exception $e) {
            $this->logger->debug(array(
                'Get categories error:',
                $e->getMessage()
            ));
            return false;
        }
        return true;
    }


    /**
     * Get ocean category synced by ocean category id and its parent id
     * @param int $cateId or is SubcategoryId in ocean product
     * @param int $parentId or CategoryId in ocean product
     * @return \Simi\Simiocean\Model\Category|null
     */
    public function getOceanCategory($cateId, $parentId = 0){
        $collection = $this->oceanCategoryCollectionFactory->create();
        $collection->addFieldToFilter('category_id', $cateId)
            ->addFieldToFilter('parent_id', $parentId)
            ->getSelect()
            ->limit(1);
        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }
        return null;
    }

    /**
     * Get magento category id by ocean category id and its parent id
     * @param int $subCateId or is SubcategoryId in ocean product
     * @param int $cateId or CategoryId in ocean product
     * @return int|null
     */
    public function getMagentoCategoryId($subCateId, $cateId = 0){
        $collection = $this->oceanCategoryCollectionFactory->create();
        $collection->addFieldToFilter('category_id', $subCateId)
            ->addFieldToFilter('parent_id', $cateId)
            ->getSelect()
            ->limit(1);
        if ($collection->getSize()) {
            return $collection->getFirstItem()->getMagentoId();
        }
        return null;
    }

    /**
     * Get ocean category synced by magento category id
     * @param int $magentoId
     * @return \Simi\Simiocean\Model\Category|null
     */
    public function getOceanCategoryByMagento($magentoId){
        $collection = $this->oceanCategoryCollectionFactory->create();
        $collection->addFieldToFilter('magento_id', $magentoId)
            ->getSelect()
            ->limit(1);
        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }
        return null;
    }
}