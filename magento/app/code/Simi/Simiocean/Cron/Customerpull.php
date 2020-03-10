<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Cron;

class Customerpull
{
    /**
     * @var \Simi\Simiocean\Model\Logger
     */
    protected $logger;
    
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
        \Simi\Simiocean\Model\Service\Customer $customerService
    ) {
        $this->logger   = $logger;
        $this->customerService   = $customerService;
    }

    public function execute()
    {
        $this->customerService->process();
        $this->logger->debug(array('Cron: Customer pull run success!'));
        /* try{
        }catch(\Exception $e){
            $this->logger->debug(array('Cron: Customer run fail! '.$e->getMessage()));
        } */
    }
}
