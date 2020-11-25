<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\ExpressRates\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class RoundedPricesFormat implements OptionSourceInterface
{
    /**
     * No rounding key.
     */
    public const DO_NOT_ROUND = 'no_rounding';

    /**
     * Full price key.
     */
    public const FULL_PRICE = 'full_price';

    /**
     * Static decimal key.
     */
    public const STATIC_DECIMAL = 'static_decimal';

    public function toOptionArray()
    {
        return [

            ['value' => self::DO_NOT_ROUND,   'label' => __('Don\'t round prices')],
            ['value' => self::FULL_PRICE,     'label' => __('Round to a whole number (ex. 1 or 37)')],
            ['value' => self::STATIC_DECIMAL, 'label' => __('Round to a specific decimal value (ex. 99 cents)')],
        ];
    }
}
