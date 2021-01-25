<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Simi\Simiconnector\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;

class CatalogPlaceHolderImage implements ResolverInterface
{
    protected $simiObjectManager;

    /**
     * @param Builder $searchCriteriaBuilder
     * @param Search $searchQuery
     * @param Filter $filterQuery
     * @param SearchFilter $searchFilter
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $simiObjectManager
    ) {
        $this->simiObjectManager = $simiObjectManager;
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
        $imageHelper = $this->simiObjectManager->get('Magento\Catalog\Helper\Image');
        $placeholderImageUrl = $imageHelper->getDefaultPlaceholderUrl('small_image');
        if (!$placeholderImageUrl) {
            $placeholderImageUrl = $imageHelper->getDefaultPlaceholderUrl('image');
        }
        return $placeholderImageUrl;
    }
}
