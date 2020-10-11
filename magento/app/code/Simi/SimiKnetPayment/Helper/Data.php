<?php

namespace Simi\SimiKnetPayment\Helper;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 * @package Simi\SimiKnetPayment\Helper
 */
class Data extends AbstractHelper
{
    const XML_PATH_LANG = 'payment/simiknet/lang';
    const XML_PATH_TRANSPORTAL_ID = 'payment/simiknet/transportal_id';
    const XML_PATH_TRANSPORTAL_PASS = 'payment/simiknet/transportal_password';
    const XML_PATH_RESOURSE_KEY = 'payment/simiknet/resource_key';

    const XML_PATH_MODE = 'payment/simiknet/mode';
    const XML_PATH_INVOICE = 'payment/simiknet/allow_invoice';

    const XML_PATH_LIVE_URL = 'https://www.kpay.com.kw/kpg/PaymentHTTP.htm?param=paymentInit';
    const XML_PATH_STAGING_URL = 'https://www.kpaytest.com.kw/kpg/PaymentHTTP.htm?param=paymentInit';

    const XML_PATH_LIVE_INQUIRY_URL = 'https://www.kpay.com.kw/kpg/tranPipe.htm?param=tranInit';
    const XML_PATH_STAGING_INQUIRY_URL = 'https://www.kpaytest.com.kw/kpg/tranPipe.htm?param=tranInit';

    const XML_PATH_PWA_STUDIO_URL = 'simiconnector/general/pwa_studio_url';

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;


    /**
     * Data constructor.
     * @param Context $context
     * @param EncryptorInterface $encryptor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(Context $context, EncryptorInterface $encryptor, StoreManagerInterface $storeManager)
    {
        $this->encryptor = $encryptor;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function isAutoInvoice()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_INVOICE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getPaymentLanguage()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_LANG, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getTransportalId()
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::XML_PATH_TRANSPORTAL_ID, ScopeInterface::SCOPE_STORE));
    }

    /**
     * @return string
     */
    public function getResourceKey()
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::XML_PATH_RESOURSE_KEY, ScopeInterface::SCOPE_STORE));
    }

    /**
     * @return string
     */
    public function getTransportalPassword()
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::XML_PATH_TRANSPORTAL_PASS, ScopeInterface::SCOPE_STORE));
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getResponseUrl($storeId)
    {
        $baseUrl = $this->storeManager->getStore($storeId)->getBaseUrl();
        return $baseUrl . "simiknet/payment/success";
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getErrorUrl($storeId)
    {
        $baseUrl = $this->storeManager->getStore($storeId)->getBaseUrl();
        return $baseUrl . "simiknet/payment/fail";
    }

    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MODE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getGatewayUrl()
    {
        if ($this->getMode()) {
            return self::XML_PATH_STAGING_URL;
        } else {
            return self::XML_PATH_LIVE_URL;
        }
    }

    /**
     * @return string
     */
    public function getInquiryGatewayUrl()
    {
        if ($this->getMode()) {
            return self::XML_PATH_STAGING_INQUIRY_URL;
        } else {
            return self::XML_PATH_LIVE_INQUIRY_URL;
        }
    }

    //AES Encryption Method Starts

    /**
     * @param $str
     * @param $key
     * @return array|false|string
     */
    public function encryptAES($str, $key)
    {
        $str = $this->pkcs5_pad($str);
        $encrypted = openssl_encrypt($str, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $key);
        $encrypted = base64_decode($encrypted);
        $encrypted = unpack('C*', ($encrypted));
        $encrypted = $this->byteArray2Hex($encrypted);
        $encrypted = urlencode($encrypted);
        return $encrypted;
    }

    /**
     * @param $text
     * @return string
     */
    public function pkcs5_pad($text)
    {
        $blocksize = 16;
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    /**
     * @param $byteArray
     * @return string
     */
    public function byteArray2Hex($byteArray)
    {
        $chars = array_map("chr", $byteArray);
        $bin = join($chars);
        return bin2hex($bin);
    }
    //AES Encryption Method Ends

    //Decryption Method for AES Algorithm Starts

    /**
     * @param $code
     * @param $key
     * @return bool|false|string
     */
    public function decrypt($code, $key)
    {
        $code = $this->hex2ByteArray(trim($code));
        $code = $this->byteArray2String($code);
        $iv = $key;
        $code = base64_encode($code);
        $decrypted = openssl_decrypt($code, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $iv);
        return $this->pkcs5_unpad($decrypted);
    }

    /**
     * @param $hexString
     * @return array|false
     */
    public function hex2ByteArray($hexString)
    {
        $string = hex2bin($hexString);
        return unpack('C*', $string);
    }

    /**
     * @param $byteArray
     * @return string
     */
    public function byteArray2String($byteArray)
    {
        $chars = array_map("chr", $byteArray);
        return join($chars);
    }

    /**
     * @param $text
     * @return bool|false|string
     */
    public function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }

    //Decryption Method for AES Algorithm Ends
}
