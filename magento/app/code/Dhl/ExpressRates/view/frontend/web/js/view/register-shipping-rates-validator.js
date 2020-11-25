/**
 * See LICENSE.md for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        'Dhl_ExpressRates/js/model/shipping-rates-validator',
        'Dhl_ExpressRates/js/model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        shippingRatesValidator,
        shippingRatesValidationRules
    ) {
        'use strict';
        defaultShippingRatesValidator.registerValidator('dhlexpress', shippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('dhlexpress', shippingRatesValidationRules);
        return Component;
    }
);
