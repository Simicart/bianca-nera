<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Cron;

class Invoicesync
{
    /**
     * @var \Simi\Simiocean\Model\Logger
     */
    protected $logger;
    protected $config;
    protected $invoiceService;

    /**
     * Constructor.
     * @param \Simi\Simiocean\Model\Logger $logger
     * @param \Simi\Simiocean\Model\Service\Invoice $invoiceService
     */
    public function __construct(
        \Simi\Simiocean\Model\Logger $logger,
        \Simi\Simiocean\Helper\Config $config,
        \Simi\Simiocean\Model\Service\Invoice $invoiceService
    ) {
        $this->logger           = $logger;
        $this->config           = $config;
        $this->invoiceService   = $invoiceService;
    }

    public function syncPush()
    {
        if ($this->config->isSyncEnabled()) {
            try{
                $this->invoiceService->syncPush();
                $this->invoiceService->syncCancel();
                $this->logger->debug(array('Cron: Invoice push run success!'));
            } catch (\Exception $e) {
                $this->logger->debug(array('Cron: Invoice push run error.', $e->getMessage()));
            }
        }
    }
}
