<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\ExpressRates\Model\Rate\Processor;

use Dhl\ExpressRates\Model\Config\ModuleConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Dhl\ExpressRates\Model\Rate\RateProcessorInterface;

class AllowedProducts implements RateProcessorInterface
{
    /**
     * @var ModuleConfigInterface
     */
    private $config;

    /**
     * AllowedProducts constructor.
     *
     * @param ModuleConfigInterface $config
     */
    public function __construct(ModuleConfigInterface $config)
    {
        $this->config = $config;
    }

    public function processMethods(array $methods, $request = null): array
    {
        $result = [];
        foreach ($methods as $method) {
            if ($this->isEnabledProduct($method)) {
                $result[] = $method;
            }
        }

        return $result;
    }

    /**
     * Returns whether the product is enabled in the configuration or not.
     *
     * @param Method $method The rate method
     *
     * @return bool
     */
    private function isEnabledProduct(Method $method): bool
    {
        $allowedDomestic      = $this->config->getAllowedDomesticProducts();
        $allowedInternational = $this->config->getAllowedInternationalProducts();
        $allowedProducts      = array_merge($allowedDomestic, $allowedInternational);

        return \in_array($method->getData('method'), $allowedProducts, true);
    }
}
