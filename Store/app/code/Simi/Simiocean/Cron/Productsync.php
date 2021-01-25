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
    protected $config;
    
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
        \Simi\Simiocean\Helper\Config $config,
        \Simi\Simiocean\Model\Service\Product $productService
    ) {
        $this->logger           = $logger;
        $this->config           = $config;
        $this->productService   = $productService;
    }

    public function syncPull()
    {
        return false;

        if ($this->config->isSyncEnabled()) {
            $this->productService->syncPull();
            $this->logger->debug(array('Cron: Product sync success!'));
        }
        return true;
    }

    public function syncPullUpdate()
    {
        if ($this->config->isSyncEnabled()) {
            $this->productService->syncUpdatePull();
            $this->logger->debug(array('Cron: Product sync pull update success!'));
        }
        
        // Update product by skus before gmt date 2020-10-13 00:00:00 (changed name)
        // $this->productService->resyncAll();
        return true;
    }

    public function syncPullUpdateCustom()
    {
        if ($this->config->isSyncEnabled()) {
            $this->productService->syncUpdatePullCustom();
            // $this->logger->debug(array('Cron: Product sync pull update success!'));
        }
        return true;
    }

    public function syncUpdateStock()
    {
        if ($this->config->isSyncEnabled()) {
            $this->productService->syncUpdateStock();
            // $this->logger->debug(array('Cron: Product sync update stock success!'));
        }
        return true;
    }
}
