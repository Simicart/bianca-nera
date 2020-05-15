<?php

namespace Simi\Simicustomize\Block\Adminhtml\Homesection;

/**
 * Admin Simicustomize page
 *
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
   
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize cms page edit block
     *
     * @return void
     */
    public function _construct()
    {
        $this->_objectId   = 'id';
        $this->_blockGroup = 'Simi_Simicustomize';
        $this->_controller = 'adminhtml_homesection';

        parent::_construct();

        if ($this->_isAllowedAction('Simi_Simicustomize::save')) {
            $this->buttonList->update('save', 'label', __('Save'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                'label'          => __('Save and Continue Edit'),
                'class'          => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
                    ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('Simi_Simicustomize::simicustomize_delete')) {
            $this->buttonList->update('delete', 'label', __('Delete'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->coreRegistry->registry('homesection')->getId()) {
            return __("Edit Homesection '%1'", $this->escapeHtml($this->coreRegistry->registry('homesection')->getId()));
        } else {
            return __('New Homesection');
        }
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

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    public function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            'simicustomize/*/save',
            ['_current'   => true,
                    'back'       => 'edit',
                    'active_tab' =>
                    '{{tab_id}}']
        );
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    public function _prepareLayout()
    {
        $arrow_down_img = $this->getViewFileUrl('Simi_Simiconnector::images/arrow_down.png');
        $arrow_up_img   = $this->getViewFileUrl('Simi_Simiconnector::images/arrow_up.png');

        $this->_formScripts[] = "
            document.addEventListener('DOMContentLoaded', function(){
                // event change Type
                changeType(document.getElementById('type').value);

                // default: hidden product grid
                document.getElementById('product_grid_type_1').style.display = 'none';
                document.getElementById('product_grid_type_2').style.display = 'none';
                document.getElementById('product_grid_1').style.display = 'none';
                document.getElementById('product_grid_2').style.display = 'none';
                document.getElementById('product_grid_3').style.display = 'none';
            }, false);

            document.body.addEventListener('click', function(e){
                var product_grid_trs = document.querySelectorAll('#product_grid_1 tbody tr');
                var trElement;
                var radioArray = [];
                for (var i = 0, j = 0; i < product_grid_trs.length; i++) {
                    trElement = product_grid_trs.item(i);
                    trElement.addEventListener('click', function(e){
                        var rd = this.getElementsByTagName('input')[0];
                        rd.checked = true;
                        document.getElementById('product_id_1').value = rd.value;
                        return false;
                    });
                }
                var product_grid_trs = document.querySelectorAll('#product_grid_2 tbody tr');
                var trElement;
                var radioArray = [];
                for (var i = 0, j = 0; i < product_grid_trs.length; i++) {
                    trElement = product_grid_trs.item(i);
                    trElement.addEventListener('click', function(e){
                        var rd = this.getElementsByTagName('input')[0];
                        rd.checked = true;
                        document.getElementById('product_id_2').value = rd.value;
                        return false;
                    });
                }
                var product_grid_trs = document.querySelectorAll('#product_grid_3 tbody tr');
                var trElement;
                var radioArray = [];
                for (var i = 0, j = 0; i < product_grid_trs.length; i++) {
                    trElement = product_grid_trs.item(i);
                    trElement.addEventListener('click', function(e){
                        var rd = this.getElementsByTagName('input')[0];
                        rd.checked = true;
                        document.getElementById('product_id_3').value = rd.value;
                        return false;
                    });
                }
                var product_grid_trs = document.querySelectorAll('#product_grid_type_1 tbody tr');
                var trElement;
                var radioArray = [];
                for (var i = 0, j = 0; i < product_grid_trs.length; i++) {
                    trElement = product_grid_trs.item(i);
                    trElement.addEventListener('click', function(e){
                        var rd = this.getElementsByTagName('input')[0];
                        rd.checked = true;
                        document.getElementById('type_value_1_product').value = rd.value;
                        return false;
                    });
                }
                var product_grid_trs = document.querySelectorAll('#product_grid_type_2 tbody tr');
                var trElement;
                var radioArray = [];
                for (var i = 0, j = 0; i < product_grid_trs.length; i++) {
                    trElement = product_grid_trs.item(i);
                    trElement.addEventListener('click', function(e){
                        var rd = this.getElementsByTagName('input')[0];
                        rd.checked = true;
                        document.getElementById('type_value_2_product').value = rd.value;
                        return false;
                    });
                }
            }, false);

            function toogleProduct(id, choose_img){
                var product_grid = document.getElementById(id);
                var choose_img = document.getElementById(choose_img);
                if(product_grid.style.display == 'none'){
                    product_grid.style.display = 'block';
                    choose_img.src = '$arrow_up_img';
                } else {
                    product_grid.style.display = 'none';
                    choose_img.src = '$arrow_down_img';
                }
            }

            function changeType(value){
                switch (value) {
                    case '1':
                        document.querySelectorAll('.field-type_value_1_product')[0].style.display = 'block';
                        document.querySelectorAll('#type_value_1_product')[0].classList.add('required-entry');
                        document.querySelectorAll('.field-type_value_2_product')[0].style.display = 'block';
                        // document.querySelectorAll('#type_value_2_product')[0].classList.add('required-entry');

                        document.querySelectorAll('.field-type_value_1_category')[0].style.display = 'none';
                        document.querySelectorAll('#type_value_1_category')[0].classList.remove('required-entry');
                        document.querySelectorAll('.field-type_value_2_category')[0].style.display = 'none';
                        // document.querySelectorAll('#type_value_2_category')[0].classList.remove('required-entry');

                        document.querySelectorAll('.field-type_value_1_url')[0].style.display = 'none';
                        document.querySelectorAll('#type_value_1_category')[0].classList.remove('required-entry');
                        document.querySelectorAll('.field-type_value_2_url')[0].style.display = 'none';
                        // document.querySelectorAll('#type_value_2_category')[0].classList.remove('required-entry');
                        break;
                    case '2':
                        document.querySelectorAll('.field-type_value_1_product')[0].style.display = 'none';
                        document.querySelectorAll('#type_value_1_product')[0].classList.remove('required-entry');
                        document.querySelectorAll('.field-type_value_2_product')[0].style.display = 'none';
                        // document.querySelectorAll('#type_value_2_product')[0].classList.remove('required-entry');

                        document.querySelectorAll('.field-type_value_1_category')[0].style.display = 'block';
                        document.querySelectorAll('#type_value_1_category')[0].classList.add('required-entry');
                        document.querySelectorAll('.field-type_value_2_category')[0].style.display = 'block';
                        // document.querySelectorAll('#type_value_2_category')[0].classList.add('required-entry');

                        document.querySelectorAll('.field-type_value_1_url')[0].style.display = 'none';
                        document.querySelectorAll('#type_value_1_url')[0].classList.remove('required-entry');
                        document.querySelectorAll('.field-type_value_2_url')[0].style.display = 'none';
                        // document.querySelectorAll('#type_value_2_url')[0].classList.remove('required-entry');
                        break;
                    case '3':
                        document.querySelectorAll('.field-type_value_1_product')[0].style.display = 'none';
                        document.querySelectorAll('#type_value_1_product')[0].classList.remove('required-entry');
                        document.querySelectorAll('.field-type_value_2_product')[0].style.display = 'none';
                        // document.querySelectorAll('#type_value_2_product')[0].classList.remove('required-entry');

                        document.querySelectorAll('.field-type_value_1_category')[0].style.display = 'none';
                        document.querySelectorAll('#type_value_1_category')[0].classList.remove('required-entry');
                        document.querySelectorAll('.field-type_value_2_category')[0].style.display = 'none';
                        // document.querySelectorAll('#type_value_2_category')[0].classList.remove('required-entry');

                        document.querySelectorAll('.field-type_value_1_url')[0].style.display = 'block';
                        document.querySelectorAll('#type_value_1_url')[0].classList.add('required-entry');
                        document.querySelectorAll('.field-type_value_2_url')[0].style.display = 'block';
                        // document.querySelectorAll('#type_value_2_url')[0].classList.add('required-entry');
                        break;
                    default:
                        document.querySelectorAll('.field-type_value_1_product')[0].style.display = 'block';
                        document.querySelectorAll('#type_value_1_product')[0].classList.add('required-entry');

                        document.querySelectorAll('.field-type_value_1_category')[0].style.display = 'none';
                        document.querySelectorAll('#type_value_1_category')[0].classList.remove('required-entry');

                        document.querySelectorAll('.field-type_value_1_url')[0].style.display = 'none';
                        document.querySelectorAll('#type_value_1_url')[0].classList.remove('required-entry');

                        document.querySelectorAll('.field-type_value_2_product')[0].style.display = 'block';
                        // document.querySelectorAll('#type_value_2_product')[0].classList.add('required-entry');

                        document.querySelectorAll('.field-type_value_2_category')[0].style.display = 'none';
                        // document.querySelectorAll('#type_value_2_category')[0].classList.remove('required-entry');

                        document.querySelectorAll('.field-type_value_2_url')[0].style.display = 'none';
                        // document.querySelectorAll('#type_value_2_url')[0].classList.remove('required-entry');
                }
            }

            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'page_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'page_content');
                }
            };
        ";
        return parent::_prepareLayout();
    }
}
