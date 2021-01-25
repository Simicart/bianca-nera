<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simipaypalexpress\Rewrite;

use Magento\Payment\Helper\Formatter;

/**
 * Config model that is aware of all \Magento\Paypal payment methods
 *
 * Works with PayPal-specific system configuration

 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Config extends \Magento\Paypal\Model\Config
{
    protected $_supportedCurrencyCodes = [
        'AUD',
        'CAD',
        'CZK',
        'DKK',
        'EUR',
        'HKD',
        'HUF',
        'ILS',
        'JPY',
        'MXN',
        'NOK',
        'NZD',
        'PLN',
        'GBP',
        'RUB',
        'SGD',
        'SEK',
        'CHF',
        'TWD',
        'THB',
        'USD',
        'INR',
        'KWD',//simicustom
    ];
}
