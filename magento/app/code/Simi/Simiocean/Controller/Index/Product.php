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
        $productApi = $this->objectManager->get('Simi\Simiocean\Model\Ocean\Product');
        $from = $this->getRequest()->getParam('from');
        $to = $this->getRequest()->getParam('to');
        $page = $this->getRequest()->getParam('page');
        $size = $this->getRequest()->getParam('size');
        if ($from && $to) {
            $dateFrom = new \DateTime($from, new \DateTimeZone('UTC'));
            $dateFromParam = $dateFrom->getTimestamp();
            $dateTo = new \DateTime($to, new \DateTimeZone('UTC'));
            $dateToParam = $dateTo->getTimestamp();
            $products = $productApi->getProductFilter($dateFromParam, $dateToParam, $page ?? 1, $size ?? 10);
            var_dump($from);
            var_dump($to);
            print_r($products);die;
        }

        $sku = $this->getRequest()->getParam('sku');
        $products = $productApi->getProductSku($sku);
        print_r($products);die;

        // $date = new \DateTime('0000-12-31', new \DateTimeZone('UTC'));
        // $date2 = new \DateTime('0001-01-01', new \DateTimeZone('UTC'));
        // var_dump($date < $date2);
        // $birthDate = $date->format('c');
        // var_dump($birthDate);
        // var_dump($date2->format('c'));die;

        // $date = new \DateTime('now', new \DateTimeZone('UTC'));
        // $date = new \DateTime('now', new \DateTimeZone('Asia/Kuwait'));
        // var_dump($date->getTimestamp());die;
        // $date->setTimestamp(1550738985);
        // var_dump($date->format('Y-m-d H:i:s'));
        // var_dump(gmdate('Y-m-d H:i:s', 1550738985 - 10800));
        // die;

        // var_dump(localtime(1550738985));die; //convert ISO 8601 to GMT date
        // var_dump(date('Y-m-d H:i:s', 1550738985));die; //convert ISO 8601 to GMT date
        // var_dump(date('Y-m-d H:i:s', strtotime('2019-05-14T12:42:23')));die; //convert ISO 8601 to GMT date

        // $products = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Product')->getProductList(1, 2);
        // var_dump('FromDate: 1569423462');
        // var_dump('ToDate: '.$date->getTimestamp());
        // $products = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Product')->getProductFilter(1569423462, '', 567, 10);
        // var_dump($products);die;

        // $products = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Product')->getProductSku('19151009');
        // print_r($products);die;

        // echo '<pre>';
        // $productService = $this->objectManager->get('\Simi\Simiocean\Model\Service\Product');
        // $productService->process();
        // exit;

        /* $rows = $this->objectManager->get('\Simi\Simiocean\Model\SyncTable')->getCollection();
        $rows->addFieldToFilter('type', 'product');
        $i = 0;
        var_dump(count($rows));
        foreach($rows as $r){
            if (($r->getPageNum() - $i) > 1) {
                var_dump($r->getData());
                return;
            }
            $i = $r->getPageNum();
        }
        print_r('ok test');die; */


        // Find these skus existing on the ocean system and log data
        /* $logger = $this->objectManager->get('\Simi\Simiocean\Model\Logger');
        $skus = [2031028, 2042028, 2062028, 2060028, 20181008, 20181009, 20181010];
        for($i = 601; $i <= 750; $i++){
            $products = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Product')->getProductList($i, 10);
            foreach($products as $p){
                if (in_array($p['SKU'], $skus)) {
                    var_dump($p);
                    $logger->debug(array('Check product result', print_r($p)));
                    die;
                }
            }
            $logger->debug('Check product page: '. $i);
        }
        var_dump('no product.');die; */

        // Get product by page, size
        // $products = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Product')->getProductList(143, 10);
        // print_r($products);die;
        
        // $products = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Product')->getProductSku('2031028');
        // print_r($products);die;

        // Get product by year, page, size
        // $products = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Product')->getProducts(2020, 1, 100);
        // var_dump($products);die;

        // Get product From date to date
        // FromDate
        $dateFrom = new \DateTime('2020-09-20 00:00:00', new \DateTimeZone('UTC'));
        $dateFromParam = $dateFrom->getTimestamp();
        // $dateFromParam = 1600586011;
        // ToDate
        $dateTo = new \DateTime('2020-09-21 00:00:00', new \DateTimeZone('UTC'));
        $dateToParam = $dateTo->getTimestamp();
        // $dateToParam = 1600586011 +  86400;
        $products = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Product')->getProductFilter($dateFromParam, $dateToParam, 1, 100);
        var_dump($products);die;
        

        // Decrypt post data
        $requestBody = $this->getRequest()->getContent();
        $data = $this->helper->decrypt($requestBody);
        echo $data;
        exit;
    }
}
