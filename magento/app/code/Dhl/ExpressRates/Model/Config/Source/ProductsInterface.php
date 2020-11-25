<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\ExpressRates\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

interface ProductsInterface extends OptionSourceInterface
{
    /**
     * Returns the list of options as plain array.
     *
     * @return array
     */
    public function toPlainArray(): array;
}
