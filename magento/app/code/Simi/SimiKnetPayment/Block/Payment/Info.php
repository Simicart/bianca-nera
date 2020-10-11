<?php

namespace Simi\SimiKnetPayment\Block\Payment;

use Magento\Payment\Block\ConfigurableInfo;

/**
 * Class Info
 * @package Simi\SimiKnetPayment\Block\Payment
 */
class Info extends ConfigurableInfo
{
    /**
     * @var string
     */
    protected $_template = 'Simi_SimiKnetPayment::payment/info.phtml';

    /**
     * @param string $field
     * @return \Magento\Framework\Phrase|string
     */
    public function getLabel($field)
    {
        switch ($field) {
            case 'method_title':
                return __('Method Title');
            case 'paymentid':
                return __('Payment Id');
            case 'result':
                return __('Transaction Status');
            case 'tranid':
                return __('Transaction ID');
            case 'auth':
                return __('Auth Number');
            case 'track_id':
                return __('Tracking Id');
            default:
                return parent::getLabel($field);
        }
    }
}
