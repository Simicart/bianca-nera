<?php

namespace Simi\SimiKnetPayment\Plugin\Sales\Block\Adminhtml\Order;

use Magento\Sales\Block\Adminhtml\Order\View as OrderView;
use Magento\Sales\Model\OrderFactory;

/**
 * Class View
 * @package Simi\SimiKnetPayment\Plugin\Sales\Block\Adminhtml\Order
 */
class View
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * View constructor.
     * @param OrderFactory $orderFactory
     */
    public function __construct(OrderFactory $orderFactory)
    {
        $this->orderFactory = $orderFactory;
    }

    /**
     * @param OrderView $subject
     */
    public function beforeSetLayout(OrderView $subject)
    {
        $order = $this->orderFactory->create()->load($subject->getOrderId());
        $name = $order->getPayment()->getMethod();
        if ($name == 'simiknet') {
            $buttonUrl = $subject->getUrl(
                'simiknet/payment/request',
                ['order_id' => $subject->getOrderId(), 'form_key' => $subject->getFormKey()]
            );

            $subject->addButton(
                'knet_inquiry_button',
                [
                    'label' => __('Knet Inquiry'),
                    'class' => __('custom-button'),
                    'id' => 'order-view-custom-button',
                    'onclick' => 'setLocation(\'' . $buttonUrl . '\')'
                ]
            );
        }
    }
}
