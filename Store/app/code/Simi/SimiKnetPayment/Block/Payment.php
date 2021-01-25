<?php

namespace Simi\SimiKnetPayment\Block;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * Class Payment
 * @package Simi\SimiKnetPayment\Block
 */
class Payment extends Template
{
    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var
     */
    protected $_orderCollectionFactory;

    /**
     * Payment constructor.
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param CollectionFactory $orderCollectionFactory
     * @param array $data
     */
    public function __construct(Context $context, CheckoutSession $checkoutSession, CollectionFactory $orderCollectionFactory, array $data = [])
    {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        $orderId = $this->_checkoutSession->getLastRealOrderId();
        if (!$orderId) {
            $orderId = $this->_checkoutSession->getKnetOrder();
        }
        return $orderId;
    }

    /**
     * @return mixed
     */
    public function getSorder()
    {
        $this->orders = $this->_orderCollectionFactory->create()->getLastItem()->getIncrementId();
        return $this->orders;
    }
}
