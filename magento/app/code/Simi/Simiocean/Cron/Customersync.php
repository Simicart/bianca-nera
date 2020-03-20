<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Cron;

class Customersync
{
    /**
     * @var \Simi\Simiocean\Model\Logger
     */
    protected $logger;

    protected $config;
    
    /**
     * @var \Simi\Simiocean\Model\Service\Customer
     */
    protected $customerService;

    /**
     * Constructor.
     * @param \Simi\Simiocean\Model\Logger $logger
     * @param \Simi\Simiocean\Model\Service\Customer $customerService
     */
    public function __construct(
        \Simi\Simiocean\Model\Logger $logger,
        \Simi\Simiocean\Helper\Config $config,
        \Simi\Simiocean\Model\Service\Customer $customerService
    ) {
        $this->logger            = $logger;
        $this->config            = $config;
        $this->customerService   = $customerService;
    }

    public function syncFromOcean()
    {
        if ($this->config->isSyncEnabled()) {
            $this->customerService->syncPull();
            $this->logger->debug(array('Cron: Customer pull run success!'));
            /* try{
            }catch(\Exception $e){
                $this->logger->debug(array('Cron: Customer run fail! '.$e->getMessage()));
            } */
        }
    }

    /**
     * Push website customer to the ocean system
     */
    public function syncFromWebsite()
    {
        if ($this->config->isSyncEnabled()) {
            try{
                $this->customerService->syncPush();
                $this->logger->debug(array('Cron: Customer push run success!'));
            } catch (\Exception $e) {
                $this->logger->debug(array('Cron: Customer push run error.', $e->getMessage()));
            }
        }
    }

    /**
     * Pull the update customer from the ocean system
     */
    public function syncUpdateFromOcean()
    {
        if ($this->config->isSyncEnabled()) {
            try{
                $this->customerService->syncUpdateFromOcean();
                $this->logger->debug(array('Cron: Customer update from ocean run success!'));
            } catch (\Exception $e) {
                $this->logger->debug(array('Cron: Customer update from ocean run error.', $e->getMessage()));
            }
        }
    }

    /**
     * Pull the update customer from website
     */
    public function syncUpdateFromWebsite()
    {
        if ($this->config->isSyncEnabled()) {
            try{
                $this->customerService->syncUpdateFromWebsite();
                $this->logger->debug(array('Cron: Customer update from website run success!'));
            } catch (\Exception $e) {
                $this->logger->debug(array('Cron: Customer update from website run error.', $e->getMessage()));
            }
        }
    }
}
