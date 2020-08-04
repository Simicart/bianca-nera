<?php
namespace Simi\Simicustomize\Model\Api;

class Instagram implements \Simi\Simicustomize\Api\InstagramInterface
{
    public $simiObjectManager;
    public $config;
    public $configWriter;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\ObjectManagerInterface $simiObjectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
    )
    {
        $this->request = $request;
        $this->simiObjectManager = $simiObjectManager;
        $this->config = $config;
        $this->configWriter = $configWriter;
        return $this;
    }

    /**
     * Get instagram url to request authorization code
     * @return String
     */
    public function auth() {
        // $proxy = $this->simiObjectManager->get('\Simi\Simicustomize\Model\Proxy');
        $apiUrl = 'https://api.instagram.com/';
        $client_id = $this->config->getValue('simiconnector/instagram/client_id');
        $redirect_uri = $this->config->getValue('simiconnector/instagram/redirect_uri');
        if ($client_id && $redirect_uri) {
            $url = $apiUrl.'oauth/authorize?client_id='.$client_id.'&redirect_uri='.$redirect_uri.
                '&scope=user_profile,user_media&response_type=code';
            return [$url];
        }
        return [false];
    }

    /**
     * Request access token from Instagram
     * @param string $code
     * @return boolean
     */
    public function getAccessToken($code){
        if ($code) {
            // curl -X POST https://api.instagram.com/oauth/access_token \
            // -F client_id=295452.......... \
            // -F client_secret=d82e56c5e545217f4c56........... \
            // -F grant_type=authorization_code \
            // -F redirect_uri=https://bianca-nera.com/instagram_auth/ \
            // -F code=AQDKMcbkcRk-cTihDbLrF...
            $proxy = $this->simiObjectManager->get('\Simi\Simicustomize\Model\Proxy');
            $client_id = $this->config->getValue('simiconnector/instagram/client_id');
            $client_secret = $this->config->getValue('simiconnector/instagram/client_secret');
            $redirect_uri = $this->config->getValue('simiconnector/instagram/redirect_uri');
            if ($client_id && $client_secret && $redirect_uri) {
                $apiUrl = 'https://api.instagram.com/oauth/access_token';
                // Post fields
                $params = [
                    'client_id' => $client_id,
                    'client_secret' => $client_secret,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $redirect_uri,
                    'code' => $code
                ];
                // Call curl
                $_ch = curl_init();
                curl_setopt($_ch, CURLOPT_URL, $apiUrl);
                curl_setopt($_ch, CURLOPT_ENCODING, "");
                curl_setopt($_ch, CURLOPT_POST, true);
                $postData = http_build_query($params);
                curl_setopt($_ch, CURLOPT_POSTFIELDS, $postData);
                curl_setopt($_ch, CURLOPT_HEADER, false);
                curl_setopt($_ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($_ch);
                curl_close($_ch); // close curl

                $response = json_decode($response, true);

                if (isset($response['access_token'])) {
                    $access_token = $response['access_token'];
                    $this->configWriter->save('simiconnector/instagram/access_token', $access_token);
                    return true;
                }
            }
        }
        return false;
    }
}
