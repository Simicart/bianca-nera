<?php

namespace Simi\Simicustomize\Override\Helper;


class Products extends \Simi\Simiconnector\Helper\Products
{
    public function getLayerNavigator($collection = null, $params = null)
    {
        if (!$collection) {
            $collection = $this->builderQuery;
        }
        if (!$params) {
            $data       = $this->getData();
            $params = isset($data['params'])?$data['params']:array();
        }
        $attributeCollection = $this->simiObjectManager
            ->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection');
        $attributeCollection
            ->setItemObjectClass(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->addStoreLabel($this->storeManager->getStore()->getId())
            ->addIsFilterableFilter()
            ->setOrder('position', 'ASC')
            //->addVisibleFilter() //cody comment out jun152019
            //->addFieldToFilter('used_in_product_listing', 1) //cody comment out jun152019
            //->addFieldToFilter('is_visible_on_front', 1) //cody comment out jun152019
        ;
        if ($this->is_search)
            $attributeCollection->addFieldToFilter('is_filterable_in_search', 1);

        $allProductIds = $collection->getAllIds();
        $arrayIDs      = [];
        foreach ($allProductIds as $allProductId) {
            $arrayIDs[$allProductId] = '1';
        }
        $layerFilters = [];

        $titleFilters = [];
        $this->_filterByAtribute($collection, $attributeCollection, $titleFilters, $layerFilters, $arrayIDs);

        if ($this->simiObjectManager
            ->get('Magento\Framework\App\ProductMetadataInterface')
            ->getEdition() != 'Enterprise')
            $this->_filterByPriceRange($layerFilters, $collection, $params);

        // category
        if ($this->category) {
            $childrenCategories = $this->category->getChildrenCategories();
            $collection->addCountToCategories($childrenCategories);
            $filters            = [];
            foreach ($childrenCategories as $childCategory) {
                if ($childCategory->getProductCount()) {
                    $filters[] = [
                        'label' => $childCategory->getName(),
                        'value' => $childCategory->getId(),
                        'count' => $childCategory->getProductCount()
                    ];
                }
            }

            $layerFilters[] = [
                'attribute' => 'category_id',
                'title'     => __('Categories'),
                'filter'    => ($filters),
            ];
        }

        $paramArray = (array)$params;
        $selectedFilters = $this->_getSelectedFilters();
        
        $selectableFilters = $this->_getSelectableFilters($collection, $paramArray, $selectedFilters, $layerFilters);

        $layerArray = ['layer_filter' => $selectableFilters];
        if ($this->simiObjectManager->get('Simi\Simiconnector\Helper\Data')->countArray($selectedFilters) > 0) {
            $layerArray['layer_state'] = $selectedFilters;
        }

        return $layerArray;
    }
    
    //add vendor option to filter
    public function _filterByAtribute($collection, $attributeCollection, &$titleFilters, &$layerFilters, $arrayIDs)
    {
        if (!count($arrayIDs)) {
            $allowedAttributes = array('color', 'size', 'is_admin_sell', 'try_to_buy', 'reservable', 'pre_order', 'vendor_id', 'price');
            foreach ($attributeCollection as $attribute) {
                if (in_array($attribute->getAttributeCode(), $allowedAttributes)) {
                    $attributeValues  = $collection->getAllAttributeValues($attribute->getAttributeCode());
                    $this->addFilterByAttributeNoProduct($attribute, $attributeValues, $layerFilters, $titleFilters);
                }
            }
        } else {
            $childProductsIds      = [];
            if ($arrayIDs && count($arrayIDs)) {
                $childProducts = $this->simiObjectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection')
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('type_id', 'simple');
                $select = $childProducts->getSelect();
                $select->joinLeft(
                        array('link_table' => 'catalog_product_super_link'),
                        'link_table.product_id = e.entity_id',
                        array('product_id', 'parent_id')
                    );
                $select = $childProducts->getSelect();
                $select->where("link_table.parent_id IN (".implode(',', array_keys($arrayIDs)).")");
                foreach ($childProducts->getAllIds() as $allProductId) {
                    $childProductsIds[$allProductId] = '1';
                }
            }

            foreach ($attributeCollection as $attribute) {
                $attributeValues  = $collection->getAllAttributeValues($attribute->getAttributeCode());
                $options = $attribute->getSource()->getAllOptions();
                $this->addFilterByAttribute($attribute, $attributeValues, $layerFilters, $titleFilters, $arrayIDs, $options, $childProductsIds);
            }
        }

        $vendorAtt = $this->simiObjectManager
                ->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')
                ->addFieldToFilter('attribute_code', 'vendor_id')
                ->getFirstItem();
        $vendorHelper = $this->simiObjectManager->get('\Simi\Simicustomize\Helper\Vendor');
        if ($vendorAtt->getId()) {
            $vendors = $this->simiObjectManager->get('\Vnecoms\Vendors\Model\Vendor')
                ->getCollection();
            $options = array();
            $attributeValues  = $this->getAllAttributeValues($collection, $vendorAtt->getAttributeCode());
            foreach ($vendors as $vendor) {
                $profile = $vendorHelper->getProfile($vendor->getId());
                $options[] = array(
                    'label' => ($profile && isset($profile['store_name']))?$profile['store_name']:$vendor->getId(),
                    'value' => $vendor->getId(),
                );
            }
            $this
                ->addFilterByAttribute($vendorAtt, $attributeValues, $layerFilters, $titleFilters, $arrayIDs, $options);
        }
    }


    protected function getAllAttributeValues($collection, $attribute)
    {
        $select = clone $collection->getSelect();
        $data = $collection->getConnection()->fetchAll($select);
        $res = [];
        foreach ($data as $row) {
            $res[$row['entity_id']][0] = $row['vendor_id'];
        }
        return $res;
    }

    protected function addFilterByAttribute($attribute, $attributeValues, &$layerFilters, &$titleFilters, $arrayIDs, $options = null, $childProductsIds = []) {
        $attributeOptions = [];
        $label = $attribute->getStoreLabel() ? $attribute->getStoreLabel() : $attribute->getDefaultFrontendLabel();
        if (in_array($label, $titleFilters)) {
            return;
        }
        foreach ($attributeValues as $productId => $optionIds) {
            if ((isset($optionIds[0]) && isset($arrayIDs[$productId]) && $arrayIDs[$productId] != null) ||
                (isset($childProductsIds[$productId]) && ($childProductsIds[$productId] != null))
            ) {
                $optionIds = explode(',', $optionIds[0]);
                foreach ($optionIds as $optionId) {
                    if (isset($attributeOptions[$optionId])) {
                        $attributeOptions[$optionId] ++;
                    } else {
                        $attributeOptions[$optionId] = 1;
                    }
                }
            }
        }

        if (!$options)
            $options = $attribute->getSource()->getAllOptions();
        $filters = [];
        foreach ($options as $option) {
            if (isset($option['value']) && isset($attributeOptions[$option['value']])
                && $attributeOptions[$option['value']]) {
                $option['count'] = $attributeOptions[$option['value']];
                $filters[]       = $option;
            }
        }

        if ($this->simiObjectManager->get('Simi\Simiconnector\Helper\Data')->countArray($filters) >= 1) {
            $titleFilters[] = $label;
            $layerFilters[] = [
                'attribute' => $attribute->getAttributeCode(),
                'title'     => $label,
                'filter'    => $filters,
            ];
        }
    }

    protected function addFilterByAttributeNoProduct($attribute, $attributeValues, &$layerFilters, &$titleFilters, $options = null) {
        $label = $attribute->getStoreLabel() ? $attribute->getStoreLabel() : $attribute->getDefaultFrontendLabel();
        if (in_array($label, $titleFilters)) {
            return;
        }
        if (!$options)
            $options = $attribute->getSource()->getAllOptions();
        $filters = array();
        if (is_array($options)) {
            foreach ($options as $option) {
                if (isset($option['label']) && !trim($option['label'])) {
                    continue;
                }
                $option['count'] = 0;
                $filters[]       = $option;
            }
        }
        if ($attribute->getAttributeCode() == 'price') {
            $filters = [[
                'value' => 0 . '-' . 10000,
                'label' => $this->_renderRangeLabel(0, 10000),
                'count' => 1
            ]];
            $titleFilters[] = $label;
            $layerFilters[] = [
                'attribute' => $attribute->getAttributeCode(),
                'title'     => $label,
                'filter'    => $filters,
            ];
        } else {
            if ($this->simiObjectManager->get('Simi\Simiconnector\Helper\Data')->countArray($filters) >= 1) {
                $titleFilters[] = $label;
                $layerFilters[] = [
                    'attribute' => $attribute->getAttributeCode(),
                    'title'     => $label,
                    'filter'    => $filters,
                ];
            }
        }
        
    }
}
