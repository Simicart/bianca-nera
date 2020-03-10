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
        // test list customer
        // UPDATE
        $data = array(
            //"CustomerID" => 4900378,
            "FirstName" => "Test simi",
            "LastName" => "simi test",
            "HomePhone" => "123456789",
            "MobilePhone" => "123456789",
            "CivilId" => "sample string 6",
            "BirthDate" => "2020-02-24T09:39:58.0840297+03:00",
            "Email" => "simitest@simicart.com",
            "Notes" => "sample string 9",
            "StateID" => 1,
            "StateArName" => "sample string 10",
            "StateEnName" => "sample string 11",
            "TwonID" => 1,
            "TwonArName" => "sample string 12",
            "TwonEnName" => "sample string 13",
            "StreetID" => 1,
            "StreetArName" => "sample string 14",
            "StreetEnName" => "sample string 15",
            "OtherAddressDetails" => "sample string 16",
            "PreferredCategoriesIds" => "sample string 17",
            "PreferredCategoriesArNames" => "sample string 18",
            "PreferredCategoriesEnNames" => "sample string 19",
            "NotifyAboutIDs" => "sample string 20",
            "NotifyAboutArNames" => "sample string 21",
            "NotifyAboutEnNames" => "sample string 22",
            "AreaCode" => 1,
            "BranchID" => "sample string 23",
            "BranchArNames" => "sample string 24",
            "BranchEnNames" => "sample string 25",
            "NationalityID" => 1,
            "NationalityArNames" => "sample string 26",
            "NationalityEnNames" => "sample string 27",
            "CustomerSize" => "sample string 28",
            "MemberID" => "sample string 29",
            "Points" => 1.1,
            "PrPoints" => 1.1,
            "DiscountCardNo" => "sample string 30",
            "DiscountRatio" => 1.1,
            "DiscountCardEndDate" => "2020-02-24T09:39:58.0880332+03:00",
            "PriceType" => 1,
            "PriceTypeArName" => "sample string 32",
            "PriceTypeEnName" => "sample string 33",
            "DiscCardTypeID" => 1,
            "DiscountCardTypeArName" => "sample string 34",
            "DiscountCardTypeEnName" => "sample string 35"
        );

        $data = array(
            // "CustomerID" => 4900390,
            "FirstName" => "Simi",
            "LastName" => "Test",
            "HomePhone" => "",
            "MobilePhone" => 98922560,
            "CivilId" => "",
            "BirthDate" => "0001-01-01T00:00:00",
            "Email" => "",
            "Notes" => "",
            "StateID" => null,
            "StateArName" => "",
            "StateEnName" => "",
            "TwonID" => null,
            "TwonArName" => "",
            "TwonEnName" => "",
            "StreetID" => null,
            "StreetArName" => "",
            "StreetEnName" => "",
            "OtherAddressDetails" => "",
            "PreferredCategoriesIds" => "",
            "PreferredCategoriesArNames" => "",
            "PreferredCategoriesEnNames" => "",
            "NotifyAboutIDs" => "",
            "NotifyAboutArNames" => "",
            "NotifyAboutEnNames" => "",
            "AreaCode" => 0,
            "BranchID" => "49",
            "BranchArNames" => "افا الافينيوز",
            "BranchEnNames" => "AVA AVENUES",
            "NationalityID" => null,
            "NationalityArNames" => "",
            "NationalityEnNames" => "",
            "CustomerSize" => "",
            "MemberID" => "",
            "Points" => 0.0,
            "PrPoints" => 0.0,
            "DiscountCardNo" => "",
            "DiscountRatio" => null,
            "DiscountCardEndDate" => "0001-01-01T00:00:00",
            "PriceType" => null,
            "PriceTypeArName" => "",
            "PriceTypeEnName" => "",
            "DiscCardTypeID" => null,
            "DiscountCardTypeArName" => "",
            "DiscountCardTypeEnName" => ""
        );

        $data = array(
            // "CustomerID" => 3404140,
            "FirstName" => "Simi Update PUT",
            "LastName" => "Test",
            "MobilePhone" => "989225664631",
            "BirthDate" => "0001-01-01T00:00:00",
            "AreaCode" => 0,
            "BranchID" => "49",
            "BranchArNames" => "افا الافينيوز",
            "BranchEnNames" => "AVA AVENUES",
            "Points" => 0,
            "PrPoints" => 0,
            "DiscountCardEndDate" => "0001-01-01T00:00:00"
        );

        // $customers = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Customer')->updateCustomer($data);
        // var_dump($customers);die;
        // $customers = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Customer')->deleteCustomer(3404140);
        // var_dump($customers);die;

        // $customers = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Customer')->addCustomer($data);
        // var_dump($customers);die;

        // $customers = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Customer')->getCustomer("989225664631"); //new customer
        // var_dump($customers);die;
        // $customers = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Customer')->getCustomer("615422145"); //phone
        // var_dump($customers);die;


        $customers = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Customer')->getCustomer('52142');
        print_r($customers);die;
        $customers = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Customer')->getCustomers(40225, 1); //40225
        print_r($customers);die;


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
