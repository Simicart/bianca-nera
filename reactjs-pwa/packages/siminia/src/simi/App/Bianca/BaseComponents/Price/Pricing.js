import React, { Fragment } from 'react';
import { number, string, shape } from 'prop-types';
import { Price } from '@magento/peregrine';
import IntlPatches from '@magento/peregrine/lib/util/intlPatches';
import Identify from 'src/simi/Helper/Identify';


/**
 * The **Price** component is used anywhere a price needs to be displayed.
 *
 * Formatting of prices and currency symbol selection is handled entirely by the ECMAScript Internationalization API available in modern browsers.
 *
 * A [polyfill][] is required for any JavaScript runtime that does not have [Intl.NumberFormat.prototype.formatToParts][].
 *
 * [polyfill]: https://www.npmjs.com/package/intl
 * [Intl.NumberFormat.prototype.formatToParts]: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/DateTimeFormat/formatToParts
 */
export default class Pricing extends Price {

    render() {
        const { value, currencyCode, classes, isServerCurrency = true, enableLocale = false, decimalsNum=2 } = this.props;
        const storeConfig = Identify.getStoreConfig() || {};
        const {config} = storeConfig && storeConfig.simiStoreConfig || {};
        const {country_code, currency_code, currency_symbol, currency_position, 
            locale_identifier,
            thousand_separator,
            decimal_separator,
            min_number_of_decimals,
            max_number_of_decimals
        } = config && config.base || {};

        if (currency_position) {
            const locale = enableLocale && locale_identifier && locale_identifier.replace(/_/g, '-') || 'en-US';
            const formatterValue = new Intl.NumberFormat(locale, {
                style: 'currency',
                currency: currencyCode,
                minimumFractionDigits: parseInt(decimalsNum || min_number_of_decimals) ? parseInt(decimalsNum || min_number_of_decimals) : parseInt(decimalsNum || max_number_of_decimals),
                maximumFractionDigits: parseInt(decimalsNum || max_number_of_decimals),
            });

            if (enableLocale) {
                return formatterValue.format(value);
            }

            let sign = '', unsignedValue = value;
            if (parseFloat(value) < 0) {
                sign = '-';
                unsignedValue = Math.abs(value);
            }

            if (isServerCurrency) {
                const priceValue = formatterValue.format(value);
                const space = /\s/g.test(priceValue) ? ' ':''; // has white space
                const serverVal = new Intl.NumberFormat(undefined, {
                    minimumFractionDigits: decimalsNum,
                    maximumFractionDigits: decimalsNum
                }).format(unsignedValue);
                if (currency_position === 'before') {
                    return `${sign}${currency_symbol||currency_code}${space}${serverVal}`;
                }
                return `${serverVal}${space}${currency_symbol||currency_code}${sign}`;
            }

            const formatterSymbol = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: currencyCode,
                minimumFractionDigits: 0
            });
            const formatedSymbol = formatterSymbol.format(1).replace(/\s/g, '');
            const symbol = isServerCurrency ? currency_symbol : formatedSymbol.substr(0, formatedSymbol.length - 1);
            const priceVal = isServerCurrency ? unsignedValue : formatterValue.format(unsignedValue).replace(symbol, '');

            if (currency_position === 'before') {
                return `${sign}${symbol}${priceVal}`;
            }
            return `${priceVal}${symbol}${sign}`;
        }

        const parts = IntlPatches.toParts.call(
            Intl.NumberFormat(undefined, {
                style: 'currency',
                currency: currencyCode,
            }),
            value
        );

        const children = parts.map((part, i) => {
            const partClass = classes[part.type];
            const key = `${i}-${part.value}`;

            return (
                <span key={key} className={partClass}>
                    {part.value}
                </span>
            );
        });

        return <Fragment>{children}</Fragment>;
    }

    static defaultProps = {
        classes: {}
    };

    static propTypes = {
        /**
         * The numeric price
         */
        value: number.isRequired,
        /**
         * A string with any of the currency code supported by Intl.NumberFormat
         */
        currencyCode: string.isRequired,
        /**
         * Class names to use when styling this component
         */
        classes: shape({
            currency: string,
            integer: string,
            decimal: string,
            fraction: string
        })
    };
}
