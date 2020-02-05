<?php

namespace Simi\Simicustomize\Override\Helper;

class Customer extends \Simi\Simiconnector\Helper\Customer
{

    /* override this function to add data for new attribute mobilephone when customer register new account 
     * $customer - Customer Model
     * $data - Data Object
     * a. Magento\Customer\Model\Data\Customer
     * b. Magento\Customer\Model\Customer
     *
     */

    public function applyDataToCustomer(&$customer, $data)
    {
        if (isset($data->day) && $data->day != "") {
            $birthday = $data->year . "-" . $data->month . "-" . $data->day;
            $customer->setDob($birthday);
        }

        if (isset($data->taxvat)) {
            $customer->setTaxvat($data->taxvat);
        }

        if (isset($data->gender) && $data->gender) {
            $customer->setGender($data->gender);
        }
        if (isset($data->prefix) && $data->prefix) {
            $customer->setPrefix($data->prefix);
        }

        if (isset($data->middlename) && $data->middlename) {
            $customer->setMiddlename($data->middlename);
        }

        if (isset($data->suffix) && $data->suffix) {
            $customer->setSuffix($data->suffix);
        }
        if (isset($data->telephone) && $data->telephone) {
            $customer->setCustomAttribute('mobilenumber', $data->telephone);
        }
        //        if (!isset($data->password)) {
        //            $encodeMethod = 'md5';
        //            $data->password = 'simipassword'
        //                    . rand(pow(10, 9), pow(10, 10)) . substr($encodeMethod(microtime()), rand(0, 26), 5);
        //        }
    }

    public function validateSimiPass($username, $password, $from = null)
    {
        $tokenModel = $this->simiObjectManager->get('Simi\Simiconnector\Model\Customertoken')
            ->getCollection()
            ->addFieldToFilter('token', $password)
            ->getFirstItem();
        if ($tokenModel->getId() && $customerId = $tokenModel->getData('customer_id')) {
            $customerModel = $this->simiObjectManager->get('Magento\Customer\Model\Customer')->load($customerId);
            if ($customerEmail = $customerModel->getData('email')) {
                if (strtolower($customerEmail) == strtolower($username))
                    return true;
            }
        }
        /*
        $encodeMethod = 'md5';
        if ($from && $from == 'social_login') {
            if ($password == 'Simi123a@'.$encodeMethod($this->simiObjectManager
                    ->get('Magento\Framework\App\Config\ScopeConfigInterface')
                                ->getValue('simiconnector/general/secret_key') . $username)) {
                return true;
            }
        }
        if ($password == $encodeMethod($this->simiObjectManager
                ->get('Magento\Framework\App\Config\ScopeConfigInterface')
                                ->getValue('simiconnector/general/secret_key') . $username)) {
            return true;
        }
        */
        return false;
    }

    public function getCustomerByEmail($email)
    {
        return $this->simiObjectManager->get('Magento\Customer\Model\Customer')
            ->setWebsiteId($this->storeManager->getStore()->getWebsiteId())
            ->loadByEmail($email);
    }

    public function loginByCustomer($customer)
    {
        $this->_getSession()->setCustomerAsLoggedIn($customer);
    }

    public function loginByEmailAndPass($username, $password)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customer  = $this->simiObjectManager->get('Magento\Customer\Model\Customer')
            ->setWebsiteId($websiteId);
        if ($this->validateSimiPass($username, $password)) {
            $customer = $this->getCustomerByEmail($username);
            if ($customer->getId()) {
                $this->loginByCustomer($customer);
                return true;
            }
        } elseif ($customer->authenticate($username, $password)) {
            $this->loginByCustomer($customer);
            return true;
        }
        return false;
    }
}
