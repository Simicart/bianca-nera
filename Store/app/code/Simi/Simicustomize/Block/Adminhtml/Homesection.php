<?php

/**
 * Adminhtml simiconnector list block
 *
 */

namespace Simi\Simicustomize\Block\Adminhtml;

class Homesection extends \Magento\Backend\Block\Widget\Grid\Container
{

    /**
     * Constructor
     *
     * @return void
     */
    public function _construct()
    {
        $this->_controller     = 'adminhtml_homesection';
        $this->_blockGroup     = 'Simi_Simicustomize';
        $this->_headerText     = __('Home Section');
        $this->_addButtonLabel = __('Add New Section');
        parent::_construct();
        if ($this->_isAllowedAction('Simi_Simicustomize::save')) {
            $this->buttonList->update('add', 'label', __('Add Section'));
        } else {
            $this->buttonList->remove('add');
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
}
