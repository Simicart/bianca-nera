<?php

namespace Simi\Simiocean\Controller\Product;

use Magento\Framework\App\Action\Context;

class Pull extends \Magento\Framework\App\Action\Action
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
        $data = $this->serviceProduct->syncPull();
        var_dump($data);
        exit;
    }
}
