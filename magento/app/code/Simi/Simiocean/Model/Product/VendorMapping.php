<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model\Product;

use Vnecoms\Vendors\Model\Vendor;

class VendorMapping extends OptionMapping
{
    /**
     * Simi\Simiocean\Helper\Data
     */
    protected $helper;

    protected $_cached; // cached vendor id

    /**
     * Get vendor id with matching VendorID of ocean
     * @return string
     */
    public function getMatching($id, $enName, $arName = ''){
        if ($id && $enName) {
            if (isset($this->_cached[$id]) && $this->_cached[$id]) return $this->_cached[$id]; // if saved cache

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            if (class_exists('Vnecoms\Vendors\Model\Vendor')) {
                $vendorModel = $objectManager->get('Vnecoms\Vendors\Model\Vendor');
                $collection = $vendorModel->getCollection();
                $collection->addAttributeToFilter('ocean_brand_id', $id) // filter by ocean BrandID
                    ->getSelect()
                    ->limit(1);

                try {

                    $vendorHelper = $objectManager->get('Vnecoms\Vendors\Helper\Data');
                    $configHelper = $objectManager->get('Vnecoms\VendorsConfig\Helper\Data');

                    $vendor = '';
                    if ($collection->getSize()) {
                        $vendor = $collection->getFirstItem();
                    }
                    if ($vendor && $vendor->getId()) {
                        $storeName = $arName ?: $enName;

                        // Save vendor store config data
                        if ($vendorHelper->getVendorStoreName($vendor->getId()) != $storeName) {
                            // $groupData = array(
                            //     'store_information' => array(
                            //         'fields' => array(
                            //             'name' => array('value' => $storeName), // store name
                            //         )
                            // ));
                            // $configHelper->saveConfig($vendor->getId(), 'general', $groupData);
                            $configModel = $objectManager->create('Vnecoms\VendorsConfig\Model\Config');
                            $configModel->setData([
                                'vendor_id' => $vendor->getId(),
                                'store_id' => 0,
                                'path' => 'general/store_information/name',
                                'value' => $storeName,
                            ]);
                            $configModel->save();
                        }

                        $this->_cached[$id] = $vendor->getId();

                        return $vendor->getId(); // id int
                    } else { // create if not exists
                        $vendorId = strtolower(str_replace(' ', '_', $enName));
                        $email = str_replace(' ', '_', $enName) .'_'. substr(str_shuffle('qwertyuiopasdfghjklzxcvbnm0123456789'), 0, 6) .'@bianca-nera.com';
                        $enNames = explode(' ', $enName);
                        $firstname = $lastname = $enName;
                        if (count($enNames) > 1) {
                            $firstname = array_shift($enNames);
                            $lastname = implode(' ', $enNames);
                        }

                        $vendorModel = $objectManager->create('Vnecoms\Vendors\Model\Vendor');
                        $vendorModel->setId(null)
                            ->setVendorId($vendorId)
                            ->setOceanBrandId($id);
                        $vendorModel->setGroupId($vendorHelper->getDefaultVendorGroup());

                        $customerHelper = $objectManager->get('Simi\Simiconnector\Helper\Customer');
                        $customer = $customerHelper->createCustomer([
                            'firstname' => $firstname,
                            'lastname' => $lastname,
                            'email' => $email,
                            'password' => 'Biancanera1@3'
                        ]);
                        $vendorModel->setCustomer($customer);
                        $vendorModel->setWebsiteId($customer->getWebsiteId());

                        // if ($vendorHelper->isRequiredVendorApproval()) {
                        //     $vendorModel->setStatus(Vendor::STATUS_PENDING);
                        // } else {
                            $vendorModel->setStatus(Vendor::STATUS_APPROVED);
                        // }
    
                        // $errors = $vendorModel->validate();
                        // if ($errors !== true) {
                        //     throw new \Exception(implode(", ", $errors));
                        // }
    
                        $vendorModel->save();

                        // Save vendor store config data
                        $configModel = $objectManager->create('Vnecoms\VendorsConfig\Model\Config');
                        $configModel->setData([
                            'vendor_id' => $vendor->getId(),
                            'store_id' => 0,
                            'path' => 'general/store_information/name',
                            'value' => $storeName,
                        ]);
                        $configModel->save();

                        $this->_cached[$id] = $vendorModel->getId();

                        return $vendorModel->getId(); // id int
                    }
                } catch(\Exception $e) {
                    throw new \Exception($e->getMessage());
                }
            }
        }
        return '';
    }
}