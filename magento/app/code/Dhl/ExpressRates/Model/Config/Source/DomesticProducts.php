<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\ExpressRates\Model\Config\Source;

use Dhl\Express\Api\Data\ShippingProductsInterface;
use Magento\Framework\Data\OptionSourceInterface;

class DomesticProducts implements OptionSourceInterface
{
    public const DELIMITER = ';';

    public function toOptionArray()
    {
        $options = ShippingProductsInterface::PRODUCT_NAMES_DOMESTIC;

        return array_map(
            function ($value, $label) {
                $value = implode(self::DELIMITER, $value);
                return [
                    'value' => $value,
                    'label' => $label,
                ];
            },
            $options,
            array_keys($options)
        );
    }
}
