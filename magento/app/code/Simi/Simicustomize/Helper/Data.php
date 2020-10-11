<?php

/**
 * Connector data helper
 */

namespace Simi\Simicustomize\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Data extends \Simi\Simiconnector\Helper\Data
{
    public function getStoreConfig($path)
    {
        return $this->scopeConfig->getValue($path);
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function convertBearerToCustomerLoggedIn(){
        $httpAuthInfo = $_SERVER['HTTP_AUTHORIZATION'];
        $bearerInfo = explode(' ', $httpAuthInfo);
        $session = $this->simiObjectManager->get('Magento\Customer\Model\Session');
        if (isset($bearerInfo[0]) && $bearerInfo[0] == 'Bearer' &&
            isset($bearerInfo[1])) {
            $bearer = $bearerInfo[1];
            $tokenModel = $this->simiObjectManager->get('Magento\Integration\Model\Oauth\Token');
            $tokenModel->loadByToken($bearer);
            if ($tokenModel->getId()) {
                // $tokenModel->validate();
                $customerId = $tokenModel->getCustomerId();
                $customer = $this->simiObjectManager->get('Magento\Customer\Model\Customer')->load($customerId);
                $session->setCustomerAsLoggedIn($customer);
            }
        }
        return $session;
    }
}
