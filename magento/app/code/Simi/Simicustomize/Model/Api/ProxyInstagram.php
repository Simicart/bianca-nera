<?php
namespace Simi\Simicustomize\Model\Api;

class ProxyInstagram implements \Simi\Simicustomize\Api\ProxyInstagramInterface
{
    public $simiObjectManager;
    public $config;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\ObjectManagerInterface $simiObjectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $config
    )
    {
        $this->request = $request;
        $this->simiObjectManager = $simiObjectManager;
        $this->config = $config;
        return $this;
    }

    /**
     * V2
     * Example: api?limit=10
     * Save Reserve request
     * @return boolean
     */
    public function index() {
        $limit = 18; //default
        $proxy = $this->simiObjectManager->get('\Simi\Simicustomize\Model\Proxy');
        $rqLimit = $this->request->getParam('limit');
        if ($rqLimit) {
            $limit = $rqLimit;
        }

        $apiUrl = 'https://api.instagram.com/';
        // $access_token = 'IGQVJWVEFkTjgxa0s3N2Jtd2diRVJXQnhTVE5DOXlDNm1ZAVmZALd2wtbU5iWTN0SE1YV1JDYjJPVHowY2R4Szh5UmdEVUVGdDhmbmFheUQ3bnpCQldENjhNenNoZAUx4dlZAsYVRxcmZAn';
        $access_token = $this->config->getValue('simiconnector/instagram/access_token');
        $userInfoJson = false;
        if ($access_token) {
            $graphqlApi = $apiUrl.'me/media?fields=id,media_type,media_url,permalink,caption,username,timestamp&access_token=';
            $userInfoJson = $proxy->query($graphqlApi.$access_token."&variables={\"first\":\"${limit}\"}");
            // Refresh access_token
            // $graphqlApi = $apiUrl.'refresh_access_token?grant_type=ig_refresh_token&access_token='.$access_token;
            // $proxy->query($graphqlApi.$access_token."&variables={\"first\":\"${limit}\"}");
        }

        if ($userInfoJson) {
            return [json_decode($userInfoJson, true)];
        }
        return false;
    }

    /**
     * V1.1
     * Save Reserve request
     * @return boolean
     */
    /* public function index() {
        $limit = 18; //default
        $proxy = $this->simiObjectManager->get('\Simi\Simicustomize\Model\Proxy');
        $path = $this->request->getParam('path');
        $rqLimit = $this->request->getParam('limit');
        if ($rqLimit) {
            $limit = $rqLimit;
        }
        $path = trim($path, '/');
        $instagram = 'https://www.instagram.com';
        $queryHash = '7c8a1055f69ff97dc201e752cf6f0093';
        $userId = '270293725';
        $infoApi = "${instagram}/graphql/query/?query_hash=${queryHash}&variables={\"id\":\"${userId}\",\"first\":\"${limit}\"}";
        $userInfoJson = $proxy->query($infoApi);
        $error = false;
        try{
            $userInfos = json_decode($userInfoJson, true);
        }catch(\Exception $e){
            $error = true;
        }
        if ($error || !isset($userInfos['data']['user']['edge_owner_to_timeline_media'])) {
            //find new query_hash
            $url = $instagram.'/'.$path.'/';
            $request1 = $proxy->query($url);
            preg_match('/\/static\/(([A-z0-9])*?\/){0,3}ProfilePageContainer\.js\/([A-z0-9])*?\.js/', $request1, $matcheds);
            if (isset($matcheds[0])) {
                $jsUrl = $instagram.$matcheds[0];
                $request2 = $proxy->query($jsUrl);
                preg_match('/profilePosts.*?pagination.*?queryId:["\'](.*?)["\'],/', $request2, $matcheds);
                if(isset($matcheds[1])){
                    $queryHash = $matcheds[1];
                    $infoApi = "${instagram}/graphql/query/?query_hash=${queryHash}&variables={\"id\":\"${userId}\",\"first\":\"${limit}\"}";
                    $userInfoJson = $proxy->query($infoApi);
                }
            }
        }
        if($userInfoJson){
            die($userInfoJson);
        }
        return false;
    } */

    /**
     * V1
     * Save Reserve request
     * @return boolean
     */
    /* public function index() {
        $limit = 18; //default
        $proxy = $this->simiObjectManager->get('\Simi\Simicustomize\Model\Proxy');
        $path = $this->request->getParam('path');
        $rqLimit = $this->request->getParam('limit');
        if ($rqLimit) {
            $limit = $rqLimit;
        }
        $path = trim($path, '/');
        $instagram = 'https://www.instagram.com';
        $queryHash = '7c8a1055f69ff97dc201e752cf6f0093';
        //get userId
        $userInfoApi = "${instagram}/${path}/?__a=1";
        $userInfo = $proxy->query($userInfoApi);
        $user = json_decode($userInfo, true);
        if (isset($user['graphql']['user']['id'])) {
            $userId = $user['graphql']['user']['id'];
            $infoApi = "${instagram}/graphql/query/?query_hash=${queryHash}&variables={\"id\":\"${userId}\",\"first\":\"${limit}\"}";
            $userInfoJson = $proxy->query($infoApi);
            $error = false;
            try{
                $userInfos = json_decode($userInfoJson, true);
            }catch(\Exception $e){
                $error = true;
            }
            if ($error || !isset($userInfos['data']['user']['edge_owner_to_timeline_media'])) {
                //find new query_hash
                $url = $instagram.'/'.$path.'/';
                $request1 = $proxy->query($url);
                preg_match('/\/static\/(([A-z0-9])*?\/){0,3}ProfilePageContainer\.js\/([A-z0-9])*?\.js/', $request1, $matcheds);
                if (isset($matcheds[0])) {
                    $jsUrl = $instagram.$matcheds[0];
                    $request2 = $proxy->query($jsUrl);
                    preg_match('/profilePosts.*?pagination.*?queryId:["\'](.*?)["\'],/', $request2, $matcheds);
                    if(isset($matcheds[1])){
                        $queryHash = $matcheds[1];
                        $infoApi = "${instagram}/graphql/query/?query_hash=${queryHash}&variables={\"id\":\"${userId}\",\"first\":\"${limit}\"}";
                        $userInfoJson = $proxy->query($infoApi);
                    }
                }
            }
            if($userInfoJson){
                die($userInfoJson);
            }
        }
        return false;
    } */
}
