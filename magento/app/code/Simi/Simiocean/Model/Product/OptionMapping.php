<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model\Product;

use Magento\Eav\Model\Config as EavConfig;
use Magento\framework\Api\ObjectFactory;

class OptionMapping
{
    /**
     * @var Simi\Simiocean\Helper\Data
     */
    protected $helper;

    /**
     * @var Simi\Simiocean\Helper\Config
     */
    protected $config;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var Magento\Eav\Model\Entity\Attribute\OptionFactory
     */
    protected $attributeOptionFactory;

    /**
     * @var \Magento\Eav\Api\AttributeOptionManagementInterface
     */
    protected $attributeOptionManagement;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $resourceModel;

    /**
     * @var \Magento\Swatches\Helper\Data
     */
    protected $swatchHelper;

    /**
    * @param \Simi\Simiocean\Helper\Data $helper,
    * @param \Simi\Simiocean\Helper\Config $config,
    * @param \Magento\Eav\Model\Entity\Attribute\OptionFactory $attributeOptionFactory,
    * @param \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement,
    * @param EavConfig $eavConfig,
    * @param ObjectFactory $objectFactory
     */
    public function __construct(
        \Simi\Simiocean\Helper\Data $helper,
        \Simi\Simiocean\Helper\Config $config,
        \Magento\Eav\Model\Entity\Attribute\OptionFactory $attributeOptionFactory,
        \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $resourceModel,
        \Magento\Swatches\Helper\Data $swatchHelper,
        EavConfig $eavConfig,
        ObjectFactory $objectFactory
    ){
        $this->helper = $helper;
        $this->config = $config;
        $this->eavConfig = $eavConfig;
        $this->attributeOptionFactory = $attributeOptionFactory;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->resourceModel = $resourceModel;
        $this->swatchHelper = $swatchHelper;
        $this->objectFactory = $objectFactory;
    }

    /**
     * Get attribute by attribute code
     * @param $attrCode
     * @return Magento\Catalog\Model\ResourceModel\Eav\Attribute|null
     */
    public function getAttribute($attrCode){
        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attrCode);
        return $attribute;
    }

    /**
     * Add new option to attribute options
     * @param array $option keys ['value', 'label', 'label_ar']
     * @return int|null
     */
    public function addAttributeOption($attribute, $option){
        if (is_string($attribute)) {
            $attribute = $this->getAttribute($attribute);
        }
        if (!is_array($option)) $option = array('label' => $option); 
        if ($attribute) {
            $options = $attribute->getOptions();
            /* $storeLabels \Magento\Eav\Api\Data\AttributeOptionLabelInterface[] */
            $newOption = $this->attributeOptionFactory->create();
            $newOption->setLabel($option['label']);
            $newOption->setAttributeId($attribute->getId());
            $newOption->setSortOrder(0);
            $newOption->setIsDefault(false);
            $storeLabels = [];
            // add store label for default store
            $optionLabel = $this->objectFactory->create(\Magento\Eav\Api\Data\AttributeOptionLabelInterface::class, []);
            $optionLabel->setStoreId(0);
            $optionLabel->setLabel($option['label']);
            $storeLabels[] = $optionLabel;
            // add store label for Arab store
            if ($this->config->getArStore() != null) {
                $arStoreId = $this->config->getArStore();
                if ($arStoreId == 0 && isset($option['label_ar'])) {
                    $optionLabel->setLabel($option['label_ar']);
                } elseif(isset($option['label_ar'])) {
                    $optionLabelAr = $this->objectFactory->create(\Magento\Eav\Api\Data\AttributeOptionLabelInterface::class, []);
                    $optionLabelAr->setStoreId($arStoreId);
                    $optionLabelAr->setLabel($option['label_ar']);
                    $storeLabels[] = $optionLabelAr;
                }
            }
            $newOption->setStoreLabels($storeLabels);
            $optionId = $this->getOptionId($newOption);
            $options['value'][$optionId][0] = $optionLabel;
            $options['order'][$optionId] = $newOption->getSortOrder();
            foreach ($storeLabels as $label) {
                $options['value'][$optionId][$label->getStoreId()] = $label->getLabel();
            }
            $attribute->setOption($options);
            /* $this->attributeOptionManagement->add(
                \Magento\Catalog\Model\Product::ENTITY,
                $attribute->getId(),
                $newOption
            ); */
            try {
                $this->resourceModel->save($attribute);
                if ($newOption->getLabel() && $attribute->getAttributeCode()) {
                    $this->setOptionValue($newOption, $attribute, $newOption->getLabel());
                }
                // add swatch option
                if ($this->swatchHelper->isTextSwatch($attribute)) {
                    $attribute = $this->getAttribute($attribute->getAttributeCode());
                    
                    $swatchs = [];
                    $optionId = $newOption->getValue();
                    $swatchs['value'][$optionId][0] = $newOption->getLabel();
                    foreach ($storeLabels as $label) {
                        $swatchs['value'][$optionId][$label->getStoreId()] = $label->getLabel();
                    }
                    $attribute->setSwatch($swatchs);
                    $this->resourceModel->save($attribute);
                }
                return $newOption->getValue();
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\StateException(__('The "%1" attribute can\'t be saved.', $attribute->getAttributeCode()));
            }
        }
        return '';
    }

    /**
     * Get matching option id with label by default store or Arab store
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute|string $attribute
     * @param string $label
     * @return int|null
     */
    public function getOptionIdByLabel($attribute, $label){
        if (is_string($attribute)) {
            $attribute = $this->getAttribute($attribute);
        }
        $option = $this->attributeOptionFactory->create();
        $optionCollection = $option->getCollection();
        $optionCollection->setAttributeFilter($attribute->getId())
            ->setStoreFilter()
            ->setPositionOrder();
        
        if ($this->config->getArStore() != null) {
            $connection = $optionCollection->getConnection();
            $optionCollection->getSelect()
                ->joinLeft(
                    ['ar' => $connection->getTableName('eav_attribute_option_value')],
                    $connection->quoteInto('ar.option_id = main_table.option_id AND ar.store_id = ?', $this->config->getArStore()),
                    [
                        'arvalue' => $connection->getCheckSql('ar.value_id > 0', 'ar.value', 'ar.value')
                    ]
                );
            $optionCollection->addFieldToFilter(
                array(
                    'tdv.value', 'ar.value'
                ),
                array(
                    array('like' => $label),
                    array('like' => $label)
            ));
        } else {
            $optionCollection->addFieldToFilter('tdv.value', array('like' => $label));
        }
        if ($optionCollection->getSize()) {
            return $optionCollection->getFirstItem()->getOptionId();
        }
        return '';
    }

    /**
     * Set option value
     *
     * @param \Magento\Eav\Api\Data\AttributeOptionInterface $option
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @param string $optionLabel
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface
     */
    private function setOptionValue(
        \Magento\Eav\Api\Data\AttributeOptionInterface $option,
        \Magento\Eav\Api\Data\AttributeInterface $attribute,
        string $optionLabel
    ) {
        $optionId = $attribute->getSource()->getOptionId($optionLabel);
        if ($optionId) {
            $option->setValue($attribute->getSource()->getOptionId($optionId));
        } elseif (is_array($option->getStoreLabels())) {
            foreach ($option->getStoreLabels() as $label) {
                if ($optionId = $attribute->getSource()->getOptionId($label->getLabel())) {
                    $option->setValue($attribute->getSource()->getOptionId($optionId));
                    break;
                }
            }
        }
    }

    /**
     * Returns option id
     *
     * @param \Magento\Eav\Api\Data\AttributeOptionInterface $option
     * @return string
     */
    private function getOptionId(\Magento\Eav\Api\Data\AttributeOptionInterface $option) : string
    {
        return 'id_' . ($option->getValue() ?: 'new_option');
    }
}