<?php


namespace Simi\Simicustomize\Observer;

use Magento\Framework\Event\ObserverInterface;

class PaymentMethodIsActive implements ObserverInterface {

    public $simiObjectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $simiObjectManager
    ) {
        $this->simiObjectManager = $simiObjectManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $method = $observer['method_instance'];
        $result = $observer->getEvent()->getResult();
        if (!isset( $observer['quote']) || !$observer['quote'])
            return;
        $quote = $observer['quote'];
        if (
            $quote->getData('is_virtual') ||
            $this->simiObjectManager->get('Simi\Simicustomize\Helper\SpecialOrder')->isQuotePreOrder($quote) ||
            $this->simiObjectManager->get('Simi\Simicustomize\Helper\SpecialOrder')->isQuoteTryToBuy($quote)
        ) {
            if ($method->getCode() == 'cashondelivery') {
                $result->setData('is_available', false);
            } else if ($method->getCode() == 'banktransfer') {
                $result->setData('is_available', false);
            } else if ($method->getCode() == 'checkmo') {
                $result->setData('is_available', false);
            }
        }
        if ($method->getCode() == 'paypal_express_bml') {
            $result->setData('is_available', false);
        }
        if ($quote->getData('grand_total') == 0) {
            if ($method->getCode() != 'free')
                $result->setData('is_available', false);
        }
    }

}
