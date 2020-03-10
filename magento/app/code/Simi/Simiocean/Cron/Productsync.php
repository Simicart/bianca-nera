<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Cron;

class Productsync
{
    /**
     * @var \Simi\Simiocean\Model\Logger
     */
    protected $logger;
    
    /**
     * @var \Simi\Simiocean\Model\Service\Product
     */
    protected $productService;

    /**
     * Constructor.
     * @param \Simi\Simiocean\Model\Logger $logger
     * @param \Simi\Simiocean\Model\Service\Product $productService
     */
    public function __construct(
        \Simi\Simiocean\Model\Logger $logger,
        \Simi\Simiocean\Model\Service\Product $productService
    ) {
        $this->logger   = $logger;
        $this->productService   = $productService;
    }

    public function execute()
    {
        $this->productService->process();
        $this->logger->debug(array('Cron: Product sync success!'));
    }
}
