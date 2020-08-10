<?php

namespace Simi\Simicustomize\Helper;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteFactory;

class SpecialOrder extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $storeManager;
    public $scopeConfig;
    public $simiObjectManager;
    public $inputParamsResolver;
    public $foundQuoteId;
    protected $checkoutSession;
    protected $_quote;
    protected $quoteIdMask;
    protected $quoteFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $simiObjectManager,
        \Magento\Webapi\Controller\Rest\InputParamsResolver $inputParamsResolver,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CheckoutSession $checkoutSession,
        QuoteIdMask $quoteIdMask,
        QuoteFactory $quoteFactory
    ) {
        $this->simiObjectManager = $simiObjectManager;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->inputParamsResolver = $inputParamsResolver;
        $this->checkoutSession = $checkoutSession;
        $this->quoteIdMask = $quoteIdMask;
        $this->quoteFactory = $quoteFactory;
        parent::__construct($context);
    }

    public function submitQuotFromRestToSession($quoteId = null) {
        $appState = $this->simiObjectManager->get('\Magento\Framework\App\State');
        if($appState->getAreaCode() == 'adminhtml') return;//not allowed admin area
        if($appState->getAreaCode() == 'frontend') return;//not allowed frontend not rest area

        $inputParams = $this->inputParamsResolver->resolve();
        if ($this->foundQuoteId)
            return;
        if (!$quoteId && $inputParams && is_array($inputParams) && isset($inputParams[0])) {
            $quoteId = $inputParams[0];
            $quoteIdMask = $this->simiObjectManager->get('Magento\Quote\Model\QuoteIdMask');
            if ($quoteIdMask->load($quoteId, 'masked_id')) {
                if ($quoteIdMask && $maskQuoteId = $quoteIdMask->getData('quote_id'))
                    $quoteId = $maskQuoteId;
            }
        }
        if ($quoteId) {
            $quoteModel = $this->simiObjectManager->get('Magento\Quote\Model\Quote')->load($quoteId);
            if ($quoteModel->getId() && $quoteModel->getData('is_active')) {
                $this->foundQuoteId = $quoteModel->getId();
                $this->simiObjectManager->get('Simi\Simiconnector\Helper\Data')->setQuoteToSession($quoteModel);
            }
        }
    }

    /* public function _getCart()
    {
        return $this->simiObjectManager->get('Magento\Checkout\Model\Cart');
    } */

    public function _getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->checkoutSession->getQuote();
        }
        if (!$this->_quote) {
            $inputParams = $this->inputParamsResolver->resolve();
            $quoteId = '';
            if ($inputParams && is_array($inputParams) && isset($inputParams[0]) && $inputParams[0]) {
                $quoteId = $inputParams[0];
                if ($this->quoteIdMask && $this->quoteIdMask->load($quoteId, 'masked_id')) {
                    if ($maskQuoteId = $this->quoteIdMask->getData('quote_id')){
                        $quoteId = $maskQuoteId;
                    }
                }
            }
            if ($quoteId) {
                $this->_quote = $this->quoteFactory->create()->load($quoteId);
                if ($this->_quote->getId() && $this->_quote->getData('is_active')) {
                    $this->checkoutSession->setQuoteId($this->_quote->getId());
                    $this->checkoutSession->replaceQuote($this->_quote);
                }
            }
        }
        return $this->_quote;
    }

    public function getPreOrderProductsFromOrder($orderModel) {
        $depositProductId = $this->scopeConfig->getValue('sales/preorder/deposit_product_id');
        $preOrderProducts = false;
        $orderData = $orderModel->toArray();
        $orderApiModel = $this->simiObjectManager->get('Simi\Simiconnector\Model\Api\Orders');
        $orderData['order_items']     = $orderApiModel->_getProductFromOrderHistoryDetail($orderModel);
        foreach ($orderData['order_items'] as $order_item) {
            if (
                $order_item['product_id'] == $depositProductId &&
                isset($order_item['product_options']['options']) && is_array($order_item['product_options']['options'])
            ) {
                foreach ($order_item['product_options']['options'] as $product_option) {
                    if (isset($product_option['label']) && $product_option['label'] == \Simi\Simicustomize\Model\Api\Quoteitems::PRE_ORDER_OPTION_TITLE) {
                        $preOrderProducts = json_decode(base64_decode($product_option['option_value']), true);
                        break;
                    }
                }
                break;
            }
        }
        return $preOrderProducts;
    }

    public function isQuotePreOrder($quote = null)
    {
        if (!$quote) {
            $quote = $this->_getQuote();
        }
        $depositProductId = $this->scopeConfig->getValue('sales/preorder/deposit_product_id');
        $quoteItems = $quote->getItemsCollection();
        foreach($quoteItems as $quoteItem) {
            if ($quoteItem && $quoteItem->getProduct() && $quoteItem->getProduct()->getId() == $depositProductId) {
                return true;
            }
        }
        return false;
    }

    public function isQuoteTryToBuy($quote = null)
    {
        if (!$quote) {
            $quote = $this->_getQuote();
        }
        $tryToBuyProductId = $this->scopeConfig->getValue('sales/trytobuy/trytobuy_product_id');
        $quoteItems = $quote->getItemsCollection();
        foreach($quoteItems as $quoteItem) {
            if ($quoteItem && $quoteItem->getProduct() && $quoteItem->getProduct()->getId() == $tryToBuyProductId) {
                return true;
            }
        }
        return false;
    }
}
