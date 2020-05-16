<?php

namespace Simi\Simicustomize\Block\Adminhtml\Homesection\Edit\Tab;

/**
 * Cms page edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    public $simiObjectManager;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    public $systemStore;

    /**
     * @var \Simi\Simicustomize\Helper\Website
     * */
    public $websiteHelper;

    /**
     * @var \Simi\Simicustomize\Model\Homesection
     */
    public $homesectionFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    public $jsonEncoder;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    public $categoryFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Simi\Simiconnector\Helper\Website $websiteHelper,
        \Simi\Simicustomize\Model\HomesectionFactory $homesectionFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\ObjectManagerInterface $simiObjectManager,
        array $data = []
    ) {
   
        $this->simiObjectManager = $simiObjectManager;
        $this->homesectionFactory     = $homesectionFactory;
        $this->websiteHelper     = $websiteHelper;
        $this->systemStore       = $systemStore;
        $this->jsonEncoder       = $jsonEncoder;
        $this->categoryFactory   = $categoryFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    public function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('homesection');

        /*
         * Checking if user have permissions to save information
         */
        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('');
        $htmlIdPrefix = $form->getHtmlIdPrefix();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Homesection Information')]);

        $data = $model->getData();

        if (isset($data['type_value_1'])) {
            $data['type_value_1_product'] = $data['type_value_1_category'] = $data['type_value_1_url'] = $data['type_value_1'];
        }
        if (isset($data['type_value_1'])) {
            $data['type_value_2_product'] = $data['type_value_2_category'] = $data['type_value_2_url'] = $data['type_value_2'];
        }

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
            $simicustomizehelper = $this->simiObjectManager->get('Simi\Simiconnector\Helper\Data');
            $typeID              = $simicustomizehelper->getVisibilityTypeId('homesection');
            $visibleStoreViews   = $this->simiObjectManager
                    ->create('Simi\Simiconnector\Model\Visibility')->getCollection()
                    ->addFieldToFilter('content_type', $typeID)
                    ->addFieldToFilter('item_id', $model->getId());
            $storeIdArray        = [];

            foreach ($visibleStoreViews as $visibilityItem) {
                $storeIdArray[] = $visibilityItem->getData('store_view_id');
            }
            $data['storeview_id'] = implode(',', $storeIdArray);
        }

        $storeResourceModel = $this->simiObjectManager
                ->create('Simi\Simiconnector\Model\ResourceModel\Storeviewmultiselect');

        $fieldset->addField('storeview_id', 'multiselect', [
            'name'     => 'storeview_id[]',
            'label'    => __('Store View'),
            'title'    => __('Store View'),
            'required' => true,
            'values'   => $storeResourceModel->toOptionArray(),
        ]);

        $fieldset->addField(
            'section_name',
            'text',
            [
            'name'     => 'section_name',
            'label'    => __('Title'),
            'title'    => __('Title'),
            'required' => true,
            'disabled' => $isElementDisabled
                ]
        );
        if (!isset($data['sort_order'])) {
            $data['sort_order'] = 1;
        }
        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name'     => 'sort_order',
                'label'    => __('Sort Order'),
                'title'    => __('Sort Order'),
                'class'    => 'validate-not-negative-number',
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'name'     => 'status',
                'label'    => __('Status'),
                'title'    => __('Status'),
                'required' => false,
                'disabled' => $isElementDisabled,
                'options'  => $this->homesectionFactory->create()->toOptionStatusHash(),
            ]
        );

        /** Add fieldset 2 */
        $fieldset2 = $form->addFieldset('image_fieldset', ['legend' => __('Left Images')]);

        $fieldset2->addField(
            'type',
            'select',
            [
                'name'     => 'type',
                'label'    => __('Direct viewers to'),
                'title'    => __('Direct viewers to'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'options'  => $this->homesectionFactory->create()->toOptionTypeHash(),
                'onchange' => 'changeType(this.value)',
            ]
        );

        $fieldset2->addField('image_left_1', 'image', 
            [
                'name'     => 'image_left_1',
                'label'    => __('Image 1 (width:572px, height:362px), (width:768px, height:362px), (width:375px, height:362px)'),
                'title'    => __('Image 1 (width:572px, height:362px), (width:768px, height:362px), (width:375px, height:362px)'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset2->addField('image_left_1_mobile', 'image', 
            [
                'name'     => 'image_left_1_mobile',
                'label'    => __('Image 1 mobile (width:343px, height:218px), (width:225px, height:106px), (width:106px, height:106px)'),
                'title'    => __('Image 1 mobile (width:343px, height:218px), (width:225px, height:106px), (width:106px, height:106px)'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        /* 1: product + category + url */
        $fieldset2->addField(
            'type_value_1_product',
            'text',
            [
                'name'               => 'type_value_1_product',
                'label'              => __('Product ID (Image 1)'),
                'title'              => __('Product ID (Image 1)'),
                'required'           => true,
                'disabled'           => $isElementDisabled,
                // 'class'              => 'validate-number',
                'after_element_html' => '<a href="#" title="Show Product Grid" onclick="toogleProduct(\'product_grid_type_1\', \'show_product_grid_type_1\');return false;">'
                    . '<img id="show_product_grid_type_1" src="'
                    . $this->getViewFileUrl('Simi_Simiconnector::images/arrow_down.png')
                    . '" title="" /></a>'
                    . $this->getLayout()->createBlock('Simi\Simicustomize\Block\Adminhtml\Homesection\Edit\Tab\Productgridtype1')
                    // ->setId('product_grid_type_1')
                    ->toHtml()
            ]
        );
        $fieldset2->addField('type_value_1_category', 'select', [
            'name'     => 'type_value_1_category',
            'label'    => __('Category (Image 1)'),
            'title'    => __('Category (Image 1)'),
            'required' => true,
            'values'   => $this->simiObjectManager->get('Simi\Simiconnector\Helper\Catetree')->getChildCatArray(),
        ]);
        $fieldset2->addField(
            'type_value_1_url',
            'textarea',
            [
                'name'     => 'type_value_1_url',
                'label'    => __('Url (Image 1)'),
                'title'    => __('Url (Image 1)'),
                'required' => true,
                'disabled' => $isElementDisabled,
            ]
        );

        
        $fieldset2->addField('image_left_2', 'image', 
            [
                'name'     => 'image_left_2',
                'label'    => __('Image 2 (width:572px, height:362px), (width:768px, height:362px), (width:375px, height:362px)'),
                'title'    => __('Image 2 (width:572px, height:362px), (width:768px, height:362px), (width:375px, height:362px)'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset2->addField('image_left_2_mobile', 'image', 
            [
                'name'     => 'image_left_2_mobile',
                'label'    => __('Image 2 mobile (width:343px, height:218px), (width:225px, height:106px), (width:106px, height:106px)'),
                'title'    => __('Image 2 mobile (width:343px, height:218px), (width:225px, height:106px), (width:106px, height:106px)'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );
        /* 2: product + category + url */
        $fieldset2->addField(
            'type_value_2_product',
            'text',
            [
                'name'               => 'type_value_2_product',
                'label'              => __('Product ID (Image 2)'),
                'title'              => __('Product ID (Image 2)'),
                'required'           => false,
                'disabled'           => $isElementDisabled,
                // 'class'              => 'validate-number',
                'after_element_html' => '<a href="#" title="Show Product Grid" onclick="toogleProduct(\'product_grid_type_2\', \'show_product_grid_type_2\');return false;">'
                    . '<img id="show_product_grid_type_2" src="'
                    . $this->getViewFileUrl('Simi_Simiconnector::images/arrow_down.png')
                    . '" title="" /></a>'
                    . $this->getLayout()->createBlock('Simi\Simicustomize\Block\Adminhtml\Homesection\Edit\Tab\Productgridtype2')
                    // ->setId('product_grid_type_2')
                    ->toHtml()
            ]
        );
        $fieldset2->addField('type_value_2_category', 'select', [
            'name'     => 'type_value_2_category',
            'label'    => __('Category (Image 2)'),
            'title'    => __('Category (Image 2)'),
            'required' => false,
            'values'   => $this->simiObjectManager->get('Simi\Simiconnector\Helper\Catetree')->getChildCatArray(),
        ]);
        $fieldset2->addField(
            'type_value_2_url',
            'textarea',
            [
                'name'     => 'type_value_2_url',
                'label'    => __('Url (Image 2)'),
                'title'    => __('Url (Image 2)'),
                'required' => false,
                'disabled' => $isElementDisabled,
            ]
        );


        /** Add fieldset 3 */
        $fieldset3 = $form->addFieldset('product_fieldset', ['legend' => __('Products')]);

        $fieldset3->addField(
            'product_id_1',
            'text',
            [
                'name'               => 'product_id_1',
                'label'              => __('Product ID 1'),
                'title'              => __('Product ID 1'),
                'required'           => false,
                'disabled'           => $isElementDisabled,
                'class' => 'validate-number',
                'after_element_html' => '<a href="#" title="Show Product Grid" onclick="toogleProduct(\'product_grid\', \'show_product_grid_1\');return false;">'
                    . '<img id="show_product_grid_1" src="'
                    . $this->getViewFileUrl('Simi_Simiconnector::images/arrow_down.png')
                    . '" title="" /></a>'
                    . $this->getLayout()->createBlock('Simi\Simicustomize\Block\Adminhtml\Homesection\Edit\Tab\Productgrid')
                    // ->setId('product_grid_1')
                    ->toHtml()
            ]
        );
        $fieldset3->addField(
            'product_id_2',
            'text',
            [
                'name'               => 'product_id_2',
                'label'              => __('Product ID 2'),
                'title'              => __('Product ID 2'),
                'required'           => false,
                'disabled'           => $isElementDisabled,
                'class' => 'validate-number',
                'after_element_html' => '<a href="#" title="Show Product Grid" onclick="toogleProduct(\'product_grid_2\', \'show_product_grid_2\');return false;">'
                    . '<img id="show_product_grid_2" src="'
                    . $this->getViewFileUrl('Simi_Simiconnector::images/arrow_down.png')
                    . '" title="" /></a>'
                    . $this->getLayout()->createBlock('Simi\Simicustomize\Block\Adminhtml\Homesection\Edit\Tab\Productgrid2')
                    // ->setId('product_grid_2')
                    ->toHtml()
            ]
        );
        $fieldset3->addField(
            'product_id_3',
            'text',
            [
                'name'               => 'product_id_3',
                'label'              => __('Product ID 3'),
                'title'              => __('Product ID 3'),
                'required'           => false,
                'disabled'           => $isElementDisabled,
                'class' => 'validate-number',
                'after_element_html' => '<a href="#" title="Show Product Grid" onclick="toogleProduct(\'product_grid_3\', \'show_product_grid_3\');return false;">'
                    . '<img id="show_product_grid_3" src="'
                    . $this->getViewFileUrl('Simi_Simiconnector::images/arrow_down.png')
                    . '" title="" /></a>'
                    . $this->getLayout()->createBlock('Simi\Simicustomize\Block\Adminhtml\Homesection\Edit\Tab\Productgrid3')
                    // ->setId('product_grid_3')
                    ->toHtml()
            ]
        );

        // $this->_eventManager->dispatch('adminhtml_homesection_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Homesection Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Homesection Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    public function _isAllowedAction($resourceId)
    {
        return true;
    }
}
