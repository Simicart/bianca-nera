<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\ExpressRates\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Logger\Monolog;

class LogLevel implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => (string)Monolog::ERROR, 'label' => __('Errors')],
            ['value' => (string)Monolog::DEBUG, 'label' => __('Debug (All API Activities)')],
        ];
    }
}
