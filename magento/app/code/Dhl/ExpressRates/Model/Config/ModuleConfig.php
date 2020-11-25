<?php

declare(strict_types=1);

/**
 * See LICENSE.md for license details.
 */

namespace Dhl\ExpressRates\Model\Config;

use Dhl\ExpressRates\Model\Config\Source\InternationalProducts;
use Dhl\ExpressRates\Model\Config\Source\RoundedPricesFormat;
use Dhl\ExpressRates\Model\Config\Source\RoundedPricesMode;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Shipping\Helper\Carrier;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\ScopeInterface;

class ModuleConfig implements ModuleConfigInterface
{
    public const DEFAULT_DIMENSION_UNIT = 'in';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var string[]
     */
    private $weightUnitMap = [
        'kgs' => 'kg',
        'lbs' => 'lb',
        'POUND' => 'lb',
        'KILOGRAM' => 'kg',
    ];

    /**
     * @var string[]
     */
    private $dimensionUnitMap = [
        'INCH' => 'in',
        'CENTIMETER' => 'cm',
    ];

    /**
     * @var string[]
     */
    private $weightUnitToDimensionUnitMap = [
        'kg' => 'cm',
        'lb' => 'in',
    ];

    /**
     * ModuleConfig constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * Check if the module is enabled.
     *
     * @param string|null $store
     * @return bool
     */
    public function isEnabled($store = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get the sort order.
     *
     * @param string|null $store
     * @return int
     */
    public function getSortOrder($store = null): int
    {
        return (int)$this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_SORT_ORDER,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get the title.
     *
     * @param string|null $store
     * @return string
     */
    public function getTitle($store = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_TITLE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get the emulated carrier.
     *
     * @param string|null $store
     * @return string
     */
    public function getEmulatedCarrier($store = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_EMULATED_CARRIER,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if shipping only to specific countries.
     *
     * @param string|null $store
     * @return bool
     */
    public function shipToSpecificCountries($store = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_SHIP_TO_SPECIFIC_COUNTRIES,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get the specific countries.
     *
     * @param string|null $store
     * @return string[]
     */
    public function getSpecificCountries($store = null): array
    {
        $countries = $this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_SPECIFIC_COUNTRIES,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        return explode(',', $countries);
    }

    /**
     * Show DHL Express in checkout if there are no products available.
     *
     * @param string|null $store
     * @return bool
     */
    public function showIfNotApplicable($store = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_SHOW_IF_NOT_APPLICABLE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get the error message.
     *
     * @param string|null $store
     * @return string
     */
    public function getNotApplicableErrorMessage($store = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_ERROR_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get the username.
     *
     * @param string|null $store
     * @return string
     */
    public function getUserName($store = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_USERNAME,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get the password.
     *
     * @param string|null $store
     * @return string
     */
    public function getPassword($store = null): string
    {
        return (string)$this->encryptor->decrypt(
            $this->scopeConfig->getValue(
                self::CONFIG_XML_PATH_PASSWORD,
                ScopeInterface::SCOPE_STORE,
                $store
            )
        );
    }

    /**
     * Get the log level.
     *
     * @param string|null $store
     * @return int
     */
    public function getLogLevel($store = null): int
    {
        return (int)$this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_LOGLEVEL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get the account number.
     *
     * @param string|null $store
     * @return string
     */
    public function getAccountNumber($store = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_ACCOUNT_NUMBER,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get the allowed domestic products.
     *
     * @param string|null $store
     * @return string[]
     */
    public function getAllowedDomesticProducts($store = null): array
    {
        $allowedProducts = $this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_ALLOWED_DOMESTIC_PRODUCTS,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        return $this->normalizeAllowedProducts($allowedProducts);
    }

    /**
     * Get the allowed international products.
     *
     * @param string|null $store
     * @return string[]
     */
    public function getAllowedInternationalProducts($store = null): array
    {
        $allowedProductsValue = $this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_ALLOWED_INTERNATIONAL_PRODUCTS,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        return $this->normalizeAllowedProducts($allowedProductsValue);
    }

    /**
     * Get the Logging status.
     *
     * @param string|null $store
     * @return bool
     */
    public function isLoggingEnabled($store = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_ENABLE_LOGGING,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if regular pickup is enabled.
     *
     * @param string|null $store
     * @return bool
     */
    public function isRegularPickup($store = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_REGULAR_PICKUP,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return if packages are insured.
     *
     * @param string|null $store
     * @return bool
     */
    public function isInsured($store = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_PACKAGE_INSURANCE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get the value from which the packages should be insured.
     *
     * @param string|null $store
     * @return float
     */
    public function insuranceFromValue($store = null): float
    {
        return (float)$this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_PACKAGE_INSURANCE_FROM_VALUE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get the pickup time.
     *
     * @param string|null $store
     * @return string
     */
    public function getPickupTime($store = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_PICKUP_TIME,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get the domestic handling type.
     *
     * @param string|null $store
     *
     * @return string
     */
    public function getDomesticHandlingType($store = null): string
    {
        return $this->isDomesticRatesConfigurationEnabled($store) ?
            (string)$this->scopeConfig->getValue(
                ModuleConfigInterface::CONFIG_XML_PATH_DOMESTIC_HANDLING_TYPE,
                ScopeInterface::SCOPE_STORE,
                $store
            ) : '';
    }

    /**
     * Get the domestic handling fee.
     *
     * @param string|null $store
     *
     * @return float|null
     */
    public function getDomesticHandlingFee($store = null)
    {
        if ($this->isDomesticRatesConfigurationEnabled($store)) {
            $type = $this->getDomesticHandlingType($store) ===
            \Magento\Shipping\Model\Carrier\AbstractCarrier::HANDLING_TYPE_FIXED ?
                self::CONFIG_XML_SUFFIX_FIXED :
                self::CONFIG_XML_SUFFIX_PERCENTAGE;

            return (float)$this->scopeConfig->getValue(
                ModuleConfigInterface::CONFIG_XML_PATH_DOMESTIC_HANDLING_FEE . $type,
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }

        return 0;
    }

    /**
     * Get the international handling type.
     *
     * @param string|null $store
     *
     * @return string
     */
    public function getInternationalHandlingType($store = null): string
    {
        return $this->isInternationalRatesConfigurationEnabled($store) ?
            (string)$this->scopeConfig->getValue(
                ModuleConfigInterface::CONFIG_XML_PATH_INTERNATIONAL_HANDLING_TYPE,
                ScopeInterface::SCOPE_STORE,
                $store
            ) :
            '';
    }

    /**
     * Get the international handling fee.
     *
     * @param string|null $store
     * @return float
     */
    public function getInternationalHandlingFee($store = null): float
    {
        if ($this->isInternationalRatesConfigurationEnabled($store)) {
            $type =
                $this->getInternationalHandlingType($store) ===
                \Magento\Shipping\Model\Carrier\AbstractCarrier::HANDLING_TYPE_FIXED ?
                    self::CONFIG_XML_SUFFIX_FIXED :
                    self::CONFIG_XML_SUFFIX_PERCENTAGE;

            return (float)$this->scopeConfig->getValue(
                ModuleConfigInterface::CONFIG_XML_PATH_INTERNATIONAL_HANDLING_FEE . $type,
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }
        return 0;
    }

    /**
     * Get mode for rounded prices.
     *
     * @param string|null $store
     * @return string
     */
    public function getRoundedPricesMode($store = null): string
    {
        return (string)$this->scopeConfig->getValue(
            ModuleConfigInterface::CONFIG_XML_PATH_ROUNDED_PRICES_MODE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Returns true when price should be rounded up.
     *
     * @param string|null $store
     * @return bool
     */
    public function roundUp($store = null): bool
    {
        return $this->getRoundedPricesFormat($store) === RoundedPricesFormat::DO_NOT_ROUND
            ? false
            : $this->getRoundedPricesMode($store) === RoundedPricesMode::ROUND_UP;
    }

    /**
     * Returns true when price should be rounded off.
     *
     * @param string|null $store
     * @return bool
     */
    public function roundOff($store = null): bool
    {
        return $this->getRoundedPricesFormat($store) === RoundedPricesFormat::DO_NOT_ROUND
            ? false
            : $this->getRoundedPricesMode($store) === RoundedPricesMode::ROUND_OFF;
    }

    /**
     * Get rounded prices format.
     *
     * @param string|null $store
     * @return string
     */
    public function getRoundedPricesFormat($store = null): string
    {
        return (string)$this->scopeConfig->getValue(
            ModuleConfigInterface::CONFIG_XML_PATH_ROUNDED_PRICES_FORMAT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get rounded prices static decimal value.
     *
     * @param string|null $store
     * @return float
     */
    public function getRoundedPricesStaticDecimal($store = null): float
    {
        return (float)$this->scopeConfig->getValue(
            ModuleConfigInterface::CONFIG_XML_PATH_ROUNDED_PRICES_STATIC_DECIMAL,
            ScopeInterface::SCOPE_STORE,
            $store
        ) / 100;
    }

    public function isInternationalFreeShippingEnabled($store = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            ModuleConfigInterface::CONFIG_XML_PATH_FREE_SHIPPING_INTERNATIONAL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function isDomesticFreeShippingEnabled($store = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            ModuleConfigInterface::CONFIG_XML_PATH_FREE_SHIPPING_DOMESTIC_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function isFreeShippingVirtualProductsIncluded($store = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            ModuleConfigInterface::CONFIG_XML_PATH_FREE_SHIPPING_VIRTUAL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getDomesticFreeShippingProducts($store = null): array
    {
        if ($this->isDomesticFreeShippingEnabled($store)) {
            $allowedProducts = $this->scopeConfig->getValue(
                ModuleConfigInterface::CONFIG_XML_PATH_DOMESTIC_FREE_SHIPPING_PRODUCTS,
                ScopeInterface::SCOPE_STORE,
                $store
            );

            return $this->normalizeAllowedProducts($allowedProducts);
        }
        return [];
    }

    public function getDomesticFreeShippingSubTotal($store = null): float
    {
        return $this->isDomesticFreeShippingEnabled($store) ?
            (float)$this->scopeConfig->getValue(
                ModuleConfigInterface::CONFIG_XML_PATH_DOMESTIC_FREE_SHIPPING_SUBTOTAL,
                ScopeInterface::SCOPE_STORE,
                $store
            ) : 0;
    }

    public function getInternationalFreeShippingProducts($store = null): array
    {
        if ($this->isInternationalFreeShippingEnabled($store)) {
            $allowedProducts = $this->scopeConfig->getValue(
                ModuleConfigInterface::CONFIG_XML_PATH_INTERNATIONAL_FREE_SHIPPING_PRODUCTS,
                ScopeInterface::SCOPE_STORE,
                $store
            );

            return $this->normalizeAllowedProducts($allowedProducts);
        }
        return [];
    }

    public function getInternationalFreeShippingSubTotal($store = null): float
    {
        return $this->isInternationalFreeShippingEnabled($store) ?
            (float)$this->scopeConfig->getValue(
                ModuleConfigInterface::CONFIG_XML_PATH_INTERNATIONAL_FREE_SHIPPING_SUBTOTAL,
                ScopeInterface::SCOPE_STORE,
                $store
            ) : 0;
    }

    /**
     * Resolves and flattens product codes separated by ";".
     *
     * @param string $allowedProductsValue The ";" separated list of product codes
     *
     * @return string[]
     *
     * @see InternationalProducts
     */
    private function normalizeAllowedProducts($allowedProductsValue): array
    {
        $combinedKeys = explode(',', $allowedProductsValue) ?: [];

        return array_reduce(
            $combinedKeys,
            function ($carry, $item) {
                $singleKeys = explode(';', $item);
                if ($singleKeys !== false) {
                    $carry = array_merge($carry, $singleKeys);
                }

                return $carry;
            },
            []
        );
    }

    /**
     * Check if delivery time should be displayed in checkout
     *
     * @param string|null $store
     * @return bool
     */
    public function isCheckoutDeliveryTimeEnabled($store = null): bool
    {
        $value = $this->scopeConfig->getValue(
            ModuleConfigInterface::CONFIG_XML_PATH_CHECKOUT_SHOW_DELIVERY_TIME,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        return $value === '1';
    }

    /**
     * Get the cut off time.
     *
     * @param null $store
     * @return string
     */
    public function getTermsOfTrade($store = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_TERMS_OF_TRADE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get terms of trade.
     *
     * @param null $store
     * @return string
     */
    public function getCutOffTime($store = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_CUT_OFF_TIME,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if international rates configuration is enabled
     *
     * @param null $store
     * @return bool
     */
    public function isInternationalRatesConfigurationEnabled($store = null): bool
    {
        $value = $this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_INTERNATIONAL_AFFECT_RATES,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        return $value === '1';
    }

    /**
     * Check if domestic rates configuration is enabled
     *
     * @param null $store
     * @return bool
     */
    public function isDomesticRatesConfigurationEnabled($store = null): bool
    {
        $value = $this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_DOMESTIC_AFFECT_RATES,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        return $value === '1';
    }

    /**
     * Get the general weight unit.
     *
     * @param null $store
     * @return string
     */
    public function getWeightUnit($store = null): string
    {
        $weightUOM = $this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_WEIGHT_UNIT,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        return $this->normalizeWeightUOM($weightUOM);
    }

    /**
     * Get the general dimensions unit.
     *
     * @return string
     */
    public function getDimensionsUOM(): string
    {
        return $this->getDimensionsUOMfromWeightUOM(
            $this->getWeightUnit()
        );
    }

    /**
     * Maps Magento's internal unit names to SDKs unit names
     *
     * @param string $unit
     * @return string
     */
    public function normalizeDimensionUOM($unit): string
    {
        if (array_key_exists($unit, $this->dimensionUnitMap)) {
            return $this->dimensionUnitMap[$unit];
        }

        return $unit;
    }

    /**
     * Maps Magento's internal unit names to SDKs unit names
     *
     * @param string $unit
     * @return string
     */
    public function normalizeWeightUOM($unit): string
    {
        if (array_key_exists($unit, $this->weightUnitMap)) {
            return $this->weightUnitMap[$unit];
        }

        return $unit;
    }

    /**
     * Derives the current dimensions UOM from weight UOM (so both UOMs are in SU or SI format, but always consistent)
     *
     * @param $unit
     * @return string
     */
    private function getDimensionsUOMfromWeightUOM($unit): string
    {
        if (array_key_exists($unit, $this->weightUnitToDimensionUnitMap)) {
            return $this->weightUnitToDimensionUnitMap[$unit];
        }

        return self::DEFAULT_DIMENSION_UNIT;
    }

    /**
     * Checks if route is dutiable by stores origin country and eu country list
     *
     * @param string $receiverCountry
     * @param mixed $store
     * @return bool
     *
     */
    public function isDutiableRoute($receiverCountry, $store = null): bool
    {
        $originCountry = $this->getOriginCountry($store);
        $euCountries = $this->getEuCountries($store);

        $bothEU = \in_array($originCountry, $euCountries, true) && \in_array($receiverCountry, $euCountries, true);

        return $receiverCountry !== $originCountry && !$bothEU;
    }

    /**
     * Returns countries that are marked as EU-Countries
     *
     * @param mixed $store
     * @return string[]
     */
    public function getEuCountries($store = null): array
    {
        $euCountries = $this->scopeConfig->getValue(
            Carrier::XML_PATH_EU_COUNTRIES_LIST,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        return explode(',', $euCountries);
    }

    /**
     * Returns the shipping origin country
     *
     * @see Config
     *
     * @param mixed $store
     * @return string
     */
    public function getOriginCountry($store = null): string
    {
        return (string)$this->scopeConfig->getValue(
            Config::XML_PATH_ORIGIN_COUNTRY_ID,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Returns configured packaging weight for rates calculation.
     *
     * @param mixed $store
     * @return float
     */
    public function getPackagingWeight($store = null): float
    {
        return (float)$this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_PACKAGING_WEIGHT,
            ScopeInterface::SCOPE_WEBSITE,
            $store
        );
    }

    public function getVersion($store = null): string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_VERSION,
            ScopeInterface::SCOPE_WEBSITE,
            $store
        );
    }
}
