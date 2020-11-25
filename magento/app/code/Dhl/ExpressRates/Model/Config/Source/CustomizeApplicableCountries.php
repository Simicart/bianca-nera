<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\ExpressRates\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CustomizeApplicableCountries implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '0', 'label' => __('Use default countries from General > Country')],
            ['value' => '1', 'label' => __('Create a customized country list')],
        ];
    }
}
