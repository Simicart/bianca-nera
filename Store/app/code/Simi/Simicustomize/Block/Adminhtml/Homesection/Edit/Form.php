<?php

namespace Simi\Simicustomize\Block\Adminhtml\Homesection\Edit;

/**
 * Adminhtml simicustomize edit form block
 *
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * Prepare form
     *
     * @return $this
     */
    public function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id'      => 'edit_form',
                        'action'  => $this->getData('action'),
                        'method'  => 'post',
                        'enctype' => 'multipart/form-data']]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
