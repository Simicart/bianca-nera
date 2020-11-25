<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\ExpressRates\Webservice;

use Dhl\ExpressRates\Webservice\Rate\RequestDataMapperInterface;
use Dhl\ExpressRates\Webservice\Rate\ResponseDataMapperInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\AbstractResult;

class RateAdapter
{
    /**
     * @var RequestDataMapperInterface
     */
    private $requestDataMapper;

    /**
     * @var ResponseDataMapperInterface;
     */
    private $responseDataMapper;

    /**
     * @var RateClient
     */
    private $client;

    public function __construct(
        RequestDataMapperInterface $requestDataMapper,
        ResponseDataMapperInterface $responseDataMapper,
        RateClient $client
    ) {
        $this->requestDataMapper = $requestDataMapper;
        $this->responseDataMapper = $responseDataMapper;
        $this->client = $client;
    }

    /**
     * Fetch Rates through API client
     *
     * @param RateRequest $request
     * @return AbstractResult[]
     * @throws LocalizedException
     */
    public function getRates(RateRequest $request): array
    {
        $requestModel = $this->requestDataMapper->mapRequest($request);

        $response = $this->client->performRatesRequest($requestModel);
        return $this->responseDataMapper->mapResult($response);
    }
}
