<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\ExpressRates\Webservice\Rate;

use Dhl\Express\Api\Data\RateResponseInterface;
use Magento\Quote\Model\Quote\Address\RateResult\Method;

interface ResponseDataMapperInterface
{
    /**
     * Map rate response shipping products into Magento rate result methods
     *
     * @param RateResponseInterface $rateResponse
     * @return Method[]
     */
    public function mapResult(RateResponseInterface $rateResponse): array;
}
