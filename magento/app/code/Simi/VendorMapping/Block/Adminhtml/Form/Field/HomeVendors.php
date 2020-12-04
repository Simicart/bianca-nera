<?php

namespace Simi\VendorMapping\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class HomeVendors extends AbstractFieldArray
{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);
    }

    protected function _prepareToRender()
    {
        $this->addColumn('id', ['label' => __('ID'), 'class' => 'required-entry', 'size' => '200px' ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        // $columnValues = $row->getColumnValues();
        // if (!$row->getId()) {
        //     $row->setId('');
        // }
    }
}
