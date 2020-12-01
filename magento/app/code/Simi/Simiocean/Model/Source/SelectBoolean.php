<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Simi\Simiocean\Model\Source;

/**
 * @api
 * @since 100.0.2
 */
class SelectBoolean extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
{
    /**
     * Option values
     */
    const VALUE_YES = 1;

    const VALUE_NO = 0;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory
     */
    // protected $_eavAttrEntity;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttrEntity
     * @codeCoverageIgnore
     */
    // public function __construct(
    //     \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttrEntity
    // ) {
    //     $this->_eavAttrEntity = $eavAttrEntity;
    // }

    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => 'Please select', 'value' => ''],
                ['label' => __('Yes'), 'value' => self::VALUE_YES],
                ['label' => __('No'), 'value' => self::VALUE_NO],
            ];
        }
        return $this->_options;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = [];
        foreach ($this->getAllOptions() as $option) {
            if ($option['value']) {
                $_options[$option['value']] = $option['label'];
            }
        }
        return $_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|int $value
     * @return string|false
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

    /**
     * Get a text for index option value
     *
     * @param  string|int $value
     * @return string|bool
     */
    public function getIndexOptionText($value)
    {
        switch ($value) {
            case self::VALUE_YES:
                return 'Yes';
            case self::VALUE_NO:
                return 'No';
            default:
                return '';
        }

        return parent::getIndexOptionText($value);
    }
}
