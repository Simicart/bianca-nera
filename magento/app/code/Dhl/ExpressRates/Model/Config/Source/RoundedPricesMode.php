<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\ExpressRates\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class RoundedPricesMode implements OptionSourceInterface
{
    /**
     * Round up key.
     */
    public const ROUND_UP = 'round_up';

    /**
     * Round off key.
     */
    public const ROUND_OFF = 'round_off';

    public function toOptionArray()
    {
        return [
            ['value' => self::ROUND_UP,  'label' => __('Round up')],
            ['value' => self::ROUND_OFF, 'label' => __('Round down')],
        ];
    }
}
