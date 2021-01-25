<?php

/**
 * Copyright © 2016 Simi. All rights reserved.
 */

namespace Simi\Simicustomize\Model\Api;

class Homesections extends \Simi\Simiconnector\Model\Api\Apiabstract
{

    const DEFAULT_DIR = 'asc';
    public $DEFAULT_ORDER = 'sort_order';

    public function setBuilderQuery()
    {
        $this->builderQuery = $this->getCollection();
    }

    public function getCollection()
    {
        $typeID           = $this->simiObjectManager
                ->get('Simi\Simiconnector\Helper\Data')->getVisibilityTypeId('homesection');
        $visibilityTable  = $this->resource->getTableName('simiconnector_visibility');
        $collection = $this->simiObjectManager
                ->get('Simi\Simicustomize\Model\Homesection')->getCollection()->addFieldToFilter('status', '1')
                ->applyAPICollectionFilter($visibilityTable, $typeID, 
                    $this->storeManager->getStore()->getId()
                );
        $this->builderQuery = $collection;
        return $collection;
    }

    public function index()
    {
        $result = parent::index();
        foreach ($result['homesections'] as $index => $item) {
            if (!$item['image_left_1_mobile']) {
                $item['image_left_1_mobile'] = $item['image_left_1'];
            }
            if (!$item['image_left_2_mobile']) {
                $item['image_left_2_mobile'] = $item['image_left_2'];
            }
            try {
                if ($item['image_left_1']) {
                    $imagesize           = getimagesize(BP . '/pub/media/' . $item['image_left_1']);
                    $item['image_left_1_width']       = $imagesize[0];
                    $item['image_left_1_height']      = $imagesize[1];
                    $item['image_left_1'] = $this->getMediaUrl($item['image_left_1']);
                }
                if ($item['image_left_1_mobile']) {
                    $imagesize                  = getimagesize(BP . '/pub/media/' . $item['image_left_1_mobile']);
                    $item['image_left_1_mobile_width']       = $imagesize[0];
                    $item['image_left_1_mobile_height']      = $imagesize[1];
                    $item['image_left_1_mobile'] = $this->getMediaUrl($item['image_left_1_mobile']);
                }
                if ($item['image_left_2']) {
                    $imagesize           = getimagesize(BP . '/pub/media/' . $item['image_left_2']);
                    $item['image_left_2_width']       = $imagesize[0];
                    $item['image_left_2_height']      = $imagesize[1];
                    $item['image_left_2'] = $this->getMediaUrl($item['image_left_2']);
                }
                if ($item['image_left_2_mobile']) {
                    $imagesize                  = getimagesize(BP . '/pub/media/' . $item['image_left_2_mobile']);
                    $item['image_left_2_mobile_width']       = $imagesize[0];
                    $item['image_left_2_mobile_height']      = $imagesize[1];
                    $item['image_left_2_mobile'] = $this->getMediaUrl($item['image_left_2_mobile']);
                }
            } catch (\Exception $e) {
                $item['function_warning'] = true;
            }

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
                $productModel         = $this->simiObjectManager
                    ->create('\Magento\Catalog\Model\Product')->load($item['type_value_1']);
                if ($productModel->getId()) {
                    $item['type_value_1'] = $productModel->getData('url_key');
                } else {
                    $item['type_value_1'] = '';
                }
                $productModel         = $this->simiObjectManager
                    ->create('\Magento\Catalog\Model\Product')->load($item['type_value_2']);
                if ($productModel->getId()) {
                    $item['type_value_2'] = $productModel->getData('url_key');
                } else {
                    $item['type_value_2'] = '';
                }
            }

            // $fields = array(
            //     'entity_id','entity_type_id','attribute_set_id','type_id','sku','name','created_at',
            //     'updated_at','has_options','required_options','cat_index_position','price','special_price','tax_class_id','final_price',
            //     'description','short_description','is_salable','url_key','url_path','small_image'
            // );
            // $item['products'] = array();
            // $productModel = $this->simiObjectManager
            //     ->create('\Magento\Catalog\Model\Product')->load($item['product_id_1']);
            // if ($productModel->getId()) {
            //     $item['products'][] = $productModel->toArray($fields);
            // }
            // $productModel = $this->simiObjectManager
            //     ->create('\Magento\Catalog\Model\Product')->load($item['product_id_2']);
            // if ($productModel->getId()) {
            //     $item['products'][] = $productModel->toArray($fields);
            // }
            // $productModel = $this->simiObjectManager
            //     ->create('\Magento\Catalog\Model\Product')->load($item['product_id_3']);
            // if ($productModel->getId()) {
            //     $item['products'][] = $productModel->toArray($fields);
            // }

            // Convert to SKU
            $productModel = $this->simiObjectManager
                ->create('\Magento\Catalog\Model\Product')->load($item['product_id_1']);
            if ($productModel->getId()) {
                $item['product_id_1'] = $productModel->getSku();
            } else {
                $item['product_id_1'] = '';
            }
            $productModel = $this->simiObjectManager
                ->create('\Magento\Catalog\Model\Product')->load($item['product_id_2']);
            if ($productModel->getId()) {
                $item['product_id_2'] = $productModel->getSku();
            } else {
                $item['product_id_2'] = '';
            }
            $productModel = $this->simiObjectManager
                ->create('\Magento\Catalog\Model\Product')->load($item['product_id_3']);
            if ($productModel->getId()) {
                $item['product_id_3'] = $productModel->getSku();
            } else {
                $item['product_id_3'] = '';
            }

            $result['homesections'][$index] = $item;
        }
        return $result;
    }
    
    public function loadCategoryWithId($id)
    {
        $categoryModel    = $this->simiObjectManager
                ->create('\Magento\Catalog\Model\Category')->load($id);
        return $categoryModel;
    }

    public function getDefaultDir() {
        return self::DEFAULT_DIR;
    }
}
