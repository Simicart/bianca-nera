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
        /* Update default value to 1 (YES) cause Ocean system does not allow to push attribute is_ocean = 1 */
        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.2', '<')) {
            $attributeId = $this->eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'is_ocean');
            $this->eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeId,
                array(
                    'default_value' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::VALUE_YES,
                    'is_visible' => true,
                    'is_required' => true,
                    'frontend_input' => 'select',
                    'source_model' => \Simi\Simiocean\Model\Source\SelectBoolean::class,
                    'note' => 'Please choose “No”',
                )
            );
        }
    }
}
