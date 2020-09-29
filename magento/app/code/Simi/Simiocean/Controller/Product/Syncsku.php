<?php

namespace Simi\Simiocean\Controller\Product;

use Magento\Framework\App\Action\Context;

class Syncsku extends \Magento\Framework\App\Action\Action
{
    protected $helper;
    protected $serviceProduct;

    public function __construct(
        Context $context,
        \Simi\Simiocean\Helper\Data $helper,
        \Simi\Simiocean\Model\Service\Product $serviceProduct
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->serviceProduct = $serviceProduct;
        $this->objectManager = $context->getObjectManager();
    }

    public function execute()
    {
        echo '<pre>';
        $sku = $this->getRequest()->getParam('sku');
        $skus = explode(',', str_replace(' ', '', $sku));
        try {
            foreach($skus as $sku){
                $this->serviceProduct->syncPullSku($sku);
                // $products = $this->objectManager->get('\Simi\Simiocean\Model\Ocean\Product')->getProductSku($sku);
            }
        }catch(\Exception $e){
            echo "false";
        }
        echo "true";
        exit;
    }
}
