<?php
/**
 * Product inventory data validator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simicustomize\Plugin;

class QuoteItemQuantityValidator
{
    public function aroundValidate($subject, $proceed, $observer)
    {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $state = $objectManager->get('Magento\Framework\App\State');
        if ($state->getAreaCode() == \Magento\Framework\App\Area::AREA_WEBAPI_REST) {
	        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	            return;
	        }
	        $uri = '';
	        if (isset($_SERVER['REQUEST_URI']))
	        	$uri = $_SERVER['REQUEST_URI'];
	        if ($uri && $_SERVER['REQUEST_METHOD'] === 'POST') {
	        	if (
			       strpos($uri, 'V1/guest-carts') !== false ||
			       strpos($uri, 'V1/carts/mine') !== false
		       	) {
		        	if (strpos($uri, 'payment-information') === false)
		        		return;
		       	}
	        }

        }
        $result = $proceed($observer);
        return $result;
    }
}
