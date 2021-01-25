<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_PRIVATE_KEY = 'simiocean/auth/private_key';
    const CONFIG_PUBLIC_KEY = 'simiocean/auth/public_key';
    const CONFIG_SERVER_API = 'simiocean/auth/server_api';

    /*
     * Object Mangager
     */
    public $objectManager;

    /*
     * Magento\Framework\App\Config\ScopeConfig
     */
    public $scopeConfig;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    protected $_privateKey;
    protected $_publicKey;
    protected $_serverApi;
    protected $_authInfo;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->_encryptor = $encryptor;
    }

    /**
     * Get server api host (IP or Domain)
     */
    public function getServerApi(){
        if (!$this->_serverApi) {
            $this->_serverApi = $this->scopeConfig->getValue(self::CONFIG_SERVER_API);
        }
        return $this->_serverApi;
    }

    /**
     * The auth key to add at request header of API.
     * The client should add the {authInfo} to the http request header as following:
	 * Key="Authorization", Value="Basic " + authInfo
     */
    public function getAuthInfo(){
        if (!$this->_authInfo) {
            $privatekey = $this->getPrivateKey();
            $publicKey = $this->getPublicKey();
            $hash = base64_encode(hash_hmac( "sha256", $publicKey, $privatekey, true));
            $this->_authInfo = base64_encode($publicKey.':'.$hash);
        }
        return $this->_authInfo;
    }

    /**
     * Decrypt data encoded reponse from Ocean server
     */
    public function decrypt($data){
        if ($data) {
            try{
                $key = bin2hex(pack("H*", substr($this->getPrivateKey(), 0, 32)));
                $iv = bin2hex(pack("H*", substr($this->getPublicKey(), 0, 32)));
                $data = trim($data, '"');
                $data = base64_decode($data);
                $decrypted = @mcrypt_decrypt(
                    MCRYPT_RIJNDAEL_256,
                    $key, 
                    $data,
                    MCRYPT_MODE_CBC,
                    $iv
                );
                $pad = ord($decrypted[strlen($decrypted) - 1]);
                $decrypted = substr($decrypted, 0, -$pad);

                // replace float 0.0 to string "0.0", ext: "Points":0.0 -> "Points":"0.0"
                // $decrypted = preg_replace_callback(
                //     '/:\s*([0-9]*\.[0-9]*)\s*,/',
                //     function ($matches) {
                //         return $matches[1] ? ':"'.$matches[1].'",':'';
                //     },
                //     $decrypted
                // );
                
                return json_decode($decrypted, TRUE);
            }catch(\Exception $e){
                // var_dump('Decrypt error: '.$e->getMessage());die;
                throw new \Exception('Decrypt error: '.$e->getMessage());
                return false;
            }
        }
        return false;
    }

    /**
     * Encrypt data to send to Ocean server
     */
    public function encrypt($data){
        if ($data) {
            try{
                $key = bin2hex(pack("H*", substr($this->getPrivateKey(), 0, 32)));
                $iv = bin2hex(pack("H*", substr($this->getPublicKey(), 0, 32)));
                if (is_array($data)) $data = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
                
                $block = @mcrypt_get_block_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
                $pad = $block ? $block - (strlen($data) % $block) : 0;
                if ($pad === 0) {
                    throw new \Exception("Zero padding found instead of PKCS#7 padding");
                } 
                $data .= str_repeat(chr($pad), $pad);

                $encrypted = @mcrypt_encrypt(
                    MCRYPT_RIJNDAEL_256,
                    $this->getPrivateKey(),
                    $data,
                    MCRYPT_MODE_CBC,
                    $this->getPublicKey()
                );

                return chr(34).base64_encode($encrypted).chr(34); //wrap string with double quote (")
            }catch(\Exception $e){
                // var_dump('Encrypt error: '.$e->getMessage());die;
                throw new \Exception('Encrypt error: '.$e->getMessage());
                return false;
            }
        }
        return false;
    }

    public function getPrivateKey(){
        if (!$this->_privateKey) {
            $this->_privateKey = $this->scopeConfig->getValue(self::CONFIG_PRIVATE_KEY);
        }
        return $this->_privateKey;
    }

    public function getPublicKey(){
        if (!$this->_publicKey) {
            $this->_publicKey = $this->scopeConfig->getValue(self::CONFIG_PUBLIC_KEY);
        }
        return $this->_publicKey;
    }
}