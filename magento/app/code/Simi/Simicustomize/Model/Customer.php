<?php

/**
 * Copyright © 2019 Simi. All rights reserved.
 */

namespace Simi\Simicustomize\Model;

use Hybrid_Auth;

use function PHPSTORM_META\type;

class Customer extends \Simi\Simiconnector\Model\Customer
{

    public function login($data)
    {
        return $this->simiObjectManager->get('Simi\Simicustomize\Override\Helper\Customer')
            ->loginByEmailAndPass($data['params']['email'], $data['params']['password']);
    }

    /*
     * Social Login (post method)
     * @param 
     * $data - Object with at least:
     * $data['contents']->email
     * $data['contents']->password
     * $data['contents']->firstname
     * $data['contents']->lastname
     * $data['contents']->telephone
     */

    public function socialLogin($data)
    {
        $data = (object) $data['contents'];
        if (!$data->email) {
            throw new \Simi\Simiconnector\Helper\SimiException(__('Cannot Get Your Email. Please let your application provide an email to login.'), 4);
        }
        $customer = $this->simiObjectManager
            ->get('Simi\Simicustomize\Override\Helper\Customer')->getCustomerByEmail($data->email);

        if (!$customer->getId()) {
            // Create new customer account for social network
            $customer = $this->_createCustomer($data);
        }

        if ($customer->getId()) {
            // If exist account with that email, check confirmation
            // if ($customer->getConfirmation()) {
            //     throw new \Simi\Simiconnector\Helper\SimiException(__('This account is not confirmed. Verify and try again.'), 4);
            // }
            //Check authenticate with facebook, google or twitter
            // Only twitter need accessTokenSecret
            if (isset($data->providerId) && isset($data->accessToken)) {
                switch ($data->providerId) {
                    case "facebook.com":
                        try {
                            $fbId = $this->simiObjectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('simiconnector/social_login/facebook_id');
                            $fbSecret = $this->simiObjectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('simiconnector/social_login/facebook_secret');
                            if ($fbId && $fbSecret) {
                                $config = [
                                    'callback'  => \Hybridauth\HttpClient\Util::getCurrentUrl(),
                                    'keys' => ['id' => $fbId, 'secret' => $fbSecret],
                                    'endpoints' => [
                                        'api_base_url'     => 'https://graph.facebook.com/v2.12/',
                                        'authorize_url'    => 'https://www.facebook.com/dialog/oauth',
                                        'access_token_url' => 'https://graph.facebook.com/oauth/access_token',
                                    ]
                                ];
                                $adapter = new \Hybridauth\Provider\Facebook($config);

                                $adapter->setAccessToken(['access_token' => $data->accessToken]);

                                $userProfile = $adapter->getUserProfile();

                                $adapter->disconnect();
                            } else {
                                throw new \Simi\Simiconnector\Helper\SimiException(__('Administrator need configure social login !'), 4);
                            }
                        } catch (\Exception $e) {
                            throw new \Simi\Simiconnector\Helper\SimiException(__($e->getMessage()), 4);
                        }
                        break;
                    case "google.com":
                        try {
                            $googleId = $this->simiObjectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('simiconnector/social_login/google_id');
                            $googleSecret = $this->simiObjectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('simiconnector/social_login/google_secret');
                            if ($googleId && $googleSecret) {
                                $config = [
                                    'callback'  => \Hybridauth\HttpClient\Util::getCurrentUrl(),
                                    'keys' => ['id' => $googleId, 'secret' => $googleSecret]
                                ];

                                $adapter = new \Hybridauth\Provider\Google($config);

                                $adapter->setAccessToken(['access_token' => $data->accessToken]);

                                $userProfile = $adapter->getUserProfile();

                                $adapter->disconnect();
                            } else {
                                throw new \Simi\Simiconnector\Helper\SimiException(__('Administrator need configure social login !'), 4);
                            }
                        } catch (\Exception $e) {
                            throw new \Simi\Simiconnector\Helper\SimiException(__($e->getMessage()), 4);
                        }
                        break;
                    case "twitter.com":
                        try {
                            // $currentUrl = \Hybridauth\HttpClient\Util::getCurrentUrl();
                            $twitterKey = $this->simiObjectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('simiconnector/social_login/twitter_key');
                            $twitterSecret = $this->simiObjectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('simiconnector/social_login/twitter_secret');

                            if ($twitterKey && $twitterSecret) {

                                $config = [
                                    'callback'  => \Hybridauth\HttpClient\Util::getCurrentUrl(),
                                    'keys' => ['key' => 'inE1PMSfzSbJZFjzar2pruHC9', 'secret' => 'EqZ7rrFcnGmdAfovg2NEyCNBXxunRSaXcjpxesinnrVEguqS2l'],
                                    'authorize' => true
                                ];

                                $adapter = new \Hybridauth\Provider\Twitter($config);
                                if ($data->accessTokenSecret) {
                                    $adapter->setAccessToken([
                                        'access_token' => $data->accessToken,
                                        'access_token_secret' => $data->accessTokenSecret
                                    ]);
                                } else {
                                    throw new \Simi\Simiconnector\Helper\SimiException(__('Twitter need access token secret !'), 4);
                                }

                                $userProfile = $adapter->getUserProfile();

                                $adapter->disconnect();
                            } else {
                                throw new \Simi\Simiconnector\Helper\SimiException(__('Administrator need configure social login !'), 4);
                            }
                        } catch (\Exception $e) {
                            throw new \Simi\Simiconnector\Helper\SimiException(__($e->getMessage()), 4);
                        }
                        break;
                }

                // Check if exist response from facebook, google or twitter
                if ($userProfile && $userProfile->identifier) {
                    // Check if above identifier the same as the identifier returned by pwa studio
                    if ($userProfile->identifier === $data->userSocialId) {
                        // If the same -> force login ( need return 2 fields: customer_access_token and customer_identity)

                        // Login by customer object, this function only create new customer session id ( customer_identity)
                        $this->simiObjectManager
                            ->get('Simi\Simicustomize\Override\Helper\Customer')->loginByCustomer($customer);
                        // Create new customer access token ( customer_access_token )
                        $tokenModel = $this->simiObjectManager->create('\Magento\Integration\Model\Oauth\Token');
                        $tokenModel->createCustomerToken($customer->getId());
                    } else {
                        // Not the same, show error
                        throw new \Simi\Simiconnector\Helper\SimiException(__('Your account is Invalid !'), 4);
                    }
                } else {
                    throw new \Simi\Simiconnector\Helper\SimiException(__('Your account is not authenticated by ' . $data->providerId . ' !'), 4);
                }
            } else {
                throw new \Simi\Simiconnector\Helper\SimiException(__('Invalid login !'), 4);
            }
        } else {
            // if (!$data->firstname) {
            //     $data->firstname = __('Firstname');
            // }
            // if (!$data->lastname) {
            //     $data->lastname = __('Lastname');
            // }
            // // Create new customer account for social network
            // $customer = $this->_createCustomer($data);
            // // Notify user to check mailbox and verify new account
            // throw new \Simi\Simiconnector\Helper\SimiException(__('Please check your mailbox to active your account !.'), 4);
        }
    }
    public function updateProfile($data)
    {
        $data     = $data['contents'];
        $result   = [];

        $customer = $this->simiObjectManager->create('Magento\Customer\Model\Customer');
        $customer->setWebsiteId($this->storeManager->getStore()->getWebsiteId());
        $customer->loadByEmail($data->email);

        $customerData = [
            'firstname' => $data->firstname,
            'lastname'  => $data->lastname,
            'email'     => $data->email,
        ];

        // Fix bug 'invalid state change requested' when change password.
        // The reason is quote has customer_id is null
        $cart = $this->simiObjectManager->get('Magento\Checkout\Model\Cart');
        $cart->getQuote()->setData('customer_id', $customer->getId());
        $cart->saveQuote();

        if (isset($data->change_password) && $data->change_password == 1) {
            $currPass = $data->old_password;
            $newPass  = $data->new_password;
            $confPass = $data->com_password;
            $customer->setChangePassword(1);
            if ($customer->authenticate($data->email, $currPass)) {
                if ($newPass != $confPass) {
                    throw new \Magento\Framework\Exception\InputException(
                        __('Password confirmation doesn\'t match entered password.')
                    );
                }
                $customer->setPassword($newPass);
                $customer->setConfirmation($confPass);
                $customer->setPasswordConfirmation($confPass);
            }
        }
        $this->setCustomerData($customer, $data);
        $customerForm   = $this->simiObjectManager->get('Magento\Customer\Model\Form');
        $customerForm->setFormCode('customer_account_edit')
            ->setEntity($customer);
        $customerErrors = $customerForm->validateData($customer->getData());
        if ($customerErrors !== true) {
            if (is_array($customerErrors)) {
                throw new \Simi\Simiconnector\Helper\SimiException($customerErrors[0], 4);
            } else {
                throw new \Simi\Simiconnector\Helper\SimiException($customerErrors, 4);
            }
        } else {
            $customerForm->compactData($customerData);
        }

        if (is_array($customerErrors)) {
            throw new \Simi\Simiconnector\Helper\SimiException(__('Invalid profile information'), 4);
        }
        $customer->setConfirmation(null);
        if (isset($data->telephone)) {
            $CollectionCustomer = $this->simiObjectManager->create('Magento\Customer\Model\ResourceModel\Customer\CollectionFactory');
            $resultCollection = $CollectionCustomer->create()
                ->addAttributeToFilter('mobilenumber', $data->telephone)
                ->load();
            if (count($resultCollection) > 0) {
                $message = __('Aready exist phone number !');
                throw new \Simi\Simiconnector\Helper\SimiException(__($message), 4);
            } else {
                $customer->setData('mobilenumber', $data->telephone);
            }
        }

        $customer->save();
        $this->_getSession()->setCustomer($customer);
        return $customer;
    }

    /*
     * Create Customer
     * @param
     * $data - Object with at least:
     * $data->firstname
     * $data->lastname
     * $data->email
     * $data->password
     */

    protected function _createCustomer($data)
    {
        $websiteid = $data->website_id ?? null;
        $mobile = $data->telephone ?? null;
        if ($websiteid && $mobile) {
            $customerData = $this->simiObjectManager->create('\Magento\Customer\Model\Customer');
            $customerSearch = $customerData->getCollection()->addFieldToFilter("mobilenumber", $mobile)
                ->addFieldToFilter("website_id", $websiteid);

            if (count($customerSearch) > 0) {
                throw new \Simi\Simiconnector\Helper\SimiException(__('Already exist account with this phone number !'), 4);
            }
        }

        $customer = $this->simiObjectManager->create('Magento\Customer\Api\Data\CustomerInterface')
            ->setFirstname($data->firstname)
            ->setLastname($data->lastname)
            ->setEmail($data->email);
        $this->simiObjectManager->get('Simi\Simicustomize\Override\Helper\Customer')->applyDataToCustomer($customer, $data);

        $password = null;
        if (isset($data->password) && $data->password) {
            $password = $data->password;
        }
        $customer = $this->getAccountManagement()->createAccount($customer, $password, '');
        $subscriberFactory = $this->simiObjectManager->get('Magento\Newsletter\Model\SubscriberFactory');
        if (isset($data->news_letter) && ($data->news_letter == '1')) {
            $subscriberFactory->create()->subscribeCustomerById($customer->getId());
        } else {
            $subscriberFactory->create()->unsubscribeCustomerById($customer->getId());
        }
        $customer = $this->simiObjectManager->create('Magento\Customer\Model\Customer')->load($customer->getId());
        return $customer;
    }
}
