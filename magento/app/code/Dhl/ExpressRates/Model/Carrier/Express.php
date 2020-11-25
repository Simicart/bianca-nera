<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\ExpressRates\Model\Carrier;

use Dhl\ExpressRates\Model\Config\ModuleConfigInterface;
use Dhl\ExpressRates\Model\Rate\CheckoutProvider as RateProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Item;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

class Express extends AbstractCarrier implements CarrierInterface
{
    public const CARRIER_CODE = 'dhlexpress';

    /**
     * @var string
     */
    protected $_code = self::CARRIER_CODE;

    /**
     * @var ResultFactory
     */
    private $rateFactory;

    /**
     * @var RateProvider
     */
    private $rateProvider;

    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * Express constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateFactory
     * @param RateProvider $rateProvider
     * @param ModuleConfigInterface $moduleConfig
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateFactory,
        RateProvider $rateProvider,
        ModuleConfigInterface $moduleConfig,
        array $data = []
    ) {
        $this->rateFactory = $rateFactory;
        $this->rateProvider = $rateProvider;
        $this->moduleConfig = $moduleConfig;

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->moduleConfig->isEnabled($this->getStore())) {
            return false;
        }

        try {
            return $this->rateProvider->getRates($request);
        } catch (LocalizedException $e) {
            $this->_logger->error($e->getMessage(), ['exception' => $e->getPrevious() ?: $e]);
            $result = $this->rateFactory->create();
            $result->append($this->getErrorMessage());

            return $result;
        }
    }

    /**
     * Perform additional validation:
     *  - check shipping origin being valid
     *  - check for weightless items (will result in error)
     *
     * @param DataObject|RateRequest $request
     * @return bool|DataObject|AbstractCarrierOnline
     */
    public function processAdditionalValidation(DataObject $request)
    {
        $errorMsg = false;

        if (!$this->validateItemWeight($request)) {
            $errorMsg = __('Some items have no configured weight.');
        }

        if ($errorMsg) {
            $this->_logger->error($errorMsg);
            return $this->getErrorMessage();
        }

        return parent::processAdditionalValidation($request);
    }

    /**
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return [];
    }

    /**
     * Get error messages
     *
     * @return bool|Error
     */
    private function getErrorMessage()
    {
        $storeId = $this->getStore();
        if ($this->moduleConfig->showIfNotApplicable($storeId)) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->getCarrierCode());
            $error->setCarrierTitle($this->moduleConfig->getTitle($storeId));
            $error->setErrorMessage($this->moduleConfig->getNotApplicableErrorMessage($storeId));

            return $error;
        }

        return false;
    }

    /**
     * Check if all request items have a weight configured
     *
     * @param DataObject|RateRequest $request
     * @return bool
     */
    private function validateItemWeight(DataObject $request): bool
    {
        /** @var $item Item */
        foreach ($request->getAllItems() as $item) {
            $product = $item->getProduct();
            if ($product && $product->getWeight()) {
                // we have weight, continue
                continue;
            }
            if ($product && !$product->isVirtual()) {
                // no weight and product is not virtual!

                return false;
            }
        }

        return true;
    }
}
