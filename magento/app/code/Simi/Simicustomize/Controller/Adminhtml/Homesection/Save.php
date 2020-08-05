<?php

namespace Simi\Simicustomize\Controller\Adminhtml\Homesection;

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
{

    /**
     * Save action
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        $simiObjectManager = $this->_objectManager;
        $model = $simiObjectManager->create('Simi\Simicustomize\Model\Homesection');

        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $model->load($id);
        }

        if (isset($data['image_left_1']['delete'])) {
            $data['image_left_1'] = '';
        }
        if (isset($data['image_left_1_mobile']['delete'])) {
            $data['image_left_1_mobile'] = '';
        }
        if (isset($data['image_left_2']['delete'])) {
            $data['image_left_2'] = '';
        }
        if (isset($data['image_left_2_mobile']['delete'])) {
            $data['image_left_2_mobile'] = '';
        }

        if (isset($data['image_left_1']['value'])) {
            $data['image_left_1'] = $data['image_left_1']['value'];
        }
        if (isset($data['image_left_1_mobile']['value'])) {
            $data['image_left_1_mobile'] = $data['image_left_1_mobile']['value'];
        }
        if (isset($data['image_left_2']['value'])) {
            $data['image_left_2'] = $data['image_left_2']['value'];
        }
        if (isset($data['image_left_2_mobile']['value'])) {
            $data['image_left_2_mobile'] = $data['image_left_2_mobile']['value'];
        }

        if ($data['type_value_1_product'] && $data['type'] == '1') {
            $data['type_value_1'] = $data['type_value_1_product'];
        }
        if ($data['type_value_1_category'] && $data['type'] == '2') {
            $data['type_value_1'] = $data['type_value_1_category'];
        }
        if ($data['type_value_1_url'] && $data['type'] == '3') {
            $data['type_value_1'] = $data['type_value_1_url'];
        }
        if ($data['type_value_2_product'] && $data['type'] == '1') {
            $data['type_value_2'] = $data['type_value_2_product'];
        }
        if ($data['type_value_2_category'] && $data['type'] == '2') {
            $data['type_value_2'] = $data['type_value_2_category'];
        }
        if ($data['type_value_2_url'] && $data['type'] == '3') {
            $data['type_value_2'] = $data['type_value_2_url'];
        }

        // save product sku
        if (isset($data['product_id_1'])) {
            $product = $simiObjectManager->get('Magento\Catalog\Model\Product')->load($data['product_id_1']);
            $data['product_sku_1'] = $product->getSku();
        }
        if (isset($data['product_id_2'])) {
            $product = $simiObjectManager->get('Magento\Catalog\Model\Product')->load($data['product_id_2']);
            $data['product_sku_2'] = $product->getSku();
        }
        if (isset($data['product_id_3'])) {
            $product = $simiObjectManager->get('Magento\Catalog\Model\Product')->load($data['product_id_3']);
            $data['product_sku_3'] = $product->getSku();
        }

        $model->addData($data);

        try {
            $imageHelper = $simiObjectManager->get('Simi\Simiconnector\Helper\Data');

            if (!isset($data['image_left_1']['delete'])) {
                $imageFile = $imageHelper->uploadImage('image_left_1');
                if ($imageFile) {
                    $model->setData('image_left_1', $imageFile);
                }
            }

            if (!isset($data['image_left_1_mobile']['delete'])) {
                $imageFile = $imageHelper->uploadImage('image_left_1_mobile');
                if ($imageFile) {
                    $model->setData('image_left_1_mobile', $imageFile);
                }
            }

            if (!isset($data['image_left_2']['delete'])) {
                $imageFile = $imageHelper->uploadImage('image_left_2');
                if ($imageFile) {
                    $model->setData('image_left_2', $imageFile);
                }
            }
            if (!isset($data['image_left_2_mobile']['delete'])) {
                $imageFile = $imageHelper->uploadImage('image_left_2_mobile');
                if ($imageFile) {
                    $model->setData('image_left_2_mobile', $imageFile);
                }
            }

            $model->save();

            $this->updateVisibility($simiObjectManager, $model, $data);
            $this->messageManager->addSuccess(__('The Data has been saved.'));
            $simiObjectManager->get('Magento\Backend\Model\Session')->setFormData(false);
            $simiObjectManager->get('Simi\Simiconnector\Helper\Data')->flushStaticCache();
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                return;
            }
            $this->_redirect('*/*/');
            return;
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
        }

        $this->_getSession()->setFormData($data);
        $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
    }
    
    private function updateVisibility($simiObjectManager, $model, $data)
    {
        $simicustomizehelper = $simiObjectManager->get('Simi\Simiconnector\Helper\Data');
        if ($data['storeview_id'] && is_array($data['storeview_id'])) {
            $typeID            = $simicustomizehelper->getVisibilityTypeId('homesection');
            $visibleStoreViews = $simiObjectManager
                    ->create('Simi\Simiconnector\Model\Visibility')->getCollection()
                    ->addFieldToFilter('content_type', $typeID)
                    ->addFieldToFilter('item_id', $model->getId());
            $visibleStoreViews->walk('delete');
            foreach ($visibleStoreViews as $visibilityItem) {
                $simiObjectManager
                    ->get('Simi\Simiconnector\Helper\Data')->deleteModel($visibilityItem);
            }
            foreach ($data['storeview_id'] as $storeViewId) {
                $visibilityItem = $simiObjectManager->create('Simi\Simiconnector\Model\Visibility');
                $visibilityItem->setData('content_type', $typeID);
                $visibilityItem->setData('item_id', $model->getId());
                $visibilityItem->setData('store_view_id', $storeViewId);
                $simiObjectManager
                    ->get('Simi\Simiconnector\Helper\Data')->saveModel($visibilityItem);
            }
        }
    }
}
