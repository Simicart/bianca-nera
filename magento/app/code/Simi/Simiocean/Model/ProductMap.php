<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model;

use Magento\Framework\Model\AbstractModel;
use Simi\Simiocean\Api\Data\ProductInterface;
use Simi\Simiocean\Helper\Config;

class ProductMap extends AbstractModel implements ProductInterface
{
    /** Object Simi\Simiocean\Helper\Config */
    protected $config;

    public function __construct(
        Config $config
    ){
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function setSku($value){
        $this->setData(self::SKU, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSku(){
        return $this->getData(self::SKU);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($value){
        $this->setData(self::NAME, $value);
        $this->setData(self::PRODUCT_EN_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getName(){
        return $this->getData(self::PRODUCT_EN_NAME);
    }
    
    /**
     * {@inheritdoc}
     */
    public function setBarCode($value){
        $this->setData(self::BAR_CODE, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getBarCode(){
        return $this->getData(self::BAR_CODE);
    }
    
    /**
     * {@inheritdoc}
     */
    public function setColor($value){
        $this->setData(self::COLOR, $value);
        $this->setData(self::COLOR_EN_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getColor(){
        return $this->getData(self::COLOR_ONLINE_NAME) ? : $this->getData(self::COLOR_EN_NAME);
    }
    
    /**
     * {@inheritdoc}
     */
    public function setSize($value){
        $this->setData(self::SIZE, $value);
        $this->setData(self::SIZE_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSize(){
        return $this->getData(self::SIZE_NAME);
    }
    
    /**
     * {@inheritdoc}
     */
    public function setBrand($value){
        $this->setData(self::BRAND, $value);
        $this->setData(self::BRAND_EN_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getBrand(){
        return $this->getData(self::BRAND_EN_NAME);
    }
    
    /**
     * {@inheritdoc}
     */
    public function setDescription($value){
        $this->setData(self::DESCRIPTION, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(){
        return $this->getData(self::DESCRIPTION);
    }
    
    /**
     * {@inheritdoc}
     */
    public function setQty($value){
        $this->setData(self::QTY, $value);
        $this->setData(self::STOCK_QUANTITY, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getQty(){
        return $this->getData(self::STOCK_QUANTITY);
    }
    
    /**
     * {@inheritdoc}
     */
    public function setPrice($value){
        $this->setData(self::PRICE, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getPrice(){
        return $this->getData(self::PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setColorID($value){
        $this->setData(self::COLOR_ID, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getColorID(){
        return $this->getData(self::COLOR_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setColorArName($value){
        $this->setData(self::COLOR_AR_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getColorArName(){
        return $this->getData(self::COLOR_AR_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setColorEnName($value){
        $this->setData(self::COLOR_EN_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getColorEnName(){
        return $this->getData(self::COLOR_EN_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setSizeID($value){
        $this->setData(self::SIZE_ID, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSizeID(){
        return $this->getData(self::SIZE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setSizeName($value){
        $this->setData(self::SIZE_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSizeName(){
        return $this->getData(self::SIZE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setColorOnlineName($value){
        $this->setData(self::COLOR_ONLINE_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getColorOnlineName(){
        return $this->getData(self::COLOR_ONLINE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setModelNo($value){
        $this->setData(self::MODEL_NO, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getModelNo(){
        return $this->getData(self::MODEL_NO);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductArName($value){
        $this->setData(self::PRODUCT_AR_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getProductArName(){
        return $this->getData(self::PRODUCT_AR_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductEnName($value){
        $this->setData(self::PRODUCT_EN_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getProductEnName(){
        return $this->getData(self::PRODUCT_EN_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCategoryId($value){
        $this->setData(self::CATEGORY_ID, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCategoryId(){
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCategoryArName($value){
        $this->setData(self::CATEGORY_AR_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCategoryArName(){
        return $this->getData(self::CATEGORY_AR_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCategoryEnName($value){
        $this->setData(self::CATEGORY_EN_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCategoryEnName(){
        return $this->getData(self::CATEGORY_EN_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setSubcategoryId($value){
        $this->setData(self::SUBCATEGORY_ID, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSubcategoryId(){
        return $this->getData(self::SUBCATEGORY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setSubcategoryArName($value){
        $this->setData(self::SUBCATEGORY_AR_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSubcategoryArName(){
        return $this->getData(self::SUBCATEGORY_AR_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setSubcategoryEnName($value){
        $this->setData(self::SUBCATEGORY_EN_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSubcategoryEnName(){
        return $this->getData(self::SUBCATEGORY_EN_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setSeasonID($value){
        $this->setData(self::SEASON_ID, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSeasonID(){
        return $this->getData(self::SEASON_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setSeasonArName($value){
        $this->setData(self::SEASON_AR_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSeasonArName(){
        return $this->getData(self::SEASON_AR_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setSeasonEnName($value){
        $this->setData(self::SEASON_EN_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSeasonEnName(){
        return $this->getData(self::SEASON_EN_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setFabricID($value){
        $this->setData(self::FABRIC_ID, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getFabricID(){
        return $this->getData(self::FABRIC_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setFabricArName($value){
        $this->setData(self::FABRIC_AR_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getFabricArName(){
        return $this->getData(self::FABRIC_AR_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setFabricEnName($value){
        $this->setData(self::FABRIC_EN_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getFabricEnName(){
        return $this->getData(self::FABRIC_EN_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandID($value){
        $this->setData(self::BRAND_ID, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getBrandID(){
        return $this->getData(self::BRAND_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandArName($value){
        $this->setData(self::BRAND_AR_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getBrandArName(){
        return $this->getData(self::BRAND_AR_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandEnName($value){
        $this->setData(self::BRAND_EN_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getBrandEnName(){
        return $this->getData(self::BRAND_EN_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCountryID($value){
        $this->setData(self::COUNTRY_ID, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCountryID(){
        return $this->getData(self::COUNTRY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCountryArName($value){
        $this->setData(self::COUNTRY_AR_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCountryArName(){
        return $this->getData(self::COUNTRY_AR_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCountryEnName($value){
        $this->setData(self::COUNTRY_EN_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCountryEnName(){
        return $this->getData(self::COUNTRY_EN_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setPatternId($value){
        $this->setData(self::PATTERN_ID, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getPatternId(){
        return $this->getData(self::PATTERN_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setPatternArName($value){
        $this->setData(self::PATTERN_AR_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getPatternArName(){
        return $this->getData(self::PATTERN_AR_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setPatternEnName($value){
        $this->setData(self::PATTERN_EN_NAME, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getPatternEnName(){
        return $this->getData(self::PATTERN_EN_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setItemYear($value){
        $this->setData(self::ITEM_YEAR, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getItemYear(){
        return $this->getData(self::ITEM_YEAR);
    }

    /**
     * {@inheritdoc}
     */
    public function setStockQuantity($value){
        $this->setData(self::STOCK_QUANTITY, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getStockQuantity(){
        return $this->getData(self::STOCK_QUANTITY);
    }

    /**
     * {@inheritdoc}
     */
    public function setWholePrice($value){
        $this->setData(self::WHOLE_PRICE, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getWholePrice(){
        return $this->getData(self::WHOLE_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setSpecialPrice($value){
        $this->setData(self::SPECIAL_PRICE, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSpecialPrice(){
        return $this->getData(self::SPECIAL_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setSalePrice($value){
        $this->setData(self::SALE_PRICE, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSalePrice(){
        return $this->getData(self::SALE_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setClearancePrice($value){
        $this->setData(self::CLEARANCE_PRICE, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getClearancePrice(){
        return $this->getData(self::CLEARANCE_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setMinPrice($value){
        $this->setData(self::MIN_PRICE, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMinPrice(){
        return $this->getData(self::MIN_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastBuy($value){
        $this->setData(self::LAST_BUY, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getLastBuy(){
        return $this->getData(self::LAST_BUY);
    }

    /**
     * {@inheritdoc}
     */
    public function setLocalCost($value){
        $this->setData(self::LOCAL_COST, $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getLocalCost(){
        return $this->getData(self::LOCAL_COST);
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($value){
        $this->setData(self::CODE, $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(){
        return $this->getData(self::CODE);
    }
}