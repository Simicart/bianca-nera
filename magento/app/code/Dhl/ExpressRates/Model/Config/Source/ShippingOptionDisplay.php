<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\ExpressRates\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ShippingOptionDisplay implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '0', 'label' => __('Cost only')],
            ['value' => '1', 'label' => __('Cost and estimated delivery dates')],
        ];
    }
}
