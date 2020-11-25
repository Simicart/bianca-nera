<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\ExpressRates\Webservice;

use Dhl\Express\Api\Data\RateRequestInterface;
use Dhl\Express\Api\Data\RateResponseInterface;
use Dhl\Express\Api\ServiceFactoryInterface;
use Dhl\Express\Exception\ExpressApiException;
use Dhl\ExpressRates\Model\Config\ModuleConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class RateClient
{
    /**
     * @var ServiceFactoryInterface
     */
    private $serviceFactory;

    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ServiceFactoryInterface $serviceFactory,
        ModuleConfigInterface $moduleConfig,
        LoggerInterface $logger
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->moduleConfig = $moduleConfig;
        $this->logger = $logger;
    }

    /**
     * @param RateRequestInterface $request
     * @return RateResponseInterface
     * @throws LocalizedException
     */
    public function performRatesRequest(RateRequestInterface $request): RateResponseInterface
    {
        try {
            $rateService = $this->serviceFactory->createRateService(
                $this->moduleConfig->getUserName(),
                $this->moduleConfig->getPassword(),
                $this->logger
            );

            return $rateService->collectRates($request);
        } catch (ExpressApiException $exception) {
            throw new LocalizedException(__('Web service request failed: %1', $exception->getMessage()), $exception);
        }
    }
}
