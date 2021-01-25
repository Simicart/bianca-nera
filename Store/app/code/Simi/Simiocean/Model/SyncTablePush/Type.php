<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model\SyncTablePush;

class Type
{
    const TYPE_PRODUCT = 'product';
    const TYPE_CUSTOMER = 'customer';

    public function getOption(){
        return array(
            self::TYPE_PRODUCT => __('Product'),
            self::TYPE_CUSTOMER => __('Customer'),
        );
    }
}