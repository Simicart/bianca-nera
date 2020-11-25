<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\ExpressRates\Model\Config\Source;

use Dhl\Express\Api\Data\ShippingProductsInterface;
use Magento\Framework\Data\OptionSourceInterface;

class InternationalProducts implements OptionSourceInterface
{
    public const DELIMITER = ';';

    public function toOptionArray()
    {
        $options = ShippingProductsInterface::PRODUCT_NAMES_INTERNATIONAL;

        return array_map(
            function ($label, $value) {
                $value = implode(self::DELIMITER, $value);
                return [
                    'value' => $value,
                    'label' => $label,
                ];
            },
            array_keys($options),
            $options
        );
    }
}
