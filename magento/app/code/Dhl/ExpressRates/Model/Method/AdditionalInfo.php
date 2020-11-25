<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\ExpressRates\Model\Method;

use Dhl\ExpressRates\Api\Data\MethodAdditionalInfoInterface;
use Magento\Framework\DataObject;

class AdditionalInfo extends DataObject implements MethodAdditionalInfoInterface
{
    public function getDeliveryDate(): string
    {
        return (string)$this->getData(self::DELIVERY_DATE);
    }

    public function setDeliveryDate($deliveryDate): void
    {
        $this->setData(self::DELIVERY_DATE, $deliveryDate);
    }
}
