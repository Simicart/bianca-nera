<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\ExpressRates\Api\Data;

interface MethodAdditionalInfoInterface
{
    public const ATTRIBUTE_KEY = 'additional_info';
    public const DELIVERY_DATE = 'delivery_date';

    /**
     * @return string
     */
    public function getDeliveryDate(): string;

    /**
     * @param string $deliveryDate
     * @return void
     */
    public function setDeliveryDate($deliveryDate): void;
}
