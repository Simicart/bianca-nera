<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\ExpressRates\Model\Rate;

use Dhl\ExpressRates\Webservice\RateAdapter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;

class CheckoutProvider
{
    /**
     * @var RateAdapter
     */
    private $rateAdapter;

    /**
     * @var ResultFactory
     */
    private $rateResultFactory;

    /**
     * @var RateProcessorInterface[]
     */
    private $rateProcessors;

    /**
     * CheckoutProvider constructor.
     *
     * @param RateAdapter $rateAdapter
     * @param ResultFactory $rateResultFactory
     * @param RateProcessorInterface[] $rateProcessors
     */
    public function __construct(
        RateAdapter $rateAdapter,
        ResultFactory $rateResultFactory,
        array $rateProcessors = []
    ) {
        $this->rateAdapter = $rateAdapter;
        $this->rateResultFactory = $rateResultFactory;
        $this->rateProcessors = $rateProcessors;
    }

    /**
     * @param RateRequest $request
     * @return Result
     * @throws LocalizedException
     */
    public function getRates(RateRequest $request): Result
    {
        $methods    = $this->rateAdapter->getRates($request);
        $rateResult = $this->rateResultFactory->create();

        foreach ($this->rateProcessors as $rateProcessor) {
            $methods = $rateProcessor->processMethods($methods, $request);
        }

        foreach ($methods as $method) {
            $rateResult->append($method);
        }

        if (empty($methods)) {
            throw new LocalizedException(__('No rates returned from API.'));
        }

        return $rateResult;
    }
}
