<?php
/**
 * Copyright (c) 2016-2019 Mastercard
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OnTap\MasterCard\Model;

use OnTap\MasterCard\Api\PaymentManagementGatewayInterface;
use OnTap\MasterCard\Gateway\Config\Hpf\Config as HpfConfig;
use Simi\Simicustomize\Model\Proxy;
use Magento\Store\Model\StoreManagerInterface;

class PaymentManagementGateway implements PaymentManagementGatewayInterface
{
    protected $proxy;
    protected $config;
    protected $storeManager;

    public function __construct(
        Proxy $proxy,
        HpfConfig $config,
        StoreManagerInterface $storeManager
    ) {
        $this->proxy = $proxy;
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function createNewPaymentSession(){
        $storeId = $this->storeManager->getStore()->getId();
        // $merchantId = $this->config->getMerchantId($storeId);
        $this->config->setMethodCode('tns_hpf');
        $componentUrl = $this->config->getComponentUrl($storeId);
        $componentUrl = str_replace('.js', '', $componentUrl);
        $result = $this->proxy->query($componentUrl);
        return ['data' => json_decode($result, true)];
    }

    /**
     * @inheritDoc
     */
    public function addCard($session){
        $storeId = $this->storeManager->getStore()->getId();
        // $merchantId = $this->config->getMerchantId($storeId);
        $this->config->setMethodCode('tns_hpf');
        $componentUrl = $this->config->getComponentUrl($storeId);
        $componentUrl = str_replace('.js', '', $componentUrl);
        $result = $this->proxy($componentUrl.'/'.$session);
        return ['data' => json_decode($result, true)];
    }

    /**
     * @inheritDoc
     */
    public function submitCard($session){
        $storeId = $this->storeManager->getStore()->getId();
        // $merchantId = $this->config->getMerchantId($storeId);
        $this->config->setMethodCode('tns_hpf');
        $componentUrl = $this->config->getComponentUrl($storeId);
        $componentUrl = str_replace('.js', '', $componentUrl);
        $result = $this->proxy($componentUrl.'/'.$session);
        return ['data' => json_decode($result, true)];
    }

    /**
     * @inheritDoc
     */
    public function proxy($url){
        $ch = curl_init();
        //Set the request URL.
        curl_setopt($ch, CURLOPT_URL, $url);

        //Tell cURL to make the request using the brower's user-agent if there is one, or a fallback user-agent otherwise.
        $user_agent = $_SERVER["HTTP_USER_AGENT"];
        if (empty($user_agent)) {
            $user_agent = "Mozilla/5.0 (compatible; miniProxy)";
        }
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);

        //Get ready to proxy the browser's request headers...
        $browserRequestHeaders = apache_request_headers();

        //Transform the associative array from getallheaders() into an
        //indexed array of header strings to be passed to cURL.
        $curlRequestHeaders = array();
        foreach ($browserRequestHeaders as $name => $value) {
            $curlRequestHeaders[] = $name . ": " . $value;
        }
        // $curlRequestHeaders[] = "X-Forwarded-For: " . $_SERVER["REMOTE_ADDR"];
        
        //Any `origin` header sent by the browser will refer to the proxy itself.
        //If an `origin` header is present in the request, rewrite it to point to the correct origin.
        $urlParts = parse_url($url);
        $port = $urlParts["port"] ?? '';
        $curlRequestHeaders[] = "Origin: " . $urlParts["scheme"] . "://" . $urlParts["host"] . (empty($port) ? "" : ":" . $port);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlRequestHeaders);

        //Proxy any received GET/POST/PUT data.
        switch ($_SERVER["REQUEST_METHOD"]) {
            case "POST":
                curl_setopt($ch, CURLOPT_POST, true);
                //For some reason, $HTTP_RAW_POST_DATA isn't working as documented at
                //http://php.net/manual/en/reserved.variables.httprawpostdata.php
                //but the php://input method works. This is likely to be flaky
                
                $payload = file_get_contents('php://input');
                if (!empty($_POST)) {
                    $payload = http_build_query($_POST);
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                break;
            case "PUT":
                curl_setopt($ch, CURLOPT_PUT, true);
                curl_setopt($ch, CURLOPT_INFILE, fopen("php://input", "r"));
            break;
        }

        //Other cURL options.
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        //Make the request.
        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        //Setting CURLOPT_HEADER to true above forces the response headers and body
        //to be output together--separate them.
        $responseInfo = curl_getinfo($ch);
        $responseHeaders = substr($response, 0, $headerSize);
        $responseBody = substr($response, $headerSize);
        curl_close($ch);

        return $responseBody;
    }
}
