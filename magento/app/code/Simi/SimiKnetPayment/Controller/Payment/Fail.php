<?php

namespace Simi\SimiKnetPayment\Controller\Payment;

use Simi\SimiKnetPayment\Controller\Main;

/**
 * Class Fail
 * @package Simi\SimiKnetPayment\Controller\Payment
 */
class Fail extends Main
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $errorMsg = __('An error occurred while making the transaction. Please try again.');
        $params = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        $query = http_build_query($params);
        return $resultRedirect->setUrl($this->getPwaStudioUrl() . 'cart.html?payment=false&'.$query.'&errorMsg='.$errorMsg);
        // $resultPage = $this->resultPageFactory->create();
        // return $resultPage;
    }
}
