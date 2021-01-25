<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Simi\Simicustomize\Api;

interface InstagramInterface
{
    /**
     * @return string
     */
    public function auth();

    /**
     * @param string $code
     * @return boolean
     */
    public function getAccessToken($code);
}