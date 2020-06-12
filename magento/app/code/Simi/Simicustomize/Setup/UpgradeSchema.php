<?php

namespace Simi\Simicustomize\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 *
 * @package Aheadworks\Giftcard\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();
        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.3', '<')) {

        }

        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.4', '<')) {

        }
        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.8', '<')) {
            // Customize simi home category
            $connection->addColumn(
                $setup->getTable('simiconnector_simicategory'),
                'is_show_name',
                [
                    'type' => Table::TYPE_SMALLINT,
                    'length' => '2',
                    'nullable' => true,
                    'comment' => 'Category name is shown yes/no'
                ]
            );
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.9', '<')) {
            /**
             * Creating table simicategory
             */
            $table_key_name = $setup->getTable('simiconnector_newcollections');
            $this->checkTableExist($setup, $table_key_name, 'simiconnector_newcollections');
            $table_key = $setup->getConnection()->newTable(
                $table_key_name
            )->addColumn(
                'newcollections_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Cat Id'
            )->addColumn(
                'newcollections_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Cat Name'
            )
            ->addColumn(
                'newcollections_filename_0',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'File Name'
            )->addColumn(
                'newcollections_filename_0_tablet',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'File name tablet'
            )
            ->addColumn(
                'newcollections_filename_1',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'File Name'
            )->addColumn(
                'newcollections_filename_1_tablet',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'File name tablet'
            )
            ->addColumn(
                'newcollections_filename_2',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'File Name'
            )->addColumn(
                'newcollections_filename_2_tablet',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'File name tablet'
            )
            ->addColumn(
                'newcollections_filename_3',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'File Name'
            )->addColumn(
                'newcollections_filename_3_tablet',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'File name tablet'
            )
            ->addColumn(
                'category_id_0',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'unsigned' => true],
                'Category Id'
            )
            ->addColumn(
                'category_id_1',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'unsigned' => true],
                'Category Id'
            )
            ->addColumn(
                'category_id_2',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'unsigned' => true],
                'Category Id'
            )
            ->addColumn(
                'category_id_3',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'unsigned' => true],
                'Category Id'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'unsigned' => true],
                'status'
            )->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'unsigned' => true],
                'Web Id'
            )->addColumn(
                'storeview_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Storeview Id'
            )->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Sort Order'
            )->addColumn(
                'matrix_width_percent',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Width Percent'
            )->addColumn(
                'matrix_height_percent',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Height Percent'
            )->addColumn(
                'matrix_width_percent_tablet',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Width Percent Tab'
            )->addColumn(
                'matrix_height_percent_tablet',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Height Percent Tab'
            )->addColumn(
                'matrix_row',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Rownum'
            );
            $setup->getConnection()->createTable($table_key);
            // end create table newcollections
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.10', '<')) {
            // Add quote_type to quote table
            $connection->addColumn(
                $setup->getTable('quote'),
                'deposit_order_increment_id',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => '12,4',
                    'nullable' => true,
                    'comment' => 'Deposit order Id'
                ]
            );
        }


        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.12', '<')) {
            // Add quote_type to quote table
            $connection->addColumn(
                $setup->getTable('quote'),
                'preorder_deposit_discount',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => true,
                    'comment' => 'Pre-order Deposit Discount'
                ]
            );
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.13', '<')) {
            $connection->addColumn(
                $setup->getTable('quote_item'), 'is_buy_service', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => '5',
                    [],
                    'comment' => 'Item is purchased with Service',
                ]
            );

            $connection->addColumn(
                $setup->getTable('sales_order_item'), 'is_buy_service', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => '5',
                    [],
                    'comment' => 'Item is purchased with Service',
                ]
            );

            // Add quote_type to quote table
            $connection->addColumn(
                $setup->getTable('quote'),
                'service_support_fee',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => true,
                    'comment' => 'Service support fee'
                ]
            );
        }

        /**
         * Creating table simiconnector_banner
         */
        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.14', '<')) {
            $table_name = $setup->getTable('simiconnector_homesection');
            $this->checkTableExist($setup, $table_name, 'simiconnector_homesection');
            $homesection_table = $setup->getConnection()->newTable(
                $table_name
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Section Id'
            )->addColumn(
                'section_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Name'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Status'
            )->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Web ID'
            )->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Sort Order'
            )

            ->addColumn(
                'image_left_1',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Section Image 1 URL'
            )->addColumn(
                'image_left_1_mobile',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Section Image 1 URL mobile'
            )->addColumn(
                'image_left_2',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Section Image 2 URL'
            )->addColumn(
                'image_left_2_mobile',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Section Image 2 URL mobile'
            )
            
            ->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Type for images'
            )->addColumn(
                'type_value_1',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Type value 1 for image 1'
            )->addColumn(
                'type_value_2',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Type value 2 for image 2'
            )
            
            ->addColumn(
                'product_id_1',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Product ID'
            )->addColumn(
                'product_id_2',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Product ID'
            )->addColumn(
                'product_id_3',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Product ID'
            );

            $setup->getConnection()->createTable($homesection_table);
        }
        /** update home section table add product skus columns */
        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.15', '<')) {
            $table_name = $setup->getTable('simiconnector_homesection');
            $connection->addColumn(
                    $table_name,
                    'product_sku_1',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => '255',
                        'nullable' => true,
                        'comment' => 'Product sku for product_id_1'
                    ]
                );
            $connection->addColumn(
                    $table_name,
                    'product_sku_2',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => '255',
                        'nullable' => true,
                        'comment' => 'Product sku for product_id_2'
                    ]
                );
            $connection->addColumn(
                    $table_name,
                    'product_sku_3',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => '255',
                        'nullable' => true,
                        'comment' => 'Product sku for product_id_3'
                    ]
                );
        }
    }

    public function checkTableExist($installer, $table_key_name, $table_name)
    {
        if ($installer->getConnection()->isTableExists($table_key_name) == true) {
            $installer->getConnection()
                    ->dropTable($installer->getConnection()->getTableName($table_name));
        }
    }
}
