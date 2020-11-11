<?php

namespace Simi\SimiKnetPayment\Model;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Payment\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Url;
use Magento\Payment\Block\Form;
use Simi\SimiKnetPayment\Block\Payment\Info;
use Magento\Payment\Model\InfoInterface;

/**
 * Class Payment
 * @package Simi\SimiKnetPayment\Model
 */
class Payment extends AbstractMethod
{
    /**
     *
     */
    const CODE = 'simiknet';
    /**
     * @var string
     */
    protected $_code = self::CODE;
    /**
     * @var string
     */
    protected $_formBlockType = Form::class;
    /**
     * @var string
     */
    protected $_infoBlockType = Info::class;
    /**
     * @var bool
     */
    protected $_isGateway = true;
    /**
     * @var bool
     */
    protected $_canAuthorize = true;
    /**
     * @var bool
     */
    protected $_canCapture = true;
    /**
     * @var bool
     */
    protected $_canCapturePartial = false;
    /**
     * @var bool
     */
    protected $_canRefund = true;
    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;
    /**
     * @var bool
     */
    protected $_canVoid = true;
    /**
     * @var bool
     */
    protected $_canUseInternal = false;
    /**
     * @var bool
     */
    protected $_canUseCheckout = true;
    /**
     * @var bool
     */
    protected $_canUseForMultishipping = false;
    /**
     * @var bool
     */
    protected $_canSaveCc = false;


    /**
     * @var Url
     */
    protected $urlBuilder;
    /**
     * @var null
     */
    protected $paymentData = null;
    /**
     * @var
     */
    protected $moduleList;
    /**
     * @var
     */
    protected $checkoutSession;
    /**
     * @var
     */
    protected $orderFactory;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var
     */
    protected $region;
    /**
     * @var
     */
    protected $country;

    /**
     * Payment constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param Url $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param StoreManagerInterface $storeManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(Context $context, Registry $registry, ExtensionAttributesFactory $extensionFactory, AttributeValueFactory $customAttributeFactory, Data $paymentData, Url $urlBuilder, ScopeConfigInterface $scopeConfig, Logger $logger, StoreManagerInterface $storeManager, AbstractResource $resource = null, AbstractDb $resourceCollection = null, array $data = [])
    {
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->logger = $logger;

        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger, $resource, $resourceCollection, $data);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getInstructions()
    {
        return __('You will be redirected to the Knet website when you place an order.');
    }

    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this|AbstractMethod
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->canOrder()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The order action is not available.'));
        }

        return $this;
    }

    /**
     * @param $field
     * @param null $storeId
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfig($field, $storeId = null)
    {
        if ('order_place_redirect_url' === $field) {
            return $this->getOrderPlaceRedirectUrl();
        }

        $path = 'payment/' . $this->getCode() . '/' . $field;
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     *  Return Order Place Redirect URL
     *
     * @return      string Order Redirect URL
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->urlBuilder->getUrl('simiknet/payment/redirect', ['_secure' => true]);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCode()
    {
        if (empty($this->_code)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('We cannot retrieve the payment method code.'));
        }
        return $this->_code;
    }

    /**
     * @param CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(CartInterface $quote = null)
    {
        return parent::isAvailable($quote);
    }

    /**
     * @return AbstractMethod
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate()
    {
        return parent::validate();
    }

    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @return AbstractMethod
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        return parent::authorize($payment, $amount);
    }

    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @return AbstractMethod
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function capture(InfoInterface $payment, $amount)
    {
        return parent::capture($payment, $amount);
    }

    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @return AbstractMethod
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(InfoInterface $payment, $amount)
    {
        return parent::refund($payment, $amount);
    }

    public function canUseForCurrency($currencyCode){
        $currentCurrency = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        if ($currentCurrency !== $this->getConfig('currency')) {
            return false;
        }
        // if ($currencyCode !== $this->getConfig('currency')) {
        //     return false;
        // }
        return true;
    }
}
