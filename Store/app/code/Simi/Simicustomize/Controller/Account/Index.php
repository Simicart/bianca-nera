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
use Magento\Framework\Controller\ResultFactory;

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
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        // Redirect to dashboard pwa studio site
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $linkRedirect = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('simiconnector/general/pwa_studio_url');
        if(isset($linkRedirect)){
            $resultRedirect->setPath($linkRedirect."account.html");
            return $resultRedirect;
        }
    }
}
