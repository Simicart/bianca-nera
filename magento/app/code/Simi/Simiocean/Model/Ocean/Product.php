<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model\Ocean;

class Product
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
     * return bool|array
     */
    public function getProducts($year, $page, $size){
        if ($year && $page && $size) {
            return $this->api->call("api/Product/ModelYear/$year?PageNumber=$page&PageSize=$size");
        }
        return [];
    }

    /**
     * Get product list by page number and page size
     * return bool|array
     */
    public function getProductList($page, $size){
        if ($page && $size) {
            return $this->api->call("api/Product?PageNumber=$page&PageSize=$size");
        }
        return false;
    }
    
    /**
     * Get product list by page number and page size
     * @param int $from Timestamp
     * @param int $to Timestamp
     * @param int $page
     * @param int $limit
     * return bool|array
     */
    public function getProductFilter($from, $to = '', $page = 1, $size = 10){
        if ($from) {
            if ($to) {
                return $this->api->call("api/Product?FromDate=$from&ToDate=$to&PageNumber=$page&PageSize=$size");
            }
            return $this->api->call("api/Product?FromDate=$from&PageNumber=$page&PageSize=$size");
        }
        return false;
    }

    /**
     * Get product by SKU from Ocean
     * return bool|array
     */
    public function getProductSku($sku){
        if ($sku) {
            return $this->api->call("api/Product/$sku");
        }
        return false;
    }
}