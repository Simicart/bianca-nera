<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Simi\Simiconnector\Model\Resolver\DataProvider;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\StoreConfigManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * StoreConfig field data provider, used for GraphQL request processing.
 */
class Simistoreconfigdataprovider extends DataProviderInterface
{
    /**
     * Get store config data
     *
     * @return array
     */
    public function getSimiStoreConfigData($args){
        $storeApi = $this->simiObjectManager->get('Simi\Simiconnector\Model\Api\Storeviews');
        $storeManager = $this->simiObjectManager->get('\Magento\Store\Model\StoreManagerInterface');

        $cartId = false;
        $this->request = $this->simiObjectManager->get('\Magento\Framework\App\Request\Http');

        $contents            = $this->request->getContent();
        $contents_array      = [];
        if ($contents && ($contents != '')) {
            $contents_parser = urldecode($contents);
            $contents_array = json_decode($contents_parser, true);
        }

        if ($contents_array) {
            if (isset($contents_array['variables']['cartId'])) {
                $cartId = $contents_array['variables']['cartId'];
            }
        }

        //in case of GET graphQL
        $graphQLVariables = $this->request->getParam('variables');
        if ($graphQLVariables) {
            $graphQLVariables = json_decode($graphQLVariables, true);
            if ($graphQLVariables && is_array($graphQLVariables)) {
                if (isset($graphQLVariables['cartId']))
                    $cartId = $graphQLVariables['cartId'];
            }
        }

        if ($cartId) {
            $quoteIdMask = $this->simiObjectManager->get('Magento\Quote\Model\QuoteIdMask');
            if ($quoteIdMask->load($cartId, 'masked_id')) {
                if ($quoteIdMask && $maskQuoteId = $quoteIdMask->getData('quote_id'))
                    $cartId = $maskQuoteId;
            }
            if ($cartId) {
                $quoteModel = $this->simiObjectManager->create('\Magento\Quote\Model\Quote')->load($cartId);
                if ($quoteModel->getId()) {
                    $storeId = $storeManager->getStore()->getId();
                    $currencyCode   = $this->storeManager->getStore()->getCurrentCurrencyCode();
                    if ($storeId && $quoteModel->getData('store_id') !== $storeId) {
                        $quoteModel->setStoreId($storeId)->collectTotals()->save();

                        if ($_SERVER['REMOTE_ADDR'] == '27.72.60.252' || $_SERVER['REMOTE_ADDR']  =='118.70.146.183') {
                            die('1');
                        }
                    }
                    if ($currencyCode && $quoteModel->getQuoteCurrencyCode() !== $currencyCode) {
                        $quoteModel->setQuoteCurrencyCode($currencyCode)->collectTotals()->save();
                    }
                }
            }
        }

        $params = array();
        if ($args) {
            $params = $args;
        }
        $data = array(
            'resource' => 'storeviews',
            'resourceid' => ($args && isset($args['storeId']))?$args['storeId']:'default',
            'is_method' => 1,
            'params' => $params,
        );
        $storeApi->setData($data);
        $storeApi->setSingularKey('storeviews');
        $storeApi->setBuilderQuery();
        return array(
            'store_id' => (int)$storeManager->getStore()->getId(),
            'currency' => $storeManager->getStore()->getCurrentCurrencyCode(),
            'root_category_id' => (int)$storeManager->getStore()->getRootCategoryId(),
            'pwa_studio_client_ver_number' => $this->simiObjectManager
                ->get('\Magento\Framework\App\Config\ScopeConfigInterface')
                ->getValue('simiconnector/general/pwa_studio_client_ver_number'),
            'config_json' => json_encode($storeApi->show()),
        );
    }
}
