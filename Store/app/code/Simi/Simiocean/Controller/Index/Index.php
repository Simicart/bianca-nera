<?php

namespace Simi\Simiocean\Controller\Index;

use Magento\Framework\App\Action\Context;


class Index extends \Magento\Framework\App\Action\Action
{
    protected $helper;
    protected $objectManager;
    protected $serviceProduct;
    protected $categoryApi;
    protected $invoiceApi;

    public function __construct(
        Context $context,
        \Simi\Simiocean\Helper\Data $helper,
        \Simi\Simiocean\Model\Service\Product $serviceProduct,
        \Simi\Simiocean\Model\Service\Category $serviceCategory,
        \Simi\Simiocean\Model\Ocean\Category $categoryApi,
        \Simi\Simiocean\Model\Ocean\Invoice $invoiceApi
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->serviceProduct = $serviceProduct;
        $this->serviceCategory = $serviceCategory;
        $this->categoryApi = $categoryApi;
        $this->invoiceApi = $invoiceApi;
        $this->objectManager = $context->getObjectManager();
    }

    public function execute()
    {
        echo '<pre>';

        // $invoice = $this->invoiceApi->getInvoice(37000247);
        // var_dump($invoice);die;
        
        $cate1 = $this->categoryApi->getCategory();
        var_dump($cate1);
        $cate = $this->categoryApi->getSubCategory(1);
        var_dump($cate);die;
        
        $data = $this->serviceProduct->getSimioceanSynced(2577);
        $data->setParentId(2577);
        $data->save();
        var_dump($data->getData());
        exit;
    }
}
