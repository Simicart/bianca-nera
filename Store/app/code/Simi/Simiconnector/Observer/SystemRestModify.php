<?php


namespace Simi\Simiconnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;

class SystemRestModify implements ObserverInterface
{
    private $simiObjectManager;
    public $simiItemQuote = false;
    protected $storeManager;
    protected $priceCurrency;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $simiObjectManager,
        PriceCurrencyInterface $priceCurrency,
        StoreManager $storeManager
    ) {
        $this->simiObjectManager = $simiObjectManager;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
    }


    public function execute(\Magento\Framework\Event\Observer $observer) {
       $obj = $observer->getObject();
       $routeData = $observer->getData('routeData');
       $requestContent = $observer->getData('requestContent');
       $request = $observer->getData('request');
       $contentArray = $obj->getContentArray();
       if ($routeData && isset($routeData['routePath'])){
	       if (
		       strpos($routeData['routePath'], 'V1/guest-carts/:cartId/payment-methods') !== false ||
		       strpos($routeData['routePath'], 'V1/carts/mine/payment-methods') !== false ||
		       strpos($routeData['routePath'], 'V1/guest-carts/:cartId/shipping-information') !== false ||
		       strpos($routeData['routePath'], 'V1/carts/mine/shipping-information') !== false
	       ) {
		       if ( isset($contentArray['payment_methods']) &&
		            (strpos($routeData['routePath'], 'V1/guest-carts/:cartId/shipping-information') !== false ||
		             strpos($routeData['routePath'], 'V1/carts/mine/shipping-information') !== false)){
			       $this->_addDataToPayment($contentArray['payment_methods'], $routeData);
		       }else{
			       $this->_addDataToPayment($contentArray, $routeData);
		       }
               if (isset($contentArray['totals']['items'])) {
                   $totalData = $contentArray['totals'];
                   $this->_addDataToQuoteItem($totalData, true);
                   $contentArray['totals'] = $totalData;
               }
	       } else if (
		       strpos($routeData['routePath'], 'V1/guest-carts/:cartId') !== false ||
		       strpos($routeData['routePath'], 'V1/carts/mine') !== false
	       ) {
                if (strpos($routeData['routePath'], 'V1/carts/mine/totals') !== false) {
    		       $this->_addDataToQuoteItem($contentArray, true);
                } else {
                   $this->_addDataToQuoteItem($contentArray, false);
                }
	       } else if (strpos($routeData['routePath'], 'integration/customer/token') !== false) {
               //_mergeCart must be run before _addCustomerIdentity because _addCustomerIdentity is changing contentArray
               $this->_mergeCart($contentArray, $requestContent, $request);
		       $this->_addCustomerIdentity($contentArray, $requestContent, $request);
	       }

           if ($this->_getQuote() && $this->_getQuote()->getId()) {
               if (
                    strpos($routeData['routePath'], '/totals') !== false
                ) {
                    $this->_addDataToTotal($contentArray);
                } else if (
                    strpos($routeData['routePath'], '/shipping-information') !== false &&
                    isset($contentArray['totals']['total_segments'])
                ) {
                    $total = $contentArray['totals'];
                    $this->_addDataToTotal($total);
                    $contentArray['totals'] = $total;
                }
            }
       }
       $obj->setContentArray($contentArray);
    }

    //modify payment api
    private function _addDataToPayment(&$contentArray, $routeData) {
        if (is_array($contentArray) && $routeData && isset($routeData['serviceClass'])) {
            $paymentHelper = $this->simiObjectManager->get('Simi\Simiconnector\Helper\Checkout\Payment');
            foreach ($paymentHelper->getMethods() as $method) {
                foreach ($contentArray as $index=>$restPayment) {
                    if ($method->getCode() == $restPayment['code']) {
                        $restPayment['simi_payment_data'] = $paymentHelper->getDetailsPayment($method);
                    }
                    $contentArray[$index] = $restPayment;
                }
            }
        }
    }

    public function _getQuote()
    {
        return $this->simiItemQuote;
    }

    private function _addDataToTotal(&$contentArray) {
        $depositDiscount = $this->_getQuote()->getPreorderDepositDiscount();
        $serviceSupportFee = $this->_getQuote()->getServiceSupportFee();
        if (isset($contentArray['total_segments']) && is_array($contentArray['total_segments'])) {
            $newTotalSegments = array();
            foreach ($contentArray['total_segments'] as $total_segment) {
                $newTotalSegments[] = $total_segment;
                if (isset($total_segment['code']) && $total_segment['code'] == 'subtotal') {
                    if ($depositDiscount) {
                        $newTotalSegments[] = array(
                            'code' => 'preorder_deposit_discount',
                            'title' => __('Pre-order Deposit Discount'),
                            'value' => (float)$depositDiscount,
                        );
                    }
                    if ($serviceSupportFee) {
                        $newTotalSegments[] = array(
                            'code' => 'service_support_fee',
                            'title' => __('Service Support'),
                            'value' => (float)$serviceSupportFee,
                        );
                    }
                }
            }
            $contentArray['total_segments'] = $newTotalSegments;
        }
    }


    //modify quote item
    private function _addDataToQuoteItem(&$contentArray, $isTotal) {
        // if ($isTotal)
        //     return;

        $store = $this->storeManager->getStore();
        $currency = $this->priceCurrency->getCurrency($store->getId());
        $baseCurrency = $store->getBaseCurrency();
        $currencyRate = $baseCurrency->getRate($currency);

        if (isset($contentArray['items']) && is_array($contentArray['items'])) {
            foreach ($contentArray['items'] as $index => $item) {
                $quoteItem = $this->simiObjectManager
                    ->get('Magento\Quote\Model\Quote\Item')->load($item['item_id']);

                if (!$this->_getQuote() && $quoteItem->getQuoteId() && $isTotal) {
                    $this->simiItemQuote = $this->simiObjectManager->create('Magento\Quote\Model\Quote')->load($quoteItem->getData('quote_id'));
                }

                if ($quoteItem->getId() && !$isTotal) {
                    $product = $this->simiObjectManager
                        ->create('Magento\Catalog\Model\Product')
                        ->load($quoteItem->getData('product_id'));

                    $item['simi_image']  = $this->simiObjectManager
                        ->create('Simi\Simiconnector\Helper\Products')
                        ->getImageProduct($product);
                    $item['simi_sku']  = $product->getData('sku');
                    $item['url_key']  = $product->getData('url_key');
                    $item['name']  = $product->getName();

                    $parentProducts = $this->simiObjectManager
                        ->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')
                        ->getParentIdsByChild($product->getId());
                    $imageProductModel = $product;
                    if($parentProducts && isset($parentProducts[0])){
                        $media_gallery = $imageProductModel->getMediaGallery();
                        $parentProductModel = $this->simiObjectManager->create('\Magento\Catalog\Model\Product')->load($parentProducts[0]);
                        if ($media_gallery && isset($media_gallery['images']) && is_array($media_gallery['images']) && !count($media_gallery['images'])) {
                            $imageProductModel = $parentProductModel;
                        }
                        $item['url_key'] = $parentProductModel->getData('url_key');
                    }
                    $item['image'] =  $this->simiObjectManager
                        ->create('Simi\Simiconnector\Helper\Products')
                        ->getImageProduct($imageProductModel);

                    $contentArray['items'][$index] = $item;


                    $product = $this->simiObjectManager
                        ->create('Magento\Catalog\Model\Product')
                        ->load($quoteItem->getData('product_id'));
                    if(!$isTotal)
                        $contentArray['items'][$index]['attribute_values'] = $product->toArray(array('type_id', 'vendor_id'));
                    $contentArray['items'][$index]['is_buy_service'] = $quoteItem->getData('is_buy_service');
                    //add giftcard info to quote item
                    if ($product->getTypeId() == 'aw_giftcard'){
                        $quoteItemCollection = $this->simiObjectManager->get('Magento\Quote\Model\Quote\Item\Option')->getCollection();
                        $quoteItemCollection->addFieldToFilter('item_id', $quoteItem->getId());
                        $requestData = [];
                        foreach ($quoteItemCollection as $option) {
                            if ($option->getCode() == 'aw_gc_value') {
                                try{
                                    $requestData[$option->getCode()] = round($option->getValue() * $currencyRate, 2); // convert price with rate
                                    // $requestData[$option->getCode()] = $option->getValue();
                                }catch(\Exception $e){}
                            } else {
                                $requestData[$option->getCode()] = $option->getValue();
                            }
                        }
                        $contentArray['items'][$index]['giftcard_values'] = $requestData;
                    }
                }
                //modify option values for special products (preorder, trytobuy)
                if ($isTotal && isset($item['options']) && is_string($item['options']) && $optionArray = json_decode($item['options'], true)) {
                    if (is_array($optionArray)) {
                        $systemProductOption = array();
                        $newOptions = false;
                        $extraFieldIndex = false;
                        foreach ($optionArray as $itemOption) {
                            if ($itemOption['label'] === \Simi\Simicustomize\Model\Api\Quoteitems::TRY_TO_BUY_OPTION_TITLE) {
                                $extraFieldIndex = 'simi_trytobuy_option';
                            } else if ($itemOption['label'] === \Simi\Simicustomize\Model\Api\Quoteitems::PRE_ORDER_OPTION_TITLE) {
                                $extraFieldIndex = 'simi_pre_order_option';
                            }
                            if ($extraFieldIndex) {
                                $systemProductOption = json_decode(base64_decode($itemOption['full_view']), true);
                                $newOptions = $systemProductOption;
                                foreach ($newOptions as $newOptionIndex => $newOption ) {
                                    $newOptions[$newOptionIndex] = array(
                                        'label' => $newOption['sku'],
                                        'value' => $newOption['quantity'],
                                        'full_view' => $newOption['name']
                                    );
                                    $productModel = $this->simiObjectManager->create(\Magento\Catalog\Model\Product::class);
                                    $productModel->load($productModel->getIdBySku($newOption['sku']));
                                    $systemProductOption[$newOptionIndex]['product_final_price'] = $productModel->getFinalPrice();
                                    $systemProductOption[$newOptionIndex]['name'] = $productModel->getName();
                                    $systemProductOption[$newOptionIndex]['url_key'] = $productModel->getData('url_key');
                                    $systemProductOption[$newOptionIndex]['vendor_id'] = $productModel->getData('vendor_id');

                                    //to get image + parent data
                                    $product = $this->simiObjectManager
                                        ->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')
                                        ->getParentIdsByChild($productModel->getId());
                                    $imageProductModel = $productModel;
                                    if($product && isset($product[0])){
                                        $media_gallery = $imageProductModel->getMediaGallery();
                                        $parentProductModel = $this->simiObjectManager->create(\Magento\Catalog\Model\Product::class)->load($product[0]);
                                        if ($media_gallery && isset($media_gallery['images']) && is_array($media_gallery['images']) && !count($media_gallery['images'])) {
                                            $imageProductModel = $parentProductModel;
                                        }
                                        $systemProductOption[$newOptionIndex]['name'] = $parentProductModel->getName();
                                        $systemProductOption[$newOptionIndex]['url_key'] = $parentProductModel->getData('url_key');
                                        $systemProductOption[$newOptionIndex]['vendor_id'] = $productModel->getData('vendor_id');
                                    }
                                    $systemProductOption[$newOptionIndex]['image'] =  $this->simiObjectManager
                                        ->create('Simi\Simiconnector\Helper\Products')
                                        ->getImageProduct($imageProductModel);

                                    if (isset($newOption['request']['super_attribute']) && is_array($newOption['request']['super_attribute'])) {
                                        $frontendOption = [];
                                        foreach ($newOption['request']['super_attribute'] as $attributeid=>$attribute) {
                                            $eavModel = $this->simiObjectManager->get('Magento\Catalog\Model\ResourceModel\Eav\Attribute')->load($attributeid);
                                            $frontendOption[] = array(
                                                'label'=>$eavModel->getFrontendLabel(),
                                                'value' => $eavModel->getFrontend()->getValue($productModel)
                                            );
                                        }

                                        $systemProductOption[$newOptionIndex]['frontend_option'] = $frontendOption;
                                    }
                                }
                                break;
                            }
                        }
                        $contentArray['items'][$index][$extraFieldIndex] = json_encode($systemProductOption);

                        if ($newOptions)
                            $contentArray['items'][$index]['options'] = json_encode($newOptions);
                    }
                }
            }
        }
    }

    //add SessionId + simiHash to login api of system rest
    private function _addCustomerIdentity(&$contentArray, $requestContent, $request) {
        if (is_string($contentArray) && $request->getParam('getSessionId') && $requestContent['username']) {
            $storeManager = $this->simiObjectManager->get('\Magento\Store\Model\StoreManagerInterface');
            $requestCustomer = $this->simiObjectManager->get('Magento\Customer\Model\Customer')
                ->setWebsiteId($storeManager->getStore()->getWebsiteId())
                ->loadByEmail($requestContent['username']);
            $tokenCustomerId = $this->simiObjectManager->create('Magento\Integration\Model\Oauth\Token')
                ->loadByToken($contentArray)->getData('customer_id');
            if ($requestCustomer && $requestCustomer->getId() == $tokenCustomerId) {
                $this->simiObjectManager
                    ->get('Magento\Customer\Model\Session')
                    ->setCustomerAsLoggedIn($requestCustomer);
                $hash = $this->simiObjectManager
                    ->get('Simi\Simiconnector\Helper\Customer')
                    ->getToken(array());
                $contentArray = array(
                    'customer_access_token' => $contentArray,
                    'customer_identity' => $this->simiObjectManager
                        ->get('Magento\Customer\Model\Session')
                        ->getSessionId(),
                    'simi_hash' => $hash,
                );
            }
        }
    }

    private function _mergeCart($contentArray, $requestContent, $request) {
        try {
            if (is_string($contentArray) && isset($requestContent['quote_id']) && $requestContent['username']) {
                $storeManager = $this->simiObjectManager->get('\Magento\Store\Model\StoreManagerInterface');
                $requestCustomer = $this->simiObjectManager->get('Magento\Customer\Model\Customer')
                                           ->setWebsiteId($storeManager->getStore()->getWebsiteId())
                                           ->loadByEmail($requestContent['username']);
                $guestQuoteId = $requestContent['quote_id'];
                $quoteIdMask  = $this->simiObjectManager->create('Magento\Quote\Model\QuoteIdMask')->load( $guestQuoteId, 'masked_id');
                $guestQuote   = $this->simiObjectManager->create('Magento\Quote\Api\CartRepositoryInterface')->get($quoteIdMask->getQuoteId());
                $quote        = $this->simiObjectManager->create('Magento\Quote\Model\Quote')->loadByCustomer($requestCustomer->getId());
                if (
                    !$this->simiObjectManager->get('Simi\Simicustomize\Helper\SpecialOrder')->isQuotePreOrder($quote) &&
                    !$this->simiObjectManager->get('Simi\Simicustomize\Helper\SpecialOrder')->isQuoteTryToBuy($quote)
                ) {
                    if ($quote->merge($guestQuote)) {
                        $this->simiObjectManager->get('Magento\Quote\Model\QuoteRepository\SaveHandler')->save( $quote );
                        $quote->collectTotals();
                    }
                }
            }
        }
        catch ( \Exception $e ) {
        }
    }
}
