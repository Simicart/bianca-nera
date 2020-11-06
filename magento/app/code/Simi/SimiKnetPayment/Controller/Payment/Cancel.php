<?php

namespace Simi\SimiKnetPayment\Controller\Payment;

use Simi\SimiKnetPayment\Controller\Main;
use Magento\Sales\Model\Order;

/**
 * Class Cancel
 * @package Simi\SimiKnetPayment\Controller\Payment
 */
class Cancel extends Main
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();
        $errorMsg = __('Transaction was not Successful. Your order was not Completed Please try again later');

        $paymentID = isset($params['paymentid']) ? $params['paymentid'] : '';
        $presult = isset($params['result']) ? $params['result'] : '';
        $postdate = isset($params['postdate']) ? $params['postdate'] : '';
        $tranid = isset($params['tranid']) ? $params['tranid'] : '';
        $auth = isset($params['auth']) ? $params['auth'] : '';
        $ref = isset($params['ref']) ? $params['ref'] : '';
        $trackid = isset($params['trackid']) ? $params['trackid'] : '';

        $order = $this->orderFactory->create()->loadByIncrementId($trackid);
        $amount = round($order->getGrandTotal(), 3);

        $message = 'KNET Payment Details:<br/>';
        if ($paymentID) {
            $message .= 'PaymentID: ' . $paymentID . "<br/>";
        }
        if ($amount) {
            $message .= 'Amount: ' . $amount . "<br/>";
        }
        if ($presult) {
            $message .= 'Result: ' . $presult . "<br/>";
        }
        if ($postdate) {
            $message .= 'PostDate: ' . $postdate . "<br/>";
        }
        if ($tranid) {
            $message .= 'TranID: ' . $tranid . "<br/>";
        }
        if ($auth) {
            $message .= 'Auth: ' . $auth . "<br/>";
        }
        if ($ref) {
            $message .= 'Ref: ' . $ref . "<br/>";
        }
        if ($trackid) {
            $message .= 'TrackID: ' . $trackid . "<br/>";
        }
        $payment = $order->getPayment();
        $order->cancel()->setState(Order::STATE_CANCELED, true, 'Gateway has declined the payment.');
        $payment->setStatus('DECLINED');
        $payment->setShouldCloseParentTransaction(1)->setIsTransactionClosed(1);
        $payment->save();
        $order->setStatus(Order::STATE_CANCELED);
        $order->addStatusToHistory($order->getStatus(), $message);
        $this->messageManager->addErrorMessage($errorMsg);
        $this->checkoutSession->restoreQuote();
        $order->save();
        
        // return $resultRedirect->setPath('checkout/cart');
        $query = http_build_query($params);
        return $resultRedirect->setUrl($this->getPwaStudioUrl() . 'cart.html?payment=false&'.$query.'&errorMsg='.$errorMsg);
    }
}
