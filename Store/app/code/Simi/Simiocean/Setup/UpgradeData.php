<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
    * @param EavSetup $eavSetup,
    */
    public function __construct(
        EavSetup $eavSetup
    ) {
        $this->eavSetup = $eavSetup;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context){
        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'is_ocean',
                [
                    'input'              => 'boolean',
                    'type'               => 'int',
                    'label'              => 'Is Ocean Product',
                    'visible'            => false,
                    'required'           => false,
                    'is_used_in_grid'            => true,
                    'is_visible_in_grid'         => true,
                    'is_filterable_in_grid'      => true,
                    'user_defined'               => false,
                    'searchable'                 => false,
                    'filterable'                 => false,
                    'comparable'                 => false,
                    'visible_on_front'           => false,
                    'visible_in_advanced_search' => false,
                    'is_html_allowed_on_front'   => false,
                    'used_for_promo_rules'       => true,
                    'source'                     => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    'default'                    => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::VALUE_NO,
                    'global'                     =>  \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'unique'                     => false,
                    'apply_to'                   => 'simple,grouped,configurable,downloadable,virtual,bundle,aw_giftcard'
                ]
            );
        }
        /* Update default value to 0 (NO), begin version 1.0.4 can remove this code */
        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.3', '<')) {
            $attributeId = $this->eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'is_ocean');
            $this->eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeId,
                array(
                    'default_value' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::VALUE_NO,
                    'is_visible' => false,
                    'is_required' => false,
                    'frontend_input' => 'boolean',
                    'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    'note' => 'Please choose “No”',
                )
            );
        }

        /** Update vendor entity attribute */
        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.5', '<')) {
            $this->eavSetup->addAttribute(
                \Vnecoms\Vendors\Model\Vendor::ENTITY,
                'ocean_brand_id',
                [
                    'type' => 'int',
                    'nullable'  => true,
                    'length'    =>  10,
                    'comment'   => 'Ocean BrandID as magento vendor_id',
                    'label'     => 'Ocean BrandID',
                    'visible'                    => false,
                    'required'                   => false,
                    'user_defined'               => false,
                    'searchable'                 => false,
                    'filterable'                 => false,
                    'comparable'                 => false,
                    'visible_on_front'           => false,
                    'visible_in_advanced_search' => false,
                    'is_html_allowed_on_front'   => false,
                ]
            );
        }
    }
}
