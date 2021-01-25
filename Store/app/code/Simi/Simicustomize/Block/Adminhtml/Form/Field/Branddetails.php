<?php

namespace Simi\Simicustomize\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class Branddetails extends AbstractFieldArray
{
    protected $imageRenderer;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Simi\Simicustomize\Block\Adminhtml\Form\Field\Renderer\Image $imageRenderer,
        array $data = []
    ){
        $this->imageRenderer = $imageRenderer;
        parent::__construct($context, $data);
    }

    protected function _prepareToRender()
    {
        $this->addColumn('brand_title', ['label' => __('Brand Title (By Store Locale)'), 'class' => 'required-entry', 'size' => '200px' ]);
        $this->addColumn('brand_description', ['label' => __('Brand description'), 'class' => 'required-entry', 'size' => '400px']);
        $this->addColumn('brand_banner', [
            'label' => __('Banner image'), 'class' => '', 'size' => '200px',
            'renderer' => $this->imageRenderer]);
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
        $columnValues = $row->getColumnValues();
        // if (!isset($columnValues['brand_banner'])) {
        //     $columnValues['brand_banner'] = '';
        // }
        if (!$row->getBrandBanner()) {
            $row->setBrandBanner('');
        }
    }
}
