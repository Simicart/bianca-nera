<?php
/**
 * Product inventory data validator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simicustomize\Plugin;

class QuoteItemQuantityValidator
{
    public function aroundValidate($subject, $proceed, $observer)
    {

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return;
        }

        $result = $proceed($observer);
        return $result;
    }
}
