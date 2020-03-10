<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model;

use Psr\Log\LoggerInterface;
use Simi\Simiocean\Helper\Config;

class Logger
{
    const CONF_DEBUG = 'simiocean/general/debug';

    /** Object Simi\Simiocean\Helper\Config */
    protected $config;


    public function __construct(
        LoggerInterface $logger,
        Config $config
    ){
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Logs payment related information used for debug
     *
     * @param array $data
     * @param bool|null $forceDebug
     * @return void
     */
    public function debug(array $data, $forceDebug = null)
    {
        $debugOn = $forceDebug !== null ? $forceDebug : $this->isDebugOn();
        if ($debugOn === true) {
            $this->logger->debug(var_export($data, true));
        }
    }

    /**
     * Whether debug is enabled in configuration
     *
     * @return bool
     */
    private function isDebugOn()
    {
        return $this->config && (bool)$this->config->isDebugOn();
    }
}