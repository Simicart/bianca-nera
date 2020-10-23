<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System config image field backend model
 */
namespace Simi\VendorMapping\Model\Config\Backend;

class Image extends \Vnecoms\VendorsConfig\Model\Config\Backend\File
{
    /**
     * Save uploaded file before saving config value
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $tmpName = $this->_requestData->getTmpName($this->getPath());
        $file = [];
        if ($tmpName) {
            $file['tmp_name'] = $tmpName;
            $file['name'] = $this->getVendorId().'-'.$this->_requestData->getName($this->getPath());
        } elseif (!empty($value['tmp_name'])) {
            $file['tmp_name'] = $value['tmp_name'];
            $file['name'] = $value['value'];
        }
        if (!empty($file)) {
            $uploadDir = $this->_getUploadDir();
            try {
                $uploader = $this->_uploaderFactory->create(['fileId' => $file]);
                $uploader->setAllowedExtensions($this->_getAllowedExtensions());
                $uploader->setAllowRenameFiles(false);
                $uploader->addValidateCallback('size', $this, 'validateMaxSize');
                $result = $uploader->save($uploadDir);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }

            $filename = $result['file'];
            if ($filename) {
                $this->setValue($filename);
            }
            return $this;
        }
        
        if (is_array($value) && !empty($value['delete'])) {
            $this->setValue('');
            return $this;
        }

        $this->unsValue();
        return $this;
    }

    /**
     * Getter for allowed extensions of uploaded files
     *
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png'];
    }
}
