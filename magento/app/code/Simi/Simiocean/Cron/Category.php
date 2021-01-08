<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Cron;

class Category
{
    /**
     * @var \Simi\Simiocean\Model\Logger
     */
    protected $logger;
    protected $config;
    protected $categoryService;

    /**
     * Constructor.
     * @param \Simi\Simiocean\Model\Logger $logger
     * @param \Simi\Simiocean\Model\Service\Invoice $invoiceService
     */
    public function __construct(
        \Simi\Simiocean\Model\Logger $logger,
        \Simi\Simiocean\Helper\Config $config,
        \Simi\Simiocean\Model\Service\Category $categoryService
    ) {
        $this->logger            = $logger;
        $this->config            = $config;
        $this->categoryService   = $categoryService;
    }

    public function syncUpdate()
    {
        if ($this->config->isSyncEnabled()) {
            try{
                $this->categoryService->pullUpdate();
            } catch (\Exception $e) {
                $this->logger->debug(array('Cron: Category pull run error.', $e->getMessage()));
            }
        }
    }

    public function syncPull()
    {
        return false;
        if ($this->config->isSyncEnabled()) {
            try{
                $this->categoryService->syncFromOcean();
                $this->logger->debug(array('Cron: Category pull run success!'));
            } catch (\Exception $e) {
                $this->logger->debug(array('Cron: Category pull run error.', $e->getMessage()));
            }
        }
    }
}
