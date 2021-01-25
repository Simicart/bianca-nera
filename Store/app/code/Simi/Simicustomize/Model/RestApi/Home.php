<?php

/**
 * Copyright © 2016 Simi. All rights reserved.
 */

namespace Simi\Simicustomize\Model\RestApi;

class Home implements \Simi\Simicustomize\Api\HomeInterface
{

    public $DEFAULT_ORDER = 'sort_order';

    protected $objectManager;
    protected $storeManager;
    protected $bannerFactory;
    protected $homesectionFactory;
    // protected $productFactory;

    /**
     * @var Magento\Framework\App\Filesystem\DirectoryList $directoryList ;
     */
    public $directoryList;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Simi\Simiconnector\Model\BannerFactory $bannerFactory,
        \Simi\Simicustomize\Model\HomesectionFactory $homesectionFactory
        // \Magento\Catalog\Model\ProductFactory $productFactory
    )
    {
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->bannerFactory = $bannerFactory;
        $this->homesectionFactory = $homesectionFactory;
        // $this->productFactory = $productFactory;
    }

    /**
     * @inheritdoc
     */
    public function get(){
        $result = array();
        // Home banners
        // get type by in Simi\Simiconnector\Helper\Data->getVisibilityTypeId()
        // $typeID = 2; //banner
        $bannerCollection = $this->bannerFactory->create()->getCollection();
        $bannerCollection->addFieldToFilter('status', '1')
            ->applyAPICollectionFilter(
                $bannerCollection->getTable('simiconnector_visibility'), 2,  // 2 = banner
                $this->storeManager->getStore()->getId()
            )
            ->setOrder('sort_order', 'ASC');
        foreach ($bannerCollection as $index => $banner) {
            $item = $banner->getData();
            if (!$banner->getData('banner_name_tablet')) {
                $item['banner_name_tablet'] = $banner->getData('banner_name');
            }
            try {
                if ($banner->getData('banner_name')) {
                    // $imagesize           = getimagesize(BP . '/pub/media/' . $banner->getData('banner_name'));
                    // $item['width']       = $imagesize[0];
                    // $item['height']      = $imagesize[1];
                    $item['banner_name'] = $this->getMediaUrl($banner->getData('banner_name'));
                }
                if ($banner->getData('banner_name_tablet')) {
                    // $imagesize                  = getimagesize(BP . '/pub/media/' . $banner->getData('banner_name_tablet'));
                    // $item['width_tablet']       = $imagesize[0];
                    // $item['height_tablet']      = $imagesize[1];
                    $item['banner_name_tablet'] = $this->getMediaUrl($banner->getData('banner_name_tablet'));
                }
            } catch (\Exception $e) {
                $item['function_warning'] = true;
            }

            if ($banner->getData('type') == 2) { //category
                $categoryModel        = $this->loadCategoryWithId($banner->getData('category_id'));
                $item['has_children'] = $categoryModel->hasChildren();
                $item['cat_name']     = $categoryModel->getName();
                $item['url_path']     = $categoryModel->getUrlPath();
            } else if ($banner->getData('type') == 1) { //product
                if ($banner->getData('banner_url')) {
                    $item['url_key']  = $banner->getData('banner_url');
                } else if ($banner->getData('product_id')) {
                    $productModel = $this->objectManager->create('\Magento\Catalog\Model\Product')->load($banner->getData('product_id'));
                    if ($productModel->getId()){
                        $item['url_key'] = $productModel->getData('url_key');
                        $banner->setData('banner_url', $productModel->getData('url_key'));
                        $banner->save();
                    }
                }
            }
            $result['home']['homebanners']['all_ids'][] = $item['banner_id'];
            $result['home']['homebanners']['homebanners'][] = $item;
        }

        // Home sections
        $homesectionCollection = $this->homesectionFactory->create()->getCollection();
        $homesectionCollection->addFieldToFilter('status', '1')
            ->applyAPICollectionFilter(
                $homesectionCollection->getTable('simiconnector_visibility'), 0, 
                $this->storeManager->getStore()->getId()
            )
            ->setOrder('sort_order', 'ASC');

        foreach ($homesectionCollection as $index => $sectionModel) {
            $item = $sectionModel->getData();
            if (!$item['image_left_1_mobile']) {
                $item['image_left_1_mobile'] = $item['image_left_1'];
            }
            if (!$item['image_left_2_mobile']) {
                $item['image_left_2_mobile'] = $item['image_left_2'];
            }
            try {
                if ($item['image_left_1']) {
                    // $imagesize           = getimagesize(BP . '/pub/media/' . $item['image_left_1']);
                    // $item['image_left_1_width']       = $imagesize[0];
                    // $item['image_left_1_height']      = $imagesize[1];
                    $item['image_left_1'] = $this->getMediaUrl($item['image_left_1']);
                }
                if ($item['image_left_1_mobile']) {
                    // $imagesize                  = getimagesize(BP . '/pub/media/' . $item['image_left_1_mobile']);
                    // $item['image_left_1_mobile_width']       = $imagesize[0];
                    // $item['image_left_1_mobile_height']      = $imagesize[1];
                    $item['image_left_1_mobile'] = $this->getMediaUrl($item['image_left_1_mobile']);
                }
                if ($item['image_left_2']) {
                    // $imagesize           = getimagesize(BP . '/pub/media/' . $item['image_left_2']);
                    // $item['image_left_2_width']       = $imagesize[0];
                    // $item['image_left_2_height']      = $imagesize[1];
                    $item['image_left_2'] = $this->getMediaUrl($item['image_left_2']);
                }
                if ($item['image_left_2_mobile']) {
                    // $imagesize                  = getimagesize(BP . '/pub/media/' . $item['image_left_2_mobile']);
                    // $item['image_left_2_mobile_width']       = $imagesize[0];
                    // $item['image_left_2_mobile_height']      = $imagesize[1];
                    $item['image_left_2_mobile'] = $this->getMediaUrl($item['image_left_2_mobile']);
                }
            } catch (\Exception $e) {
                $item['function_warning'] = true;
            }

            $isUpdateModel = false;
            if ($item['type'] == 2) { //category
                $categoryModel        = $this->loadCategoryWithId($item['type_value_1']);
                $item['image_left_1_url']       = $categoryModel->getUrlPath();
                $item['image_left_1_title']     = $categoryModel->getName();
                $item['type_value_1']           = $categoryModel->getUrlPath();
                $item['type_value_1_title']     = $categoryModel->getName();

                $categoryModel        = $this->loadCategoryWithId($item['type_value_2']);
                $item['image_left_2_url']       = $categoryModel->getUrlPath();
                $item['image_left_2_title']     = $categoryModel->getName();
                $item['type_value_2']           = $categoryModel->getUrlPath();
                $item['type_value_2_title']     = $categoryModel->getName();
            } else if ($item['type'] == 1) { //product
                if (is_numeric($item['type_value_1'])) {
                    $productModel = $this->objectManager
                        ->create('\Magento\Catalog\Model\Product')->load($item['type_value_1']);
                    $item['type_value_1'] = $productModel->getData('url_key');
                    $sectionModel->setData('type_value_1', $productModel->getData('url_key'));
                    $isUpdateModel = true;
                } else {
                    $item['type_value_1'] = '';
                }

                if (is_numeric($item['type_value_2'])) {
                    $productModel         = $this->objectManager
                        ->create('\Magento\Catalog\Model\Product')->load($item['type_value_2']);
                    $item['type_value_2'] = $productModel->getData('url_key');
                    $sectionModel->setData('type_value_2', $productModel->getData('url_key'));
                    $isUpdateModel = true;
                } else {
                    $item['type_value_2'] = '';
                }
            }

            // Convert to SKU
            if ($item['product_sku_1']) {
                $item['product_id_1'] = $item['product_sku_1'];
            } else if($item['product_id_1']) {
                $productModel = $this->objectManager
                    ->create('\Magento\Catalog\Model\Product')->load($item['product_id_1']);
                if ($productModel->getId()) {
                    $item['product_id_1'] = $productModel->getSku();
                    $sectionModel->setData('product_sku_1', $productModel->getSku());
                    $isUpdateModel = true;
                }
            }
            
            if ($item['product_sku_2']) {
                $item['product_id_2'] = $item['product_sku_2'];
            } else if($item['product_id_2']) {
                $productModel = $this->objectManager
                    ->create('\Magento\Catalog\Model\Product')->load($item['product_id_2']);
                if ($productModel->getId()) {
                    $item['product_id_2'] = $productModel->getSku();
                    $sectionModel->setData('product_sku_2', $productModel->getSku());
                    $isUpdateModel = true;
                }
            }
            
            if ($item['product_sku_3']) {
                $item['product_id_3'] = $item['product_sku_3'];
            } else if($item['product_id_3']) {
                $productModel = $this->objectManager
                    ->create('\Magento\Catalog\Model\Product')->load($item['product_id_3']);
                if ($productModel->getId()) {
                    $item['product_id_3'] = $productModel->getSku();
                    $sectionModel->setData('product_sku_3', $productModel->getSku());
                    $isUpdateModel = true;
                }
            }

            // if has data change
            if ($isUpdateModel) {
                $sectionModel->save();
            }

            $result['home']['homesections']['all_ids'][] = $item['id']; //the section id
            $result['home']['homesections']['homesections'][] = $item;
        }

        return array($result);
    }

    public function loadCategoryWithId($id)
    {
        $categoryModel    = $this->objectManager
            ->create('\Magento\Catalog\Model\Category')->load($id);
        return $categoryModel;
    }

    /**
     * @return string
     */
    public function getMediaUrl($media_path)
    {
        return $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . $media_path;
    }
}
