<?php

namespace Simi\Simiocean\Controller\Category;

class Update extends \Magento\Framework\App\Action\Action
{
    protected $helper;
    protected $serviceCategory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Simi\Simiocean\Helper\Data $helper,
        \Simi\Simiocean\Model\Service\Category $serviceCategory
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->serviceCategory = $serviceCategory;
    }

    public function execute()
    {
        echo '<pre>';
        $data = $this->serviceCategory->pullUpdate();
        var_dump($data);
        exit;
    }
}
