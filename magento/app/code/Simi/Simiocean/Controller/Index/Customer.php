<?php

namespace Simi\Simiocean\Controller\Index;

use Magento\Framework\App\Action\Context;


class Customer extends \Magento\Framework\App\Action\Action
{
    protected $helper;
    protected $objectManager;

    public function __construct(
        Context $context,
        \Simi\Simiocean\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->objectManager = $context->getObjectManager();
    }

    public function execute()
    {
        echo '<pre>';
        // $data = array(
        //     "CustomerID" => 200008,
        //     "FirstName" => "Test 5",
        //     "LastName" => null,
        //     "MobilePhone" => '99224210',
        //     "AreaCode" => 965
        // );

        $data = array(
            "CustomerID" => 900605,
            "FirstName" => "Test name 3",
            "MobilePhone" => '0046739775347',
            "AreaCode" => 1
        );

        $customers = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Customer')->getCustomer("0509464764");
        var_dump($customers);die;
        // $customers = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Customer')->updateCustomer($data);
        // var_dump($customers);die;

        // check customer dupplicate phone number
        $customers = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Customer')->getCustomers(215, 10); //40225
        print_r($customers);die;

        // $customers = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Customer')->getCustomer("50668888");
        // var_dump($customers);die;

        // $customers = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Customer')->deleteCustomer(3404140);
        // var_dump($customers);die;

        // $customers = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Customer')->addCustomer($data);
        // var_dump($customers);die;

        // $customers = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Customer')->getCustomer("989225664631"); //new customer
        // var_dump($customers);die;
        // $customers = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Customer')->getCustomer("615422145"); //phone
        // var_dump($customers);die;

        


        $products = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Product')->getProductList(1, 5);
        print_r($products);die;
        
        $products = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Product')->getProductSku('19151009');
        print_r($products);die;

        // $products = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Product')->getProducts(2019, 100, 1);
        // var_dump($products);die;
        
        $requestBody = $this->getRequest()->getContent();
        $data = $this->helper->decrypt($requestBody);
        echo $data;
        exit;
    }
}
