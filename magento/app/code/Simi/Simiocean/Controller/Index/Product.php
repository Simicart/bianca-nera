<?php

namespace Simi\Simiocean\Controller\Index;

use Magento\Framework\App\Action\Context;


class Product extends \Magento\Framework\App\Action\Action
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
        // $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $date = new \DateTime('now', new \DateTimeZone('Asia/Kuwait'));
        // var_dump($date->getTimestamp());die;
        // $date->setTimestamp(1550738985);
        // var_dump($date->format('Y-m-d H:i:s'));
        // var_dump(gmdate('Y-m-d H:i:s', 1550738985 - 10800));
        // die;

        // var_dump(localtime(1550738985));die; //convert ISO 8601 to GMT date
        // var_dump(date('Y-m-d H:i:s', 1550738985));die; //convert ISO 8601 to GMT date
        // var_dump(date('Y-m-d H:i:s', strtotime('2019-05-14T12:42:23')));die; //convert ISO 8601 to GMT date

        // $products = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Product')->getProductList(1, 2);
        var_dump('FromDate: 1569423462');
        var_dump('ToDate: '.$date->getTimestamp());
        $products = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Product')->getProductFilter(1569423462, '', 567, 10);
        var_dump($products);die;

        $products = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Product')->getProductSku('19151009');
        print_r($products);die;

        echo '<pre>';
        $productService = $this->objectManager->get('\Simi\Simiocean\Model\Service\Product');
        $productService->process();
        exit;

        echo '<pre>';
        $products = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Product')->getProductList(50, 2);
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
