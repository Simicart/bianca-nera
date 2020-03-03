<?php
/**
 *
 * Copyright © Simicart 2020, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Simi\Simicustomize\Controller\Account;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Customer\Controller\AbstractAccount implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Redirect from vendor dashboard to PWA site.
     *
     * 
     */
    public function execute()
    {
        // Redirect to dashboard pwa studio site
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $linkRedirect = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('simiconnector/url_logout/url');
        header("Location: {$linkRedirect}account.html");
        exit;
    }
}
