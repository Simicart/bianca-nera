<?php

namespace Simi\Simicustomize\Block\Adminhtml\Form\Field\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class Image extends AbstractRenderer
{
    private $_storeManager;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context, StoreManagerInterface $storemanager, array $data = [])
    {
        $this->_storeManager = $storemanager;
        parent::__construct($context, $data);
        $this->_authorization = $context->getAuthorization();
    }

    public function toHtml(){
        $mediaDirectory = $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        return '<img src="'.$mediaDirectory.'<%- brand_banner %>" style="width: 100px;max-height: 100px;"/> \
            <input type="file" name="brand_detail_banner[<%- _id %>]" accept="image/jpeg,image/gif,image/png" style="max-width: 100px;"/> \
            <input name="'.$this->getInputName().'" id="'.$this->getInputId().'" type="hidden"/>';
    }
}