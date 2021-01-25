<?php

namespace Simi\SimiKnetPayment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Checkout\Model\Session;

/**
 * Class KnetConfigProvider
 * @package Simi\SimiKnetPayment\Model
 */
class KnetConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var
     */
    protected $config;

    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var array
     */
    protected $methods = [];

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * KnetConfigProvider constructor.
     * @param ResolverInterface $localeResolver
     * @param CurrentCustomer $currentCustomer
     * @param Session $checkoutSession
     * @param PaymentHelper $paymentHelper
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(ResolverInterface $localeResolver, CurrentCustomer $currentCustomer, Session $checkoutSession, PaymentHelper $paymentHelper)
    {
        $this->localeResolver = $localeResolver;
        $this->currentCustomer = $currentCustomer;
        $this->paymentHelper = $paymentHelper;
        $this->checkoutSession = $checkoutSession;

        $code = 'simiknet';
        $this->methods[$code] = $this->paymentHelper->getMethodInstance($code);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {
        $code = 'simiknet';
        $config = [];

        if ($this->methods[$code]->isAvailable($this->checkoutSession->getQuote())) {
            $config = [];
            $config['payment'] = [];
            $config['payment']['simiknet']['redirectUrl'] = [];
            $config['payment']['simiknet']['redirectUrl'][$code] = $this->getMethodRedirectUrl($code);
            $config['payment']['simiknet'][$code]['instructions'] = $this->methods[$code]->getInstructions();
        }

        return $config;
    }

    /**
     * @param $code
     * @return mixed
     */
    protected function getMethodRedirectUrl($code)
    {
        return $this->methods[$code]->getOrderPlaceRedirectUrl();
    }
}
