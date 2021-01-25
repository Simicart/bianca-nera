<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model;

class SyncStatus
{
    const SUCCESS   = 'success';
    const FAILED    = 'failed';
    const CONFLICT  = 'conflict';
    const PENDING   = 'pending';
    const MISSING   = 'missing';
    const SYNCING   = 'syncing';

    public function getOption(){
        return array(
            self::SUCCESS   => __('Success'),
            self::FAILED    => __('Failed'),
            self::CONFLICT  => __('Conflict'),
            self::PENDING   => __('pending'),
            self::SYNCING   => __('syncing'),
        );
    }
}