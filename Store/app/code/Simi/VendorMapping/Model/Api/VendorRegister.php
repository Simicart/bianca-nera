<?php

/**
 * Copyright © 2016 Simi. All rights reserved.
 */

namespace Simi\VendorMapping\Model\Api;

use Magento\Framework\App\Filesystem\DirectoryList;
use Vnecoms\Vendors\Controller\Seller\RegisterPost;
use Vnecoms\Vendors\Model\Session as VendorSession;
use Vnecoms\Vendors\Model\Vendor;
use Simi\VendorMapping\Api\VendorRegisterInterface;

class VendorRegister extends RegisterPost implements VendorRegisterInterface
{
    protected $_mediaDir;

    public function registerPost()
    {

        if (!$this->_vendorHelper->moduleEnabled()) {
            return [[
                'status' => 'error',
                'message' => __('Module not enabled.')
            ]];
        }

        if (!$this->_vendorHelper->isEnableVendorRegister()) {
            return [[
                'status' => 'error',
                'message' => __("You don't have permission to access this page")
            ]];
        }

        if ($this->getRequest()->isPost()) {
            $vendorData = $this->getRequest()->getPost();
            if (!isset($vendorData['username'])) {
                try {
                    $requestBody = $this->getRequest()->getContent();
                    $vendorData = json_decode($requestBody, true);
                } catch (\Exception $e) {
                    return [[
                        'status' => 'error',
                        'message' => __('Request body not a json string.')
                    ]];
                }
            }

            if ($vendorData && is_array($vendorData)) {
                try {
                    $simiObjectManager = $this->_objectManager;
                    $websiteid = $vendorData['vendor_data']['website_id'] ?? null;
                    $mobile = $vendorData['vendor_data']['telephone'] ?? null;

                    $vendor = $this->_vendorFactory->create();
                    $vendor->setData($vendorData);
                    $vendor->setVendorId($vendorData['vendor_data']['vendor_id']);
                    $vendor->setGroupId($this->_vendorHelper->getDefaultVendorGroup());

                    // $customer = $this->_vendorSession->getCustomer();
                    $helperCustomer = $simiObjectManager->create('Simi\Simicustomize\Override\Helper\Customer');
                    $customer = $helperCustomer->getCustomerByEmail($vendorData['email']);
                    if (!$customer->getId()) {
                        // Not exist customer account. Find by phone number
                        if ($websiteid && $mobile) {
                            $customerData = $simiObjectManager->create('\Magento\Customer\Model\Customer');
                            $customerSearch = $customerData->getCollection()->addFieldToFilter("mobilenumber", $mobile)
                                ->addFieldToFilter("website_id", $websiteid);

                            if (count($customerSearch) > 0) {
                                return [[
                                    'status' => 'error',
                                    'message' => __('Already exist account with this phone number !')
                                ]];
                            }
                        }
                        // Create new customer account
                        $customer = $helperCustomer->createCustomer($vendorData);
                    } else {
                        // exist customer 
                        // check phone number the same or not ?
                        $customerTelephone = $customer->getData('mobilenumber');
                        if ($customerTelephone != $vendorData['vendor_data']['telephone']) {
                            return [[
                                'status' => 'error',
                                'message' => __('There is a customer registered with this email address. Please use the same phone number to register as a designer.')
                            ]];
                        }
                        // check password vendor as same as password of customer or not ?
                        if (!$customer->validatePassword($vendorData['password'])) {
                            // throw new \Simi\Simiconnector\Helper\SimiException(__('Your password does not match your customer account password !'), 4);
                            return [[
                                'status' => 'error',
                                'message' => __('Your password does not match your customer account password !')
                            ]];
                        } else {
                            $idVendorIfExist = $vendor->loadByCustomer($customer)->getId();
                            if ($idVendorIfExist) {
                                return [[
                                    'status' => 'error',
                                    'message' => __('There is already an account with this email address !')
                                ]];
                            }
                        }
                    }
                    $vendor->setCustomer($customer);
                    $vendor->setWebsiteId($customer->getWebsiteId());
                    $vendor->setData('country_id', $vendorData['vendor_data']['country_id'] ?? '');
                    // $vendor->setData('postcode', $vendorData['vendor_data']['postcode']);
                    $vendor->setData('city', $vendorData['vendor_data']['city'] ?? '');
                    $vendor->setData('region', $vendorData['vendor_data']['region'] ?? '');
                    $vendor->setData('telephone', $vendorData['vendor_data']['telephone'] ?? '');
                    $vendor->setData('company', $vendorData['vendor_data']['company'] ?? '');
                    if (isset($vendorData['vendor_data']['website'])) {
                        $vendor->setData('website', $vendorData['vendor_data']['website']);
                    }
                    if (isset($vendorData['vendor_data']['facebook'])) {
                        $vendor->setData('facebook', $vendorData['vendor_data']['facebook']);
                    }
                    if (isset($vendorData['vendor_data']['website'])) {
                        $vendor->setData('instagram', $vendorData['vendor_data']['instagram']);
                    }
                    /*Add new customer credit account*/
                    $credit = $simiObjectManager->create('Vnecoms\Credit\Model\Credit');
                    $credit->load($customer->getId(), 'customer_id');
                    if (!$credit->getId()) {
                        $credit->setData([
                            'customer_id' => $customer->getId(),
                            'credit' => 0,
                        ])->setId(null)->save();
                    }

                    if ($this->_vendorHelper->isRequiredVendorApproval()) {
                        $vendor->setStatus(Vendor::STATUS_PENDING);
                        $message = __("Your seller account has been created and awaiting for approval. You may be need to check mailbox to activate your account.");
                    } else {
                        $vendor->setStatus(Vendor::STATUS_APPROVED);
                        $message = __("Your seller account has been created. You may be need to check mailbox to activate your account.");
                    }

                    $errors = $vendor->validate();

                    if ($errors !== true) {
                        return [[
                            'status' => 'error',
                            'message' => __(implode(", ", $errors))
                        ]];
                    }

                    $vendor->save();

                    $banner = '';
                    // upload banner image
                    if (isset($vendorData['vendor_data']['banner'])) {
                        $banner = $this->saveUploadedImage('banner', $vendorData['vendor_data']['banner'], $vendor->getVendorId());
                        $vendorData['banner'] = $banner ? 'ves_vendors/attribute/banner/'.$banner : '';
                    }

                    $logo = '';
                    // upload logo image
                    if (isset($vendorData['vendor_data']['logo'])) {
                        $logo = $this->saveUploadedImage('logo', $vendorData['vendor_data']['logo'], $vendor->getVendorId());
                        $vendorData['logo'] = $logo ? 'ves_vendors/attribute/logo/'.$logo : '';
                    }

                    // Save other vendor configurations (Description, banner, logo)
                    $groupData = array(
                        'store_information' => array('fields' => array(
                            'logo' => array('value' => $logo ?? ''),
                            'banner' => array('value' => $banner ?? ''),
                            // 'name' => array('value' => $vendorData['vendor_data']['company'] ?? ''),
                            'company' => array('value' => $vendorData['vendor_data']['company'] ?? ''),
                            'short_description' => array('value' => $vendorData['vendor_data']['description'] ?? ''),
                    )));
                    $configHelper = $this->_objectManager->get('Vnecoms\VendorsConfig\Helper\Data');
                    $configHelper->saveConfig(
                        $vendor->getId(),
                        'general', 
                        $groupData // group data for save
                    );

                    if ($this->_vendorHelper->isUsedCustomVendorUrl()) {
                        return [[
                            'status' => 'error',
                            'message' => __('Your seller account has been created. You can now login to vendor panel.')
                        ]];
                    }

                    return [[
                        'status' => 'success',
                        'message' => __($message)
                    ]];
                } catch (\Exception $e) {
                    $this->_messageManager->addError($e->getMessage());
                    $this->_vendorSession->setFormData($vendorData);
                    return [[
                        'status' => 'error',
                        'message' => __($e->getMessage())
                    ]];
                }
            } else {
                return [
                    [
                        'status' => 'error',
                        'message' => __('POST data is invalid')
                    ]
                ];
            }
        }

        return [
            [
                'status' => 'error',
                'message' => __('Register error')
            ]
        ];
    }

    /**
     * Get media directory absolute path
     */
    protected function getMediaDir(){
        if (!$this->_mediaDir) {
            $filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');
            $this->_mediaDir = $filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        }
        return $this->_mediaDir;
    }

    /**
     * @param string $attribute attribute code to save
     * @param string $fullImagePath upload with full absolute path
     * @param int $vendorId vendor id
     * @return string
     */
    protected function saveUploadedImage($attribute, $fullImagePath, $vendorId, $isWithDirPath = false){
        $DS = DIRECTORY_SEPARATOR;
        $media = $this->getMediaDir();
        if ($fullImagePath && is_file($fullImagePath) && $media) {
            $fileDir = "ves_vendors${DS}attribute${DS}${attribute}" . $DS;
            if (!is_dir($media.$fileDir)) mkdir($media.$fileDir, 755, true);
            $ext = explode('.', $fullImagePath);
            $ext = count($ext) > 1 ? array_pop($ext) : 'jpg';
            $filename = $vendorId . '-' . $attribute . '.'.$ext;
            $file = $fileDir . $filename; // file save to
            if(rename($fullImagePath, $media.$file)){
                if ($isWithDirPath) {
                    return $fileDir . $filename;
                }
                return $filename;
            }
        }
        return '';
    }
}
