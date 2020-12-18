<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model\SyncTable;

class Type
{
    const TYPE_PRODUCT = 'product';
    const TYPE_CUSTOMER = 'customer';
    const TYPE_CUSTOMER_UPDATE = 'customer_update'; // ocean update
    const TYPE_PRODUCT_UPDATE = 'product_update'; // ocean update
    const TYPE_PRODUCT_UPDATE_CUSTOM = 'product_update_custom';
    const TYPE_PRODUCT_UPDATE_STOCK = 'product_update_stock'; // ocean stock update

    public function getOption(){
        return array(
            self::TYPE_PRODUCT => __('Product'),
            self::TYPE_CUSTOMER => __('Customer'),
            self::TYPE_CUSTOMER_UPDATE => __('Ocean Customer Update'),
            self::TYPE_PRODUCT_UPDATE_CUSTOM => __('Ocean Product Update'),
            self::TYPE_PRODUCT_UPDATE_STOCK => __('Ocean Product Stock Update'),
        );
    }
}