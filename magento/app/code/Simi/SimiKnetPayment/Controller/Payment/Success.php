<?php

namespace Simi\SimiKnetPayment\Controller\Payment;

use Magento\Framework\UrlInterface;
use Simi\SimiKnetPayment\Controller\Main;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

/**
 * Class Success
 * @package Simi\SimiKnetPayment\Controller\Payment
 */
class Success extends Main implements CsrfAwareActionInterface
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\MailException
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $params = $this->getRequest()->getParams();
        $ResErrorText = isset($params['ErrorText']) ? $params['ErrorText'] : '';
        $paymentID = isset($params['paymentid']) ? $params['paymentid'] : '';
        $trackid = isset($params['trackid']) ? $params['trackid'] : '';
        $ResErrorNo = isset($params['Error']) ? $params['Error'] : '';
        $TranData = isset($params['trandata']) ? $params['trandata'] : '';
        $amount = isset($params['amt']) ? $params['amt'] : '';

        $order = $this->orderFactory->create()->loadByIncrementId($trackid);

        $terminalResKey = $this->helper->getResourceKey();

        // $baseUrl = $this->storeManager->getStore($order->getStoreId())->getBaseUrl();

        if ($ResErrorText == null && $ResErrorNo == null) {
            $ResTranData = $TranData;
            if ($ResTranData != null) {
                $decrytedData = $this->helper->decrypt($ResTranData, $terminalResKey);
                $httpResponseAr = explode("&", $decrytedData);

                $httpParsedResponseAr = [];
                foreach ($httpResponseAr as $i => $value) {
                    $tmpAr = explode("=", $value);
                    if (sizeof($tmpAr) > 1) {
                        $httpParsedResponseAr[$tmpAr[0]] = urldecode($tmpAr[1]);
                    }
                }
                $amount = round($order->getGrandTotal(), 3);
                $payment = $order->getPayment();
                $presult = isset($httpParsedResponseAr['result']) ? $httpParsedResponseAr['result'] : '';
                $postdate = isset($httpParsedResponseAr['postdate']) ? $httpParsedResponseAr['postdate'] : '';
                $tranid = isset($httpParsedResponseAr['tranid']) ? $httpParsedResponseAr['tranid'] : '';
                $auth = isset($httpParsedResponseAr['auth']) ? $httpParsedResponseAr['auth'] : '';
                $ref = isset($httpParsedResponseAr['ref']) ? $httpParsedResponseAr['ref'] : '';
                if ($presult == 'CAPTURED') {
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
                    $payment->setTransactionId($tranid);
                    $payment->setLastTransId($tranid);
                    $payment->setAdditionalInformation('paymentid', $paymentID);
                    $payment->setAdditionalInformation('result', $presult);
                    $payment->setAdditionalInformation('tranid', $tranid);
                    $payment->setAdditionalInformation('auth', $auth);
                    $payment->setAdditionalInformation('track_id', $trackid);

                    $payment->setAdditionalInformation((array)$payment->getAdditionalInformation());
                    $trans = $this->transactionBuilder;
                    $transaction = $trans->setPayment($payment)->setOrder($order)->setTransactionId($tranid)->setAdditionalInformation((array)$payment->getAdditionalInformation())->setFailSafe(true)->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

                    $payment->addTransactionCommentsToOrder($transaction, $message);
                    $payment->setParentTransactionId(null);

                    $payment->save();

                    $this->orderSender->notify($order);

                    $order->addStatusHistoryComment(__('Transaction is approved by the bank'), Order::STATE_PROCESSING)->setIsCustomerNotified(true);
                    $this->messageManager->addSuccessMessage(__('Transaction is approved by the bank'));
                    $order->setState(Order::STATE_PROCESSING);
                    $order->setStatus(Order::STATE_PROCESSING);
                    $order->save();

                    $transaction->save();

                    if ($this->helper->isAutoInvoice()) {
                        if (!$order->canInvoice()) {
                            $order->addStatusHistoryComment('Sorry, Order cannot be invoiced.', false);
                        }
                        $invoice = $this->invoiceService->prepareInvoice($order);
                        if (!$invoice) {
                            $order->addStatusHistoryComment('Can\'t generate the invoice right now.', false);
                        }

                        if (!$invoice->getTotalQty()) {
                            $order->addStatusHistoryComment('Can\'t generate an invoice without products.', false);
                        }
                        $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
                        $invoice->register();
                        $invoice->getOrder()->setCustomerNoteNotify(true);
                        $invoice->getOrder()->setIsInProcess(true);
                        $transactionSave = $this->transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
                        $transactionSave->save();

                        try {
                            $this->invoiceSender->send($invoice);
                        } catch (\Magento\Framework\Exception\LocalizedException $e) {
                            $order->addStatusHistoryComment('Can\'t send the invoice Email right now.', false);
                        }

                        $order->addStatusHistoryComment('Automatically Invoice Generated.', false);
                        $order->save();
                    }

                    $result_params = "?paymentid=" . $paymentID . "&amount=" . $amount . "&result=" . $presult . "&tranid=" . $tranid . "&auth=" . $auth . "&ref=" . $ref . "&trackid=" . $trackid . "&postdate=" . $postdate;

                    $resultRedirect = $this->resultRedirectFactory->create();
                    return $resultRedirect->setUrl($this->getPwaStudioUrl() . 'thankyou.html?order_increment_id='.$order->getIncrementId());
                    // $this->_redirect('checkout/onepage/success' . $result_params);
                }
                if ($presult == 'NOT CAPTURED' || $presult == 'NOT+CAPTURED') {
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

                    $payment->setAdditionalInformation('paymentid', $paymentID);
                    $payment->setAdditionalInformation('result', $presult);
                    $payment->setAdditionalInformation('tranid', $tranid);
                    $payment->setAdditionalInformation('auth', $auth);
                    $payment->setAdditionalInformation('track_id', $trackid);

                    $payment->setAdditionalInformation((array)$payment->getAdditionalInformation());

                    $order->addStatusHistoryComment($message, Order::STATE_CANCELED);

                    $order->cancel()->setState(Order::STATE_CANCELED, true, 'Transaction is not approved by the bank');
                    $payment->setStatus('CANCEL');
                    $payment->setShouldCloseParentTransaction(1)->setIsTransactionClosed(1);
                    $payment->save();
                    $this->checkoutSession->restoreQuote();
                    $this->messageManager->addErrorMessage(__('Transaction is not approved by the bank'));
                    $order->save();
                    $result_params = "?paymentid=" . $paymentID . "&amount=" . $amount . "&result=" . $presult . "&tranid=" . $tranid . "&auth=" . $auth . "&ref=" . $ref . "&trackid=" . $trackid . "&postdate=" . $postdate;
                    $this->_redirect('simiknet/payment/fail' . $result_params);
                }
                if ($presult == 'CANCELED') {
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

                    $payment->setAdditionalInformation('paymentid', $paymentID);
                    $payment->setAdditionalInformation('result', $presult);
                    $payment->setAdditionalInformation('tranid', $tranid);
                    $payment->setAdditionalInformation('auth', $auth);
                    $payment->setAdditionalInformation('track_id', $trackid);

                    $payment->setAdditionalInformation((array)$payment->getAdditionalInformation());

                    $order->addStatusHistoryComment($message, Order::STATE_CANCELED);

                    $order->cancel()->setState(Order::STATE_CANCELED, true, 'Transaction is not approved by the bank');
                    $payment->setStatus('CANCEL');
                    $payment->setShouldCloseParentTransaction(1)->setIsTransactionClosed(1);
                    $payment->save();
                    $this->checkoutSession->restoreQuote();
                    $this->messageManager->addErrorMessage(__('Transaction is not approved by the bank.'));
                    $order->save();
                    $result_params = "?paymentid=" . $paymentID . "&amount=" . $amount . "&result=" . $presult . "&tranid=" . $tranid . "&auth=" . $auth . "&ref=" . $ref . "&trackid=" . $trackid . "&postdate=" . $postdate;
                    $this->_redirect('simiknet/payment/fail' . $result_params);
                }
            }
        } else {
            $result_params = "?ErrorText=" . $ResErrorText . "&trackid=" . $trackid . "&amt=" . $amount . "&paymentid=" . $paymentID;
            $this->_redirect('simiknet/payment/cancel' . $result_params);
        }
    }
    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
