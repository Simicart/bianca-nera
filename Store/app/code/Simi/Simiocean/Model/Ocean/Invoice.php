<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model\Ocean;

class Invoice
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
     * Get sales invoice from Ocean
     * @param int $invoiceNo
     * @return array|false
     */
    public function getInvoice($invoiceNo){
        if ($invoiceNo) {
            return $this->api->call("api/SalesInvoice/$invoiceNo");
        }
        return false;
    }

    /**
     * Add sales invoice
     * @param array $data
     * @return bool
     */
    public function addInvoice($data){
        if ($data) {
            return $this->api->call("api/SalesInvoice", 'POST', $data);
        }
        return false;
    }
    
    /**
     * Update sales invoice
     * @param array $data
     * @return bool
     */
    public function updateInvoice($data){
        if ($data) {
            return $this->api->call("api/SalesInvoice", 'PUT', $data);
        }
        return false;
    }

    /**
     * Delete sales invoice
     * @param int $invoiceNo
     * @return bool
     */
    public function deleteInvoice($invoiceNo){
        if ($invoiceNo) {
            return $this->api->call("api/SalesInvoice/$invoiceNo", 'DELETE');
        }
        return false;
    }

    /**
     * Get payments from Ocean
     * @return array|false
     */
    public function getPayment(){
        return $this->api->call("api/PaymentType");
    }
}