<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\ExpressRates\Webservice\Rate;

use Dhl\Express\Api\Data\RateRequestInterface;
use Dhl\Express\Api\RateRequestBuilderInterface;
use Dhl\ExpressRates\Model\Config\ModuleConfigInterface;
use Dhl\ExpressRates\Model\PickupTime;
use Dhl\Express\Model\Request\Rate\ShipmentDetails;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;

class RequestDataMapper implements RequestDataMapperInterface
{
    /**
     * @var RateRequestBuilderInterface
     */
    private $rateRequestBuilder;

    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * @var PickupTime
     */
    private $pickupTime;

    public function __construct(
        RateRequestBuilderInterface $rateRequestBuilder,
        ModuleConfigInterface $moduleConfig,
        PickupTime $pickupTime
    ) {
        $this->rateRequestBuilder = $rateRequestBuilder;
        $this->moduleConfig = $moduleConfig;
        $this->pickupTime = $pickupTime;
    }

    /**
     * Maps the available application data to the DHL Express specific request object
     *
     * @param RateRequest $request
     *
     * @return RateRequestInterface
     * @throws LocalizedException
     */
    public function mapRequest(RateRequest $request): RateRequestInterface
    {
        $this->rateRequestBuilder->setShipperAddress(
            $request->getCountryId(),
            $request->getPostcode(),
            $request->getCity()
        );

        if (empty($request->getDestPostcode())) {
            throw new LocalizedException(
                __('The recipient postal code is missing, which is required to calculate rates')
            );
        }

        if (empty($request->getDestCity())) {
            throw new LocalizedException(
                __('The recipient city is missing, which is required to calculate rates')
            );
        }

        $this->rateRequestBuilder->setRecipientAddress(
            $request->getDestCountryId(),
            $request->getDestPostcode(),
            $request->getDestCity(),
            [substr($request->getDestStreet(), 0, 35)]
        );

        $this->rateRequestBuilder->addPackage(
            1,
            $this->calculatePackageWeight($request),
            $this->moduleConfig->getWeightUnit($request->getStoreId()),
            1,
            1,
            1,
            $this->moduleConfig->getDimensionsUOM()
        );

        $contentType = $this->moduleConfig
            ->isDutiableRoute($request->getDestCountryId(), $request->getStoreId())
            ? ShipmentDetails::CONTENT_TYPE_NON_DOCUMENTS
            : ShipmentDetails::CONTENT_TYPE_DOCUMENTS;

        $this->rateRequestBuilder
            ->setContentType($contentType)
            ->setIsUnscheduledPickup(!$this->moduleConfig->isRegularPickup($request->getStoreId()))
            ->setTermsOfTrade($this->moduleConfig->getTermsOfTrade($request->getStoreId()))
            ->setIsValueAddedServicesRequested(true)
            ->setNextBusinessDayIndicator(true)
            ->setReadyAtTimestamp($this->pickupTime->getReadyAtTimestamp())
            ->setShipperAccountNumber($this->moduleConfig->getAccountNumber($request->getStoreId()));

        if ($this->moduleConfig->isInsured() &&
            $request->getPackagePhysicalValue() >= $this->moduleConfig->insuranceFromValue()
        ) {
            $this->rateRequestBuilder->setInsurance(
                $request->getPackagePhysicalValue(),
                $request->getBaseCurrency()->getCurrencyCode()
            );
        }

        return $this->rateRequestBuilder->build();
    }

    /**
     * Calculate the total weight of the package by adding the individiual weight of the items in the quote to the
     * configured packaging weight.
     *
     * @param RateRequest $request
     * @return float
     */
    private function calculatePackageWeight(RateRequest $request): float
    {
        $itemWeight = (float)$request->getPackageWeight();
        $packagingWeight = $this->moduleConfig->getPackagingWeight($request->getWebsiteId());

        return $itemWeight + $packagingWeight;
    }
}
