<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Simi\Simiconnector\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;

/**
 * @inheritdoc
 */
class Simiproductdetailextrafieldresolver implements ResolverInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;
    public $extraFields;
    protected $storeManager;

    /**
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $simiObjectManager,
        MetadataPool $metadataPool,
        StoreManager $storeManager
    ) {
        $this->metadataPool = $metadataPool;
        $this->simiObjectManager = $simiObjectManager;
        $this->storeManager = $storeManager;
    }

    /**
     * Fetch and format configurable variants.
     *
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ){
        try {
            $productCollection = $this->simiObjectManager->get('Magento\Catalog\Model\Product')->getCollection()
                ->addAttributeToSelect('entity_id'); // Optimize speed
                // ->addAttributeToSelect('*');
            if ($args && isset($args['filter'])) {
                foreach ($args['filter'] as $key => $filter) {
                    $productCollection->addAttributeToFilter($key, $filter);
                }
            }
            $productModel = $productCollection->getFirstItem();
            if ($productId = $productModel->getId()) {
                // Optimize speed
                /* $registry = $this->simiObjectManager->get('\Magento\Framework\Registry');
                if (!$registry->registry('product') && $productModel->getId()) {
                    $registry->register('product', $productModel);
                    $registry->register('current_product', $productModel);
                } */
                // End
                $options = $this->simiObjectManager
                    ->get('\Simi\Simiconnector\Helper\Options')->getOptions($productModel);

                $app_reviews  = $this->simiObjectManager
                    ->get('\Simi\Simiconnector\Helper\Review')
                    ->getProductReviews($productModel->getId());

                // Optimize speed
                /* $layout      = $this->simiObjectManager->get('Magento\Framework\View\LayoutInterface');
                $block_att   = $layout->createBlock('Magento\Catalog\Block\Product\View\Attributes');
                $_additional = $block_att->getAdditionalData();

                $tierPrice   = $this->simiObjectManager
                    ->get('\Simi\Simiconnector\Helper\Price')->getProductTierPricesLabel($productModel); */
                // End
                $productModel->load($productId);

                // Convert giftcard currency reate
                $gcAmounts = $productModel->getAwGcAmounts();
                if ($gcAmounts) {
                    $store = $this->storeManager->getStore();
                    $currency = $store->getCurrentCurrency();
                    $baseCurrency = $store->getBaseCurrency();
                    $currencyRate = $baseCurrency->getRate($currency);
                    foreach($gcAmounts as &$gcAmountOption){
                        if (isset($gcAmountOption['price'])) {
                            $gcAmountOption['price'] = round((float) $gcAmountOption['price'] * (float) $currencyRate, 2);
                        }
                        if (isset($gcAmountOption['price'])) {
                            $gcAmountOption['current_percent'] = round((float) $gcAmountOption['percent'] * (float) $currencyRate, 2);
                        }
                    }
                }

                $this->extraFields = array(
                    // 'attribute_values' => $productModel->load($productId)->toArray(), // Optimize speed
                    'attribute_values' => [
                        'aw_gc_allow_open_amount' => $productModel->getAwGcAllowOpenAmount(),
                        'aw_gc_open_amount_max'  => $productModel->getData('aw_gc_open_amount_max'),
                        'aw_gc_open_amount_min' => $productModel->getData('aw_gc_open_amount_min'),
                        'aw_gc_amounts'  => $gcAmounts,
                        'pre_order'  => $productModel->getData('pre_order'),
                        'try_to_buy'  => $productModel->getData('try_to_buy'),
                        'reservable'  => $productModel->getData('reservable'),
                    ],
                    'app_options' => $options,
                    'app_reviews' => $app_reviews,
                    // 'additional'  => $_additional, // Optimize speed
                    // 'app_tier_prices' => $tierPrice, // Optimize speed
                    'is_salable' => $productModel->getIsSalable() ? 1 : 0, // Optimize speed
                );
                $this->eventManager = $this->simiObjectManager->get('\Magento\Framework\Event\ManagerInterface');
                $this->eventManager->dispatch(
                    'simi_simiconnector_graphql_product_detail_extra_field_after',
                    ['object' => $this, 'data' => $this->extraFields, 'product' => $productModel]
                );
                return json_encode($this->extraFields);
            }
        } catch (\Exception $e) {
            return '';
        }
        return '';
    }
}
