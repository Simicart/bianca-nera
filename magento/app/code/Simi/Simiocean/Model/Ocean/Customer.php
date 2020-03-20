<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model\Ocean;

class Customer
{
    /**
     * object \Simi\Simiocean\Model\Ocean\Api
     */
    protected $api;

    public function __construct(
        Api $api
    ){
        $this->api = $api;
    }

    /**
     * Get customers from Ocean
     * @param int $page number
     * @param int $size number limit each page
     * @return array|false
     */
    public function getCustomers($page = 1, $size = 10){
        if ($page && $size) {
            return $this->api->call("api/Customer?PageNumber=$page&PageSize=$size");
        }
        return false;
    }

    /**
     * Get customers from Ocean filter by FromDate to ToDate
     * @param int $from date timestamp
     * @param int $to date timestamp
     * @param int $page number
     * @param int $size limit each page
     * @return array|false
     */
    public function getFilterCustomers($from = '', $to = '', $page = 1, $size = 10){
        if ($page && $size) {
            $query = "api/Customer?PageNumber=$page&PageSize=$size";
            if ($from) {
                $query .= "&FromDate=$from";
            }
            if ($to) {
                $query .= "&ToDate=$to";
            }
            return $this->api->call($query);
        }
        return false;
    }

    /**
     * Get customers by phone number
     * @param string $phone number
     * @return array|false
     */
    public function getCustomer($phone){
        if ($phone) {
            return $this->api->call("api/Customer/$phone");
        }
        return false;
    }

    /**
     * Get customers by discount card number
     * @param string $cardNo card number
     * @return array
     */
    public function getCustomerCardDiscount($cardNo){
        if ($cardNo) {
            return $this->api->call("api/Customer/CardNo/$cardNo");
        }
        return [];
    }

    /**
     * Add customer
     * @param array $data
     * @return bool
     */
    public function addCustomer($data){
        if ($data) {
            return $this->api->call("api/Customer", 'POST', $data);
        }
        return false;
    }

    /**
     * Delete customer by customer id
     * @param string $id
     * @return bool
     */
    public function deleteCustomer($id){
        if ($id) {
            return $this->api->call("api/Customer/$id", 'DELETE');
        }
        return false;
    }

    /**
     * Edit/update customer
     * @param $data array
     * @return bool
     */
    public function updateCustomer($data){
        if ($data) {
            return $this->api->call("api/Customer", 'PUT', $data);
        }
        return false;
    }
}