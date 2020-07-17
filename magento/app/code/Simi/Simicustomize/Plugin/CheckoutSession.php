<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Simi\Simicustomize\Plugin;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Class Quote
 * @package Simi\Simicustomize\Plugin
 */
class CheckoutSession
{
    protected $userContext;

    public function __construct(
        UserContextInterface $userContext
    ) {
        $this->userContext = $userContext;
    }

    /**
     * Fix bug load customer from checkout session to quote, see Magento\Checkout\Model\Session::getQuote():288
     * Fix bug invalid state change request
     */
    public function afterGetQuote(
        \Magento\Checkout\Model\Session $session,
        \Magento\Quote\Model\Quote $quote
    ){
        if ($this->userContext->getUserType() == UserContextInterface::USER_TYPE_GUEST) {
            // $customer->setCustomerId(null);
            $quote->setCustomerId(null);
            $quote->setCustomerEmail(null);
            $quote->setCustomerFirstname(null);
            $quote->setCustomerMiddlename(null);
            $quote->setCustomerLastname(null);
            $quote->setCustomerSuffix(null);
            $quote->setCustomerDob(null);
            $quote->setCustomerIsGuest(1);
        }
        return $quote;
    }
}
