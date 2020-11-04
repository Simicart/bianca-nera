<?php

/**
 * Connector data helper
 */

namespace Simi\Simicustomize\Override\Helper\Options;

class Configurable extends \Simi\Simiconnector\Helper\Options\Configurable
{
    protected $allowProducts;
    protected $localeFormat;

    public function helper($helper)
    {
        return $this->simiObjectManager->get($helper);
    }

    public function getPrice($product, $price, $includingTax = null)
    {
        if (!($includingTax === null)) {
            $price = $this->catalogHelper->getTaxPrice($product, $price, true);
        } else {
            $price = $this->catalogHelper->getTaxPrice($product, $price);
        }
        return $price;
    }

    public function getOptions($product)
    {
        // $layout = $this->simiObjectManager->get('Magento\Framework\View\LayoutInterface');
        // $block  = $layout->createBlock('Magento\ConfigurableProduct\Block\Product\View\Type\Configurable');
        // $block->setProduct($product);
        $this->setProduct($product);
        $options                         = [];
        $configurable_options            = json_decode($this->getJsonConfig($product), 1);

        if (isset($configurable_options['attributes'])) {
            foreach ($configurable_options['attributes'] as $attribute_code => $attribute_details) {
                if (isset($attribute_details['options'])) {
                    $updatedOptions = array();
                    foreach ($attribute_details['options'] as $option_key => $option_data) {
                        // if (
                        //     isset($option_data['products']) &&
                        //     is_array($option_data['products']) &&
                        //     count($option_data['products']) != 0
                        // ) {
                            if($attribute_details['code'] === 'color') {
                                $option_data['option_value'] = $this->getValueSwatch($option_data['id']); // add color swatch image
                            }
                            $updatedOptions[] = $option_data;
                        // }
                    }
                    $attribute_details['options'] = $updatedOptions;
                    $configurable_options['attributes'][$attribute_code] = $attribute_details;
                }
            }
        }

        $options['configurable_options'] = $configurable_options;

        if (!($product->getOptions() === null) && $this->simiObjectManager
                ->get('Simi\Simiconnector\Helper\Data')->countArray($product->getOptions())) {
            $custom_options            = $this
                    ->helper('Simi\Simiconnector\Helper\Options\Simple')->getOptions($product);
            $options['custom_options'] = $custom_options['custom_options'];
        }
        return $options;
    }

    private function getValueSwatch($id) {
        $swatchHelper = $this->simiObjectManager->get('Magento\Swatches\Helper\Data');
        $value = $swatchHelper->getSwatchesByOptionsId([$id]);
        if (!isset($value[$id]['value']))
            return '';
        if(strpos($value[$id]['value'], '#') === FALSE) {
            $value[$id]['value'] = $this->simiObjectManager->get('Magento\Swatches\Helper\Media')->getSwatchMediaUrl().$value[$id]['value'];
        }
        return $value[$id]['value'];
    }

    /**
     * Composes configuration for js
     *
     * @return string
     */
    public function getJsonConfig($product)
    {
        // $store = $this->storeManager->getStore();
        $options = [];
        $allowAttributes = $product->getTypeInstance()->getConfigurableAttributes($product);
        $allowedProducts = $this->getAllowProducts();
        krsort($allowedProducts); // sort product for option products desc
        $allowedProductsOptions = $this->getAllowProductsForOptions($product);
        foreach ($allowAttributes as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            $productAttributeId = $productAttribute->getId();
            $productAttributeCode = $productAttribute->getAttributeCode();

            /* $optionIndex = []; // uncomment to get option label
            foreach($attribute->getOptions() as $option){
                if (isset($option['value_index'])){
                    $optionIndex[$option['value_index']] = $option;
                }
            } */

            $_options = [];
            foreach ($allowedProducts as $childProduct) {
                $productId = $childProduct->getId();
                $attributeValue = $childProduct->getData($productAttributeCode);
                $_options[$attributeValue]['id'] = $attributeValue;
                // $_options[$attributeValue]['label'] = isset($optionIndex[$attributeValue]['label']) ? $optionIndex[$attributeValue]['label'] : '';
                $_options[$attributeValue]['products'][] = $productId;
            }
            // Help to add option_value for color of product not salable
            foreach ($allowedProductsOptions as $childProduct) {
                $productId = $childProduct->getId();
                $attributeValue = $childProduct->getData($productAttributeCode);
                $_options[$attributeValue]['id'] = $attributeValue;
                if (!isset($_options[$attributeValue]['products'])) {
                    $_options[$attributeValue]['products'] = []; // to empty array to output data
                }
            }

            $options[$productAttributeId] = [
                'id' => $productAttributeId,
                'code' => $productAttributeCode,
                'options' => array_values($_options),
            ];

        }

        // $localeFormat = $this->getLocaleFormat();
        // $variationPrices = $this->simiObjectManager->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices');

        $config = [
            'attributes' => $options, //$attributesData['attributes'],
            // 'template' => str_replace('%s', '<%- data.price %>', $store->getCurrentCurrency()->getOutputFormat()),
            // 'currencyFormat' => $store->getCurrentCurrency()->getOutputFormat(),
            'optionPrices' => $this->getOptionPrices(),
            // 'priceFormat' => $localeFormat->getPriceFormat(),
            // 'prices' => $variationPrices->getFormattedPrices($product->getPriceInfo()),
            // 'productId' => $product->getId(),
            // 'chooseText' => __('Choose an Option...'),
            'images' => $this->getOptionImages(),
            // 'index' => isset($options['index']) ? $options['index'] : [],
        ];

        if ($product->hasPreconfiguredValues() && !empty($attributesData['defaultValues'])) {
            $config['defaultValues'] = $attributesData['defaultValues'];
        }

        $jsonEncoder = $this->simiObjectManager->get('Magento\Framework\Json\EncoderInterface');
        $jsonData = $jsonEncoder->encode($config);

        return $jsonData;
    }

    protected function setProduct($product){
        $this->product = $product;
    }

    protected function getProduct(){
        return $this->product;
    }

    public function getLocaleFormat(){
        if (!$this->localeFormat) {
            $this->localeFormat = $this->simiObjectManager->get('Magento\Framework\Locale\Format');
        }
        return $this->localeFormat;
    }

    /**
     * Get Allowed Products
     *
     * @return \Magento\Catalog\Model\Product[]
     */
    public function getAllowProducts()
    {
        $product = $this->getProduct();
        if ($product && $product->getId()) {
            if (!isset($this->allowProducts[$product->getId()])) {
                $products = [];
                $catalogProductHelper = $this->simiObjectManager->get('\Magento\Catalog\Helper\Product');
                $skipSaleableCheck = $catalogProductHelper->getSkipSaleableCheck();
                // $product = $this->getProduct();
                $allProducts = $product->getTypeInstance()->getUsedProducts($product, null);
                $stockState = $this->simiObjectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
                foreach ($allProducts as $childProduct) {
                    // Optimize speed
                    // Replace magento to check saleable for child product in a confugurable product
                    $stockQty = $stockState->getStockQty($childProduct->getId(), $childProduct->getStore()->getWebsiteId());
                    if ($stockQty <= 0) {
                        $childProduct->setData('is_salable', false);
                    }
                    // End
                    if ($childProduct->isSaleable() || $skipSaleableCheck) {
                        $products[] = $childProduct;
                    }
                }
                $this->allowProducts[$product->getId()] = $products;
            }
            return $this->allowProducts[$product->getId()];
        }
        return [];
    }

    public function getAllowProductsForOptions($product)
    {
        $products = [];
        $allProducts = $product->getTypeInstance()->getUsedProducts($product, null);
        foreach ($allProducts as $product) {
            $products[] = $product;
        }
        return $products;
    }

    /**
     * Optimized speed for get product images: not resize the original image
     * 
     * Get product images for configurable variations
     *
     * @return array
     * @since 100.1.10
     */
    protected function getOptionImages()
    {
        $images = [];
        // $helper = $this->simiObjectManager->get('Magento\ConfigurableProduct\Helper\Data');
        // $this->imageUrlBuilder = $this->simiObjectManager->get('Magento\Catalog\Model\Product\Image\UrlBuilder');
        $products = $this->getAllowProducts();
        $helperImage = $this->simiObjectManager->get('Magento\Catalog\Helper\Image');
        foreach ($products as $product) {
            
            // $helperImage->init($product, 'product_swatch_image_small');
            $helperImage->init($product, 'swatch_image');
            $swatchImage = $helperImage->getUrl();
            if (strpos($swatchImage, 'Magento_Catalog/images/product/placeholder') === FALSE) { // skip placeholder image
                $images[$product->getId()][] = [
                    'img' => $helperImage->getUrl(),
                    'full' => '',
                    'caption' => 'Swatch Image',
                    'position' => 0,
                    'isMain' => false,
                    'type' => 'swatch_image',
                    'videoUrl' => '',
                ];
            }

            $galleryImages = $product->getMediaGalleryImages();
            if ($galleryImages instanceof \Magento\Framework\Data\Collection) {
                /** @var $image Image */
                foreach ($galleryImages as $image) {
                    // $smallImageUrl = $this->imageUrlBuilder
                    //     ->getUrl($image->getFile(), 'product_page_image_small');

                    // $mediumImageUrl = $this->imageUrlBuilder
                    //     ->getUrl($image->getFile(), 'product_page_image_medium');

                    // $largeImageUrl = $this->imageUrlBuilder
                    //     ->getUrl($image->getFile(), 'product_page_image_large');

                    $images[$product->getId()][] =
                    [
                        // 'thumb' => $smallImageUrl,
                        // 'img' => $mediumImageUrl,
                        'full' => $image->getUrl(),//$largeImageUrl,
                        'caption' => $image->getLabel(),
                        'position' => $image->getPosition(),
                        'isMain' => $image->getFile() == $product->getImage(),
                        'type' => str_replace('external-', '', $image->getMediaType()),
                        'videoUrl' => $image->getVideoUrl(),
                    ];
                }
            }
        }
        return $images;
    }

    /**
     * Collect price options
     *
     * @return array
     */
    protected function getOptionPrices()
    {
        $prices = [];
        foreach ($this->getAllowProducts() as $product) {
            $tierPrices = [];
            $priceInfo = $product->getPriceInfo();
            $tierPriceModel =  $priceInfo->getPrice('tier_price');
            $tierPricesList = $tierPriceModel->getTierPriceList();
            foreach ($tierPricesList as $tierPrice) {
                $tierPrices[] = [
                    'qty' => $this->getLocaleFormat()->getNumber($tierPrice['price_qty']),
                    'price' => $this->getLocaleFormat()->getNumber($tierPrice['price']->getValue()),
                    'percentage' => $this->getLocaleFormat()->getNumber(
                        $tierPriceModel->getSavePercent($tierPrice['price'])
                    ),
                ];
            }

            $prices[$product->getId()] =
                [
                    'oldPrice' => [
                        'amount' => $this->getLocaleFormat()->getNumber(
                            $priceInfo->getPrice('regular_price')->getAmount()->getValue()
                        ),
                    ],
                    'basePrice' => [
                        'amount' => $this->getLocaleFormat()->getNumber(
                            $priceInfo->getPrice('final_price')->getAmount()->getBaseAmount()
                        ),
                    ],
                    'finalPrice' => [
                        'amount' => $this->getLocaleFormat()->getNumber(
                            $priceInfo->getPrice('final_price')->getAmount()->getValue()
                        ),
                    ],
                    'tierPrices' => $tierPrices,
                    'msrpPrice' => [
                        'amount' => $this->getLocaleFormat()->getNumber(
                            $product->getMsrp()
                        ),
                    ],
                 ];
        }
        return $prices;
    }
}
