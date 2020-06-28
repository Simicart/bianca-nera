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
                        if (
                            isset($option_data['products']) &&
                            is_array($option_data['products']) &&
                            count($option_data['products']) != 0
                        ) {
                            if($attribute_details['code'] === 'color') {
                                $option_data['option_value'] = $this->getValueSwatch($option_data['id']); // add color swatch image
                            }
                            $updatedOptions[] = $option_data;
                        }
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
            return;
        if(strpos($value[$id]['value'], '#') === false) {
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
        $store = $this->storeManager->getStore();
        $currentProduct = $product;

        $helper = $this->simiObjectManager
            ->get('Magento\ConfigurableProduct\Helper\Data');
        $options = $helper->getOptions($currentProduct, $this->getAllowProducts());
        
        $configurableAttributeData = $this->simiObjectManager
                ->get('Magento\ConfigurableProduct\Model\ConfigurableAttributeData');
        $attributesData = $configurableAttributeData->getAttributesData($currentProduct, $options);
        
        $localeFormat = $this->getLocaleFormat();
        $variationPrices = $this->simiObjectManager->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices');

        $config = [
            'attributes' => $attributesData['attributes'],
            'template' => str_replace('%s', '<%- data.price %>', $store->getCurrentCurrency()->getOutputFormat()),
            'currencyFormat' => $store->getCurrentCurrency()->getOutputFormat(),
            'optionPrices' => $this->getOptionPrices(),
            'priceFormat' => $localeFormat->getPriceFormat(),
            'prices' => $variationPrices->getFormattedPrices($product->getPriceInfo()),
            'productId' => $currentProduct->getId(),
            'chooseText' => __('Choose an Option...'),
            'images' => $this->getOptionImages(),
            'index' => isset($options['index']) ? $options['index'] : [],
        ];

        if ($currentProduct->hasPreconfiguredValues() && !empty($attributesData['defaultValues'])) {
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
        if (!$this->allowProducts) {
            $products = [];
            $catalogProductHelper = $this->simiObjectManager->get('\Magento\Catalog\Helper\Product');
            $skipSaleableCheck = $catalogProductHelper->getSkipSaleableCheck();
            $product = $this->getProduct();
            $allProducts = $product->getTypeInstance()->getUsedProducts($product, null);
            foreach ($allProducts as $product) {
                if ($product->isSaleable() || $skipSaleableCheck) {
                    $products[] = $product;
                }
            }
            $this->allowProducts = $products;
        }
        return $this->allowProducts;
    }

    /**
     * Get product images for configurable variations
     *
     * @return array
     * @since 100.1.10
     */
    protected function getOptionImages()
    {
        $images = [];
        $helper = $this->simiObjectManager->get('Magento\ConfigurableProduct\Helper\Data');
        $products = $this->getAllowProducts();
        foreach ($products as $product) {
            $productImages = $helper->getGalleryImages($product) ?: [];
            foreach ($productImages as $image) {
                $images[$product->getId()][] =
                    [
                        'thumb' => $image->getData('small_image_url'),
                        'img' => $image->getData('medium_image_url'),
                        'full' => $image->getData('large_image_url'),
                        'caption' => $image->getLabel(),
                        'position' => $image->getPosition(),
                        'isMain' => $image->getFile() == $product->getImage(),
                        'type' => str_replace('external-', '', $image->getMediaType()),
                        'videoUrl' => $image->getVideoUrl(),
                    ];
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