<?php

namespace Simi\SimiKnetPayment\Controller\Adminhtml\Payment;

use Magento\Sales\Model\OrderFactory;
use Simi\SimiKnetPayment\Helper\Data;
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Form\FormKey\Validator;

/**
 * Class Request
 * @package Simi\SimiKnetPayment\Controller\Adminhtml\Payment
 */
class Request extends Action
{
    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var Validator
     */
    protected $formKeyValidator;


    /**
     * Request constructor.
     * @param Action\Context $context
     * @param OrderFactory $orderFactory
     * @param Validator $formKeyValidator
     * @param Data $helper
     * @param array $params
     */
    public function __construct(
        Action\Context $context,
        OrderFactory $orderFactory,
        Validator $formKeyValidator,
        Data $helper,
        $params = []
    )
    {
        $this->orderFactory = $orderFactory;
        $this->helper = $helper;
        $this->formKeyValidator = $formKeyValidator;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Unable to process Inquiry Service.'));
            return $this->resultRedirectFactory->create()->setPath(
                'sales/order/view',
                [
                    'order_id' => $params['order_id']
                ]
            );
        }

        if ($params) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $order = $this->orderFactory->create()->load($params['order_id']);

            $payment = $order->getPayment()->getAdditionalInformation();
            $transportalID = $this->helper->getTransportalId();
            $transportalPass = $this->helper->getTransportalPassword();
            $amount = round($order->getGrandTotal(), 3);

            if (array_key_exists('tranid', $payment)) {
                $transId = $payment['tranid'];
                $udf5 = 'TranID';
            } else {
                $transId = $order->getIncrementId();
                $udf5 = 'TrackID';
            }

            $paymentUrl = $this->helper->getInquiryGatewayUrl();


            $xmlData = '<id>' . $transportalID . '</id><password>' . $transportalPass . '</password><action>8</action><amt>' . $amount . '</amt><transid>' . $transId . '</transid><udf5>' . $udf5 . '</udf5><trackid>' . $transId . '</trackid>';

            $ch = curl_init($paymentUrl);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);

            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $output = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);


            $xml = '<div>' . $output . '</div>';
            $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
            $xml = simplexml_load_string($xml);

            $json = json_encode($xml);
            $responseArray = json_decode($json, true); // true to have an array, false for an object

            if(array_key_exists('result', $responseArray)) {
                if ($responseArray['result'] == "CAPTURED" || $responseArray['result'] == "SUCCESS") {
                    $paymentID = $responseArray['payid'];
                    $presult = $responseArray['result'];
                    $tranId = $responseArray['tranid'];
                    $payments = $order->getPayment();
                    $payments->setAdditionalInformation('paymentid', $paymentID);
                    $payments->setAdditionalInformation('result', $presult);
                    $payments->setAdditionalInformation('tranid', $tranId);
                    $payments->setAdditionalInformation('track_id', $order->getIncrementId());

                    $payments->setAdditionalInformation((array)$payments->getAdditionalInformation());
                    $payments->save();
                    $order->save();
                    $this->messageManager->addSuccessMessage(__('You have successfully inquired Knet Service.'));
                }

                if ((trim($responseArray['result']) != "CAPTURED") && (trim($responseArray['result']) != "SUCCESS")) {
                    $this->messageManager->addErrorMessage(__('Your order is not captured via Knet.'));
                }
            }

            return $this->resultRedirectFactory->create()->setPath(
                'sales/order/view',
                [
                    'order_id' => $order->getId()
                ]
            );
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
