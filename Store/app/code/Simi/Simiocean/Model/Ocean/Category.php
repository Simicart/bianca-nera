<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model\Ocean;

class Category
{
    protected $helper;

    /**
     * object \Simi\Simiocean\Model\Ocean\Api
     */
    protected $api;

    public function __construct(
        \Simi\Simiocean\Helper\Data $helper,
        Api $api
    ){
        $this->helper = $helper;
        $this->api = $api;
    }

    /**
     * Get products from Ocean
     * @param int $subcateId
     * return bool|array
     */
    public function getCategory($subcateId = ''){
        if ($subcateId) {
            return $this->api->call("api/Category/$subcateId");
        }
        return $this->api->call("api/Category");
    }

    /**
     * Get products from Ocean
     * @param int $parentCateId
     * return bool|array
     */
    public function getSubCategory($parentCateId = ''){
        if ($parentCateId) {
            return $this->api->call("api/Subcategory/$parentCateId");
        }
        return $this->api->call("api/Subcategory");
    }
}