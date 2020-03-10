<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model\Ocean;

class Api
{
    protected $helper;
    protected $_ch;

    public function __construct(
        \Simi\Simiocean\Helper\Data $helper
    ){
        $this->helper = $helper;
    }

    public function init(){
        if (!$this->_ch) {
            $this->_ch = curl_init();
            curl_setopt($this->_ch, CURLOPT_ENCODING, "");
            $curlRequestHeaders = array(
                'Content-Type: application/json',
                'Authorization: Basic '.$this->helper->getAuthInfo()
            );
            curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $curlRequestHeaders);
        }
        return $this;
    }

    public function close(){
        $this->closeCurl();
        return $this;
    }

    public function closeCurl(){
        if ($this->_ch) {
            curl_close($this->_ch);
            $this->_ch = null;
        }
        return $this;
    }

    /**
     * Make a curl call to external server
     * return false|mixed type array('headers', 'body', 'responseInfo')
     */
    public function callApi($endPoint, $method = 'GET', $body = '', $params = array()){
        if ($this->helper->getServerApi()) {
            $this->init();
            curl_setopt($this->_ch, CURLOPT_URL, trim($this->helper->getServerApi(), '/').'/'.trim($endPoint, '/'));
            switch ($method) {
                case "GET":
                    if (is_array($body)) {
                        $postData = http_build_query($body);
                        curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $this->helper->encrypt($postData));
                    }
                    if (is_array($params) && !empty($params)) {
                        $postData = http_build_query($params);
                        curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $this->helper->encrypt($postData));
                    }
                    break;
                case "POST":
                    curl_setopt($this->_ch, CURLOPT_POST, true);
                    $postData = $body;
                    if (!$body && !empty($params)) {
                        $params = !is_array($params) ? array($params) : $params;
                        $postData = http_build_query($params);
                    }
                    if ($postData) {
                        curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $this->helper->encrypt($postData));
                    }
                    break;
                case "PUT":
                    curl_setopt($this->_ch, CURLOPT_PUT, true);
                    curl_setopt($this->_ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: PUT'));
                    if ($body) {
                        curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $this->helper->encrypt($body));
                    }
                    break;
                case "DELETE":
                    curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                    curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $this->helper->encrypt($body));
                    break;
            }
            //Other cURL options.
            curl_setopt($this->_ch, CURLOPT_HEADER, true);
            curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
            //Make the request.
            $response = curl_exec($this->_ch);
            $responseInfo = curl_getinfo($this->_ch);
            $headerSize = curl_getinfo($this->_ch, CURLINFO_HEADER_SIZE);
            $this->closeCurl();
            //Setting CURLOPT_HEADER to true above forces the response headers and body
            //to be output together--separate them.
            $responseHeaders = substr($response, 0, $headerSize);
            $responseBody = substr($response, $headerSize);
            return array("headers" => $responseHeaders, "body" => $responseBody, "responseInfo" => $responseInfo);
        }
        return false;
    }

    /**
     * @return bool|array
     */
    public function call($endPoint, $method = 'GET', $body = '', $params = array()){
        $response = $this->callApi($endPoint, $method, $body, $params);
        if (isset($response['responseInfo']) && isset($response['responseInfo']['http_code'])) {
            switch($response['responseInfo']['http_code']){
                case "400":
                case "405":
                    if (isset($response['body'])) {
                        throw new \Exception($response['body']);
                    } else {
                        throw new \Exception(json_encode($response));
                    }
                    return false;
                    break;
            }
        }
        if (isset($response['body'])) {
            return $this->helper->decrypt($response['body']);
        }
        return false;
    }
}