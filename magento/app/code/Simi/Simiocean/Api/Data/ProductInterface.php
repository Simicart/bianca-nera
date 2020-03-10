<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface Customer
 * @api
 */
interface ProductInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const SKU = "SKU";
    const BAR_CODE = "BarCode";
    const COLOR_ID = "ColorID";
    const NAME = "name";
    const COLOR_AR_NAME = "ColorArName";
    const COLOR_EN_NAME = "ColorEnName";
    const SIZE = "size";
    const SIZE_ID = "SizeID";
    const SIZE_NAME = "SizeName";
    const COLOR_ONLINE_NAME = "ColorOnlineName";
    const MODEL_NO = "ModelNo";
    const PRODUCT_AR_NAME = "ProductArName";
    const PRODUCT_EN_NAME = "ProductEnName";
    const CATEGORY_ID = "CategoryId";
    const CATEGORY_AR_NAME = "CategoryArName";
    const CATEGORY_EN_NAME = "CategoryEnName";
    const SUBCATEGORY_ID = "SubcategoryId";
    const SUBCATEGORY_AR_NAME = "SubcategoryArName";
    const SUBCATEGORY_EN_NAME = "SubcategoryEnName";
    const SEASON_ID = "SeasonID";
    const SEASON_AR_NAME = "SeasonArName";
    const SEASON_EN_NAME = "SeasonEnName";
    const FABRIC_ID = "FabricID";
    const FABRIC_AR_NAME = "FabricArName";
    const FABRIC_EN_NAME = "FabricEnName";
    const BRAND_ID = "BrandID";
    const BRAND_AR_NAME = "BrandArName";
    const BRAND_EN_NAME = "BrandEnName";
    const COUNTRY_ID = "CountryID";
    const COUNTRY_AR_NAME = "CountryArName";
    const COUNTRY_EN_NAME = "CountryEnName";
    const PATTERN_ID = "PatternId";
    const PATTERN_AR_NAME = "PatternArName";
    const PATTERN_EN_NAME = "PatternEnName";
    const ITEM_YEAR = "ItemYear";
    const DESCRIPTION = "Description";
    const QTY = "qty";
    const STOCK_QUANTITY = "StockQuantity";
    const PRICE = "Price";
    const WHOLE_PRICE = "WholePrice";
    const SPECIAL_PRICE = "SpecialPrice";
    const SALE_PRICE = "SalePrice";
    const CLEARANCE_PRICE = "ClearancePrice";
    const MIN_PRICE = "MinPrice";
    const LAST_BUY = "LastBuy";
    const LOCAL_COST = "LocalCost";
    const CODE = "Code";
    /**#@-*/

    /**
     * Set sku
     *
     * @param string $value
     * @return $this
     */
    public function setSku($value);
    
    /**
     * Get sku
     *
     * @return string
     */
    public function getSku();

    /**
     * Set name ProductEnName | ProductArName
     *
     * @param string $value
     * @return $this
     */
    public function setName($value);
    
    /**
     * Get name
     *
     * @return string
     */
    public function getName();
    
    /**
     * Set bar code
     *
     * @param string $value
     * @return $this
     */
    public function setBarCode($value);
    
    /**
     * Get bar code
     *
     * @return string
     */
    public function getBarCode();
    
    /**
     * Set color
     *
     * @param string $value ColorID | ColorArName | ColorEnName | ColorOnlineName
     * @return $this
     */
    public function setColor($value);
    
    /**
     * Get color ColorID | ColorArName | ColorEnName | ColorOnlineName
     *
     * @return string
     */
    public function getColor();
    
    /**
     * Set size
     *
     * @param string $value
     * @return $this
     */
    public function setSize($value);
    
    /**
     * Get size (SizeName)
     *
     * @return string
     */
    public function getSize();
    
    /**
     * Set brand id (BrandID | BrandArName | BrandEnName)
     *
     * @param string $value
     * @return $this
     */
    public function setBrand($value);
    
    /**
     * Get brand id
     *
     * @return string
     */
    public function getBrand();
    
    /**
     * Set description
     *
     * @param string $value
     * @return $this
     */
    public function setDescription($value);
    
    /**
     * Get description
     *
     * @return string
     */
    public function getDescription();
    
    /**
     * Set stock quantity
     *
     * @param string $value
     * @return $this
     */
    public function setQty($value);
    
    /**
     * Get stock quantity
     *
     * @return string
     */
    public function getQty();
    
    /**
     * Set price
     *
     * @param string $value
     * @return $this
     */
    public function setPrice($value);
    
    /**
     * Get price
     *
     * @return string
     */
    public function getPrice();

    /**
     * Set ColorID
     *
     * @param string $value
     * @return $this
     */
    public function setColorID($value);
    
    /**
     * Get ColorID
     *
     * @return string
     */
    public function getColorID();

    /**
     * Set ColorArName
     *
     * @param string $value
     * @return $this
     */
    public function setColorArName($value);
    
    /**
     * Get ColorArName
     *
     * @return string
     */
    public function getColorArName();

    /**
     * Set ColorEnName
     *
     * @param string $value
     * @return $this
     */
    public function setColorEnName($value);
    
    /**
     * Get ColorEnName
     *
     * @return string
     */
    public function getColorEnName();

    /**
     * Set SizeID
     *
     * @param string $value
     * @return $this
     */
    public function setSizeID($value);
    
    /**
     * Get SizeID
     *
     * @return string
     */
    public function getSizeID();

    /**
     * Set SizeName
     *
     * @param string $value
     * @return $this
     */
    public function setSizeName($value);
    
    /**
     * Get SizeName
     *
     * @return string
     */
    public function getSizeName();

    /**
     * Set ColorOnlineName
     *
     * @param string $value
     * @return $this
     */
    public function setColorOnlineName($value);
    
    /**
     * Get ColorOnlineName
     *
     * @return string
     */
    public function getColorOnlineName();

    /**
     * Set ModelNo
     *
     * @param string $value
     * @return $this
     */
    public function setModelNo($value);
    
    /**
     * Get ModelNo
     *
     * @return string
     */
    public function getModelNo();

    /**
     * Set ProductArName
     *
     * @param string $value
     * @return $this
     */
    public function setProductArName($value);
    
    /**
     * Get ProductArName
     *
     * @return string
     */
    public function getProductArName();

    /**
     * Set ProductEnName
     *
     * @param string $value
     * @return $this
     */
    public function setProductEnName($value);
    
    /**
     * Get ProductEnName
     *
     * @return string
     */
    public function getProductEnName();

    /**
     * Set CategoryId
     *
     * @param string $value
     * @return $this
     */
    public function setCategoryId($value);
    
    /**
     * Get CategoryId
     *
     * @return string
     */
    public function getCategoryId();

    /**
     * Set CategoryArName
     *
     * @param string $value
     * @return $this
     */
    public function setCategoryArName($value);
    
    /**
     * Get CategoryArName
     *
     * @return string
     */
    public function getCategoryArName();

    /**
     * Set CategoryEnName
     *
     * @param string $value
     * @return $this
     */
    public function setCategoryEnName($value);
    
    /**
     * Get CategoryEnName
     *
     * @return string
     */
    public function getCategoryEnName();

    /**
     * Set SubcategoryId
     *
     * @param string $value
     * @return $this
     */
    public function setSubcategoryId($value);
    
    /**
     * Get SubcategoryId
     *
     * @return string
     */
    public function getSubcategoryId();

    /**
     * Set SubcategoryArName
     *
     * @param string $value
     * @return $this
     */
    public function setSubcategoryArName($value);
    
    /**
     * Get SubcategoryArName
     *
     * @return string
     */
    public function getSubcategoryArName();

    /**
     * Set SubcategoryEnName
     *
     * @param string $value
     * @return $this
     */
    public function setSubcategoryEnName($value);
    
    /**
     * Get SubcategoryEnName
     *
     * @return string
     */
    public function getSubcategoryEnName();

    /**
     * Set SeasonID
     *
     * @param string $value
     * @return $this
     */
    public function setSeasonID($value);
    
    /**
     * Get SeasonID
     *
     * @return string
     */
    public function getSeasonID();

    /**
     * Set SeasonArName
     *
     * @param string $value
     * @return $this
     */
    public function setSeasonArName($value);
    
    /**
     * Get SeasonArName
     *
     * @return string
     */
    public function getSeasonArName();

    /**
     * Set SeasonEnName
     *
     * @param string $value
     * @return $this
     */
    public function setSeasonEnName($value);
    
    /**
     * Get SeasonEnName
     *
     * @return string
     */
    public function getSeasonEnName();

    /**
     * Set FabricID
     *
     * @param string $value
     * @return $this
     */
    public function setFabricID($value);
    
    /**
     * Get FabricID
     *
     * @return string
     */
    public function getFabricID();

    /**
     * Set FabricArName
     *
     * @param string $value
     * @return $this
     */
    public function setFabricArName($value);
    
    /**
     * Get FabricArName
     *
     * @return string
     */
    public function getFabricArName();

    /**
     * Set FabricEnName
     *
     * @param string $value
     * @return $this
     */
    public function setFabricEnName($value);
    
    /**
     * Get FabricEnName
     *
     * @return string
     */
    public function getFabricEnName();

    /**
     * Set BrandID
     *
     * @param string $value
     * @return $this
     */
    public function setBrandID($value);
    
    /**
     * Get BrandID
     *
     * @return string
     */
    public function getBrandID();

    /**
     * Set BrandArName
     *
     * @param string $value
     * @return $this
     */
    public function setBrandArName($value);
    
    /**
     * Get BrandArName
     *
     * @return string
     */
    public function getBrandArName();

    /**
     * Set BrandEnName
     *
     * @param string $value
     * @return $this
     */
    public function setBrandEnName($value);
    
    /**
     * Get BrandEnName
     *
     * @return string
     */
    public function getBrandEnName();

    /**
     * Set CountryID
     *
     * @param string $value
     * @return $this
     */
    public function setCountryID($value);
    
    /**
     * Get CountryID
     *
     * @return string
     */
    public function getCountryID();

    /**
     * Set CountryArName
     *
     * @param string $value
     * @return $this
     */
    public function setCountryArName($value);
    
    /**
     * Get CountryArName
     *
     * @return string
     */
    public function getCountryArName();

    /**
     * Set CountryEnName
     *
     * @param string $value
     * @return $this
     */
    public function setCountryEnName($value);
    
    /**
     * Get CountryEnName
     *
     * @return string
     */
    public function getCountryEnName();

    /**
     * Set PatternId
     *
     * @param string $value
     * @return $this
     */
    public function setPatternId($value);
    
    /**
     * Get PatternId
     *
     * @return string
     */
    public function getPatternId();

    /**
     * Set PatternArName
     *
     * @param string $value
     * @return $this
     */
    public function setPatternArName($value);
    
    /**
     * Get PatternArName
     *
     * @return string
     */
    public function getPatternArName();

    /**
     * Set PatternEnName
     *
     * @param string $value
     * @return $this
     */
    public function setPatternEnName($value);
    
    /**
     * Get PatternEnName
     *
     * @return string
     */
    public function getPatternEnName();

    /**
     * Set ItemYear
     *
     * @param string $value
     * @return $this
     */
    public function setItemYear($value);
    
    /**
     * Get ItemYear
     *
     * @return string
     */
    public function getItemYear();

    /**
     * Set StockQuantity
     *
     * @param string $value
     * @return $this
     */
    public function setStockQuantity($value);
    
    /**
     * Get StockQuantity
     *
     * @return string
     */
    public function getStockQuantity();

    /**
     * Set WholePrice
     *
     * @param string $value
     * @return $this
     */
    public function setWholePrice($value);
    
    /**
     * Get WholePrice
     *
     * @return string
     */
    public function getWholePrice();

    /**
     * Set SpecialPrice
     *
     * @param string $value
     * @return $this
     */
    public function setSpecialPrice($value);
    
    /**
     * Get SpecialPrice
     *
     * @return string
     */
    public function getSpecialPrice();

    /**
     * Set SalePrice
     *
     * @param string $value
     * @return $this
     */
    public function setSalePrice($value);
    
    /**
     * Get SalePrice
     *
     * @return string
     */
    public function getSalePrice();

    /**
     * Set ClearancePrice
     *
     * @param string $value
     * @return $this
     */
    public function setClearancePrice($value);
    
    /**
     * Get ClearancePrice
     *
     * @return string
     */
    public function getClearancePrice();

    /**
     * Set MinPrice
     *
     * @param string $value
     * @return $this
     */
    public function setMinPrice($value);
    
    /**
     * Get MinPrice
     *
     * @return string
     */
    public function getMinPrice();

    /**
     * Set LastBuy
     *
     * @param string $value
     * @return $this
     */
    public function setLastBuy($value);
    
    /**
     * Get LastBuy
     *
     * @return string
     */
    public function getLastBuy();

    /**
     * Set LocalCost
     *
     * @param string $value
     * @return $this
     */
    public function setLocalCost($value);
    
    /**
     * Get LocalCost
     *
     * @return string
     */
    public function getLocalCost();

    /**
     * Set Code
     *
     * @param string $value
     * @return $this
     */
    public function setCode($value);

    /**
     * Get Code
     *
     * @return string
     */
    public function getCode();
}