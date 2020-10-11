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
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
