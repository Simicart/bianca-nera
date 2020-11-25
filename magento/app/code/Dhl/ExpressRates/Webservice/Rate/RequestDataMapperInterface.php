<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\ExpressRates\Webservice\Rate;

use Dhl\Express\Api\Data\RateRequestInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;

interface RequestDataMapperInterface
{
    /**
     * Maps the available application data to the DHL Express specific request object
     *
     * @param RateRequest $request
     * @return RateRequestInterface
     */
    public function mapRequest(RateRequest $request): RateRequestInterface;
}
