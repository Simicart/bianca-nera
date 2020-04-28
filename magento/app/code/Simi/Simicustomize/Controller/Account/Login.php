<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Simi\Simicustomize\Controller\Account;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Controller\AbstractAccount;

/**
 * Login form page. Accepts POST for backward compatibility reasons.
 */
class Login extends AbstractAccount implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory
    ) {
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Customer login form page
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // Redirect to pwa site
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $linkRedirect = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('simiconnector/general/pwa_studio_url');
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        if ($this->session->isLoggedIn()) {
            if(isset($linkRedirect)){
                $resultRedirect->setPath($linkRedirect);
                return $resultRedirect;
            }
        }

        if(isset($linkRedirect)){
            $resultRedirect->setPath($linkRedirect."login.html");
            return $resultRedirect;
        }
    }
}