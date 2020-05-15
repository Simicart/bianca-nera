<?php

namespace Simi\Simicustomize\Controller\Adminhtml\Homesection;

use Magento\Backend\App\Action;

class Edit extends \Magento\Backend\App\Action
{
    public $coreRegistry = null;

    public $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
    
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry     = $registry;
        parent::__construct($context);
    }
    /**
     * Init actions
     *
     * @return $this
     */
    private function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(
            'Simi_Simiconnector::simiconnector_manage'
        )->addBreadcrumb(
            __('Homesection'),
            __('Homesection')
        )->addBreadcrumb(
            __('Manage Homesection'),
            __('Manage Homesection')
        );
        return $resultPage;
    }

    /**
     * Edit CMS page
     *
     * @return void
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id    = $this->getRequest()->getParam('id');
        $simiobjectManager = $this->_objectManager;
        $model = $simiobjectManager->create('Simi\Simicustomize\Model\Homesection');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This homesection no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        // 3. Set entered data if was error when we do save
        $data = $simiobjectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->coreRegistry->register('homesection', $model);

        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Homesection') : __('New Homesection'),
            $id ? __('Edit Homesection') : __('New Homesection')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Homesection'));
        $resultPage->getConfig()->getTitle()
                ->prepend($model->getId() ? $model->getId() : __('New Homesection'));
        return $resultPage;
    }
}
