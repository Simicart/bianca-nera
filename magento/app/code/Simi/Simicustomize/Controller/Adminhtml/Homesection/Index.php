<?php

namespace Simi\Simicustomize\Controller\Adminhtml\Homesection;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{

    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
   
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    /**
     * Simiconnector List action
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(
            'Simi_Simiconnector::simiconnector_manage'
        )->addBreadcrumb(
            __('Home Section'),
            __('Home Section')
        )->addBreadcrumb(
            __('Manage Home Section'),
            __('Manage Home Section')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Home Section'));
        return $resultPage;
    }
}
