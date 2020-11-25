<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\ExpressRates\Model\Rate\Processor;

use Dhl\Express\Api\Data\ShippingProductsInterface;
use Dhl\ExpressRates\Model\Config\ModuleConfigInterface;
use Dhl\ExpressRates\Model\Rate\RateProcessorInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Shipping\Model\Carrier\AbstractCarrier;

/**
 * A rate processor to append the handling fee based on handling type to the shipping price.
 */
class HandlingFee implements RateProcessorInterface
{
    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    public function __construct(
        ModuleConfigInterface $moduleConfig
    ) {
        $this->moduleConfig = $moduleConfig;
    }

    public function processMethods(array $methods, $request = null): array
    {
        /** @var Method $method */
        foreach ($methods as $method) {
            // Calculate fee depending on shipping type
            $price = $this->calculatePrice(
                $method->getPrice(),
                $this->getHandlingType($method),
                $this->getHandlingFee($method)
            );

            $method->setPrice($price);
            $method->setCost($price);
        }

        return $methods;
    }

    /**
     * Returns the configured handling type depending on the shipping type.
     *
     * @param Method $method The rate method
     *
     * @return string
     */
    private function getHandlingType(Method $method): string
    {
        // Calculate fee depending on shipping type
        if ($this->isDomesticShipping($method)) {
            return $this->moduleConfig->getDomesticHandlingType();
        }

        return $this->moduleConfig->getInternationalHandlingType();
    }

    /**
     * Returns the configured handling fee depending on the shipping type.
     *
     * @param Method $method The rate method
     *
     * @return float
     */
    private function getHandlingFee(Method $method): float
    {
        // Calculate fee depending on shipping type
        if ($this->isDomesticShipping($method)) {
            return $this->moduleConfig->getDomesticHandlingFee();
        }

        return $this->moduleConfig->getInternationalHandlingFee();
    }

    /**
     * Returns whether the given method applies to domestic shipping or not.
     *
     * @param Method $method The rate method
     *
     * @return bool
     */
    private function isDomesticShipping(Method $method): bool
    {
        return \in_array($method->getMethod(), ShippingProductsInterface::PRODUCTS_DOMESTIC, true);
    }

    /**
     * Calculates the shipping price altered by the handling type aqnd fee.
     *
     * @param float $amount The total price of the rated shipment for the product
     * @param string $handlingType The handling type determining the type of calculation to do
     * @param float $handlingFee The handling fee to apply to the amount
     *
     * @return float
     */
    private function calculatePrice($amount, $handlingType, $handlingFee): float
    {
        if ($handlingType === AbstractCarrier::HANDLING_TYPE_PERCENT) {
            $amount += $amount * $handlingFee / 100.0;
        } else {
            $amount += $handlingFee;
        }

        return $amount < 0.0 ? 0.0 : $amount;
    }
}
