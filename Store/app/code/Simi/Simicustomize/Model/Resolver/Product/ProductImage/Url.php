<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Simi\Simicustomize\Model\Resolver\Product\ProductImage;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\ImageFactory;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Image\Placeholder as PlaceholderProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Returns product's image url
 */
class Url implements ResolverInterface
{
    /**
     * @var ImageFactory
     */
    private $productImageFactory;
    /**
     * @var PlaceholderProvider
     */
    private $placeholderProvider;

    protected $storeManager;

    /**
     * Application Cache Manager
     *
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cacheManager;

    /**
     * @var string
     */
    private $cachePrefix = 'IMG_INFO';

    /**
     * @var string[]
     */
    private $placeholderCache = [];

    private $serializer;

    /**
     * @param ImageFactory $productImageFactory
     * @param PlaceholderProvider $placeholderProvider
     */
    public function __construct(
        ImageFactory $productImageFactory,
        PlaceholderProvider $placeholderProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\CacheInterface $cacheManager,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->productImageFactory = $productImageFactory;
        $this->placeholderProvider = $placeholderProvider;
        $this->storeManager = $storeManager;
        $this->cacheManager = $cacheManager;
        $this->serializer = $serializer;
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
        if (!isset($value['image_type'])) {
            throw new LocalizedException(__('"image_type" value should be specified'));
        }

        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Product $product */
        $product = $value['model'];
        if (isset($value['image_type'])) {
            if ($value['image_type'] == 'simi_small_image') {
                $value['image_type'] = 'small_image';
            }
            $imagePath = $product->getData($value['image_type']);
            return $this->getImageUrl($value['image_type'], $imagePath);
        } elseif (isset($value['file'])) {
            return $this->getImageUrl('image', $value['file']);
        }
        return [];
    }

    /**
     * Check is image cached
     *
     * @return bool
     */
    public function isCached($path)
    {
        return is_array($this->loadImageInfoFromCache($path)) && file_exists($path);
    }

    /**
     * Get image URL
     *
     * @param string $imageType
     * @param string|null $imagePath
     * @return string
     * @throws \Exception
     */
    private function getImageUrl(string $imageType, ?string $imagePath): string
    {
        if (empty($imagePath) && !empty($this->placeholderCache[$imageType])) {
            return $this->placeholderCache[$imageType];
        }

        $image = $this->productImageFactory->create();
        $image->setDestinationSubdir($imageType)
            // ->setWidth(768)
            // ->setHeight(961)
            ->setBaseFile($imagePath);

        try{
            if ($imagePath && !$image->isCached()) {
                $image->resize()->saveFile();
            }
        }catch(\Exception $e){
            // If Unsupported image format, no resize the image file.
            $image = $this->productImageFactory->create();
            $image->setDestinationSubdir($imageType)
                ->setBaseFile($imagePath);
        }

        if ($image->isBaseFilePlaceholder()) {
            $this->placeholderCache[$imageType] = $this->placeholderProvider->getPlaceholder($imageType);
            return $this->placeholderCache[$imageType];
        }

        return $image->getUrl();
    }

    protected function convertImageWebp($path) {
    	$position = strrpos($path, '/');
    	$name = substr($path, $position + 1);
    	$dir = substr($path, 0, $position) . '/';
    	$newWebpName = substr($name, 0, strrpos($name, '.')) . '.webp';
    	try {
    		$image = imagecreatefromstring(file_get_contents($path));
	    	imagepalettetotruecolor($image);
	    	imagealphablending($image, true);
	    	imagesavealpha($image, true);
	    	$imageWebp = imagewebp($image, $dir . $newWebpName, 80);
	    	return $imageWebp;
    	} catch (\Expection $e) {
    		return false;
    	}
    }

    /**
     * Save image data to cache
     *
     * @param array $imageInfo
     * @param string $imagePath
     * @return void
     */
    private function saveImageInfoToCache(array $imageInfo, string $imagePath)
    {
        $imagePath = $this->cachePrefix  . $imagePath;
        $this->cacheManager->save(
            $this->serializer->serialize($imageInfo),
            $imagePath,
            [$this->cachePrefix]
        );
    }

    /**
     * Load image data from cache
     *
     * @param string $imagePath
     * @return array|false
     */
    private function loadImageInfoFromCache(string $imagePath)
    {
        $imagePath = $this->cachePrefix  . $imagePath;
        $cacheData = $this->cacheManager->load($imagePath);
        if (!$cacheData) {
            return false;
        } else {
            return $this->serializer->unserialize($cacheData);
        }
    }

    
}
