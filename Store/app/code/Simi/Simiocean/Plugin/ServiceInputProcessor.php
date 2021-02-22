<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Simi\Simiocean\Plugin;


/**
 * @package Simi\Simiocean\Plugin
 */
class ServiceInputProcessor
{
    public function beforeProcess(
        \Magento\Framework\Webapi\ServiceInputProcessor $subject, 
        $serviceClassName, $serviceMethodName, $inputArray
    ){
        if ($serviceClassName == 'Magento\Catalog\Api\ProductRepositoryInterface' &&
            $serviceMethodName === 'save'
        ) {
            // This code integrate with Ocean System, if other system need work together then check the ip address of something
            if (isset($inputArray['product'])) {
                if (!isset($inputArray['product']['custom_attributes'])) {
                    $inputArray['product']['custom_attributes'] = [];
                }
                $inputArray['product']['custom_attributes'][] = array(
                    'attribute_code' => 'is_ocean',
                    'value' => '1'
                );
                $inputArray['product']['custom_attributes'][] = array(
                    'attribute_code' => 'approval',
                    'value' => '2' // for Approved
                );
                $inputArray['product']['custom_attributes'][] = array(
                    'attribute_code' => 'is_admin_sell',
                    'value' => '0' // for Approved
                );
            }
        }
        if ($serviceClassName == 'Magento\Catalog\Api\ProductAttributeOptionManagementInterface' &&
            $serviceMethodName === 'add'
        ) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $logtable = $objectManager->create('Simi\Simiocean\Model\Logtable');
            // This code integrate with Ocean System, if other system need work together then check the ip address of something
            if (isset($inputArray)) {
                $logtable->setLogName('Create Attribute Option');
                $logtable->setData('option1', 'products/attributes/{attributeCode}/options');
                $logtable->setData('data', json_encode($inputArray));
                $logtable->setData('created_at', gmdate('Y-m-d H:i:s'));
                $logtable->save();
            }
        }
        return array($serviceClassName, $serviceMethodName, $inputArray);
    }
}
