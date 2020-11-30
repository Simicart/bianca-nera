<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Simi\Simiconnector\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Simi\Simiconnector\Model\Resolver\Products\Query\Filter;
use Simi\Simiconnector\Model\Resolver\Products\Query\Search;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\SearchFilter;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchCriteria\Helper\Filter as FilterHelper;

use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Simiproducts field resolver, used for GraphQL request processing.
 */
class Simiproducts implements ResolverInterface
{
    public $result; //simiconnector
    public $productExtraData; //simiconnector

    /**
     * @var Builder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Search
     */
    private $searchQuery;

    /**
     * @var Filter
     */
    private $filterQuery;

    /**
     * @var SearchFilter
     */
    private $searchFilter;

    /**
     * @var FilterHelper
     */
    private $filterHelper;

    const XML_PATH_STOCK_THRESHOLD_QTY = 'cataloginventory/options/stock_threshold_qty';

    private $getStockItemConfiguration;
    private $productSalableQty;
    private $stockResolver;
    private $storeManager;

    /**
     * @param Builder $searchCriteriaBuilder
     * @param Search $searchQuery
     * @param Filter $filterQuery
     * @param SearchFilter $searchFilter
     */
    public function __construct(
        Builder $searchCriteriaBuilder,
        Search $searchQuery,
        Filter $filterQuery,
        SearchFilter $searchFilter,
        FilterHelper $filterHelper,
        \Magento\Framework\ObjectManagerInterface $simiObjectManager, //simiconnector changing,
        GetProductSalableQtyInterface $productSalableQty,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        StockResolverInterface $stockResolver,
        StoreManagerInterface $storeManager
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchQuery = $searchQuery;
        $this->filterQuery = $filterQuery;
        $this->searchFilter = $searchFilter;
        $this->filterHelper = $filterHelper;
        $this->simiObjectManager = $simiObjectManager; //simiconnector changing
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->productSalableQty = $productSalableQty;
        $this->stockResolver = $stockResolver;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $searchCriteria = $this->searchCriteriaBuilder->build($field->getName(), $args);

        $searchCriteria->setCurrentPage($args['currentPage']);
        $searchCriteria->setPageSize($args['pageSize']);

        if (!isset($args['search']) && !isset($args['filter'])) {
            throw new GraphQlInputException(
                __("'search' or 'filter' input argument is required.")
            );
        } elseif (isset($args['search'])) {
            $layerType = Resolver::CATALOG_LAYER_SEARCH;
            $this->searchFilter->add($args['search'], $searchCriteria);
            $searchResult = $this->searchQuery->getResult($args, $searchCriteria, $info);
        } else {
            $layerType = Resolver::CATALOG_LAYER_CATEGORY;
            $searchResult = $this->filterQuery->getResult($args, $searchCriteria, $info);
        }

        //possible division by 0
        if ($searchCriteria->getPageSize()) {
            $maxPages = ceil($searchResult->getTotalCount() / $searchCriteria->getPageSize());
        } else {
            $maxPages = 0;
        }

        $currentPage = $searchCriteria->getCurrentPage();
        if ($searchCriteria->getCurrentPage() > $maxPages && $searchResult->getTotalCount() > 0) {
            throw new GraphQlInputException(
                __(
                    'currentPage value %1 specified is greater than the %2 page(s) available.',
                    [$currentPage, $maxPages]
                )
            );
        }

        $registry = $this->simiObjectManager->get('\Magento\Framework\Registry');
        $simiProductFilters = $registry->registry('simiProductFilters');

        //simiconnector changing
        $this->eventManager = $this->simiObjectManager->get('\Magento\Framework\Event\ManagerInterface');

        $products = $searchResult->getProductsSearchResult();
        // $stockModel = $this->simiObjectManager->get('Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');

        $websiteCode = $this->storeManager->getWebsite()->getCode();
        $stock = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
        $stockId = $stock->getStockId();
        
        foreach ($products as $index => $product) {
            $sku = $product['sku'];
            // $productModel = $this->simiObjectManager->get('Magento\Catalog\Model\Product')
            //     ->getCollection()
            //     ->addAttributeToFilter('sku', $sku)
            //     ->getFirstItem();
            $productModel = $product['model'];
            if ($productModel->getId()) {
                /* $productModel = $this->simiObjectManager->create('Magento\Catalog\Model\Product')
                     ->load($productModel->getId());
                $this->productExtraData = array(
                    'attribute_values' => $productModel->toArray(),
                    'app_reviews' => $this->simiObjectManager
                        ->get('\Simi\Simiconnector\Helper\Review')
                        ->getProductReviews($productModel->getId())
                ); */
                
                $isSalable = $productModel->getIsSalable();
                // if is configurable product then check with collect all children product
                if ($productModel->getTypeId() == 'configurable') {
                    $_children = $productModel->getTypeInstance()->getUsedProducts($productModel);
                    $salableQty = 0;
                    foreach ($_children as $child){
                        $salableQty += $this->productSalableQty->execute($child->getSku(), $stockId);
                        if ($salableQty > 0) break;
                    }
                } else {
                    $salableQty = $this->productSalableQty->execute($sku, $stockId);
                }
                if ($salableQty <= 0) {
                    $isSalable = false;
                }
                /* if ($productModel->getIsSalable()) {
                    try{
                        $salable_qty_by_sku = $stockModel->execute($sku);
                        if (isset($salable_qty_by_sku[0]['qty']) && $salable_qty_by_sku[0]['qty'] == 0) {
                            $isSalable = false;
                        }
                        if ($salableQty <= 0) {
                            $isSalable = false;
                        }
                    }catch(\Exception $e){}
                } */
                $this->productExtraData = array(
                    'attribute_values' => array(
                        'is_salable' => $isSalable ? 1 : 0,
                        'vendor_id' => $productModel->getVendorId(),
                        'pre_order' => $productModel->getPreOrder(),
                    ),
                    'app_reviews' => $this->simiObjectManager->get('\Simi\Simiconnector\Helper\Review')
                        ->getProductReviews($productModel->getId())
                );
                $this->eventManager->dispatch(
                    'simi_simiconnector_graphql_simi_product_list_item_after',
                    ['object' => $this, 'extraData' => $this->productExtraData, 'productModel' => $productModel]
                );
                $product['extraData'] = json_encode($this->productExtraData);
                $products[$index] = $product;
            }
        }
        $this->result = [
            'total_count' => $searchResult->getTotalCount(),
            'items' => $products,
            'page_info' => [
                'page_size' => $searchCriteria->getPageSize(),
                'current_page' => $currentPage,
                'total_pages' => $maxPages
            ],
            'layer_type' => $layerType,
            'simiProductListItemExtraField' => '',
            'simi_filters' => $simiProductFilters?json_decode($simiProductFilters):array()
        ];

        $this->eventManager->dispatch(
            'simi_simiconnector_graphql_simi_product_list_after',
            ['object' => $this, 'data' => $this->result]
        );

        return $this->result;
    }
}
