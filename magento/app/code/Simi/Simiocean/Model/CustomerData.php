<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model;

use Magento\Framework\Model\AbstractModel;
use Simi\Simiocean\Api\Data\CustomerInterface;
use Simi\Simiocean\Helper\Config;

class CustomerData extends AbstractModel implements CustomerInterface
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
    public function setFirstname($value){
        $this->setData(self::FIRST_NAME, $value);
        $this->setData('firstname', $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstname(){
        return $this->getData(self::FIRST_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastname($value){
        $this->setData(self::LAST_NAME, $value);
        $this->setData('lastname', $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastname(){
        return $this->getData(self::LAST_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getDob(){
        return $this->getData(self::BIRTH_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setDob($value){
        $this->setData(self::BIRTH_DATE, $value);
        $this->setData('dob', $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail(){
        return $this->getData(self::EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($value){
        $this->setData(self::EMAIL, $value);
        $this->setData('email', $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrand(){
        return $this->getData(self::BRAND);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrand($value){
        $this->setData(self::BRAND, $value);
        $this->setData('brand', $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getPhone(){
        return $this->getData(self::MOBILE_PHONE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPhone($value){
        $this->setData(self::MOBILE_PHONE, $value);
        $this->setData('phone', $value);
        return $this;
    }
}