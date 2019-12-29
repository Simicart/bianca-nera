<?php

/**
 * Copyright © 2016 Simi. All rights reserved.
 */

namespace Simi\VendorMapping\Model\Api;

use Simi\VendorMapping\Api\VendorInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Vendor implements VendorInterface
{
    const DEFAULT_DIR = 'desc';
    const DEFAULT_LIMIT = 15;
    const DIR = 'dir';
    const ORDER = 'order';
    const PAGE = 'page';
    const LIMIT = 'limit';
    const OFFSET = 'offset';
    const FILTER = 'filter';
    const LIMIT_COUNT = 200;
    const VENDOR_IDS = 'ids'; //Filter by ids ex: 1,2,3

    /**
     * \Vnecoms\Vendors\Model\VendorFactory
     */
    protected $_vendorFactory;

    /**
     * \Vnecoms\Vendors\Model\ResourceModel\Vendor\Collection
     */
    protected $_collection;

    /**
     * \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Vnecoms\VendorsConfig\Helper\Data
     */
    protected $_configHelper;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $_fileStorageDatabase;
    
    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $mediaDirectory;

    protected $storeManager;
    protected $vendorHelper;
    protected $reviewHelper;

    protected $_logoConfig;
    protected $_bannerConfig;

    public function __construct(
        \Vnecoms\Vendors\Model\VendorFactory $vendorFactory,
        \Vnecoms\Vendors\Model\ResourceModel\Vendor\Collection $collection,
        \Magento\Framework\App\RequestInterface $request,
        \Vnecoms\VendorsConfig\Helper\Data $configHelper,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDatabase,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Simi\Simicustomize\Helper\Vendor $vendorHelper,
        \Simi\VendorMapping\Helper\Review $reviewHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        DirectoryList $directory_list
    ){
        $this->_vendorFactory = $vendorFactory;
        $this->_collection = $collection;
        $this->_request = $request;
        $this->_configHelper = $configHelper;
        $this->_fileStorageDatabase = $fileStorageDatabase;
        $this->_mediaDirectory = $context->getFilesystem()->getDirectoryRead(DirectoryList::MEDIA);
        $this->_storeManager = $storeManager;
        $this->vendorHelper = $vendorHelper;
        $this->reviewHelper = $reviewHelper;
    }

    /**
     * Vendor api VnecomsVendor module
     * @param int $id The Vendor ID.
     * @return array | json
     */
    public function getVendorDetail($id){
        $vendor = $this->_vendorFactory->create()->load($id);
        $data = $vendor->toArray();
        $data['logo'] = $this->getLogoUrl($vendor->getId());
        $data['logo_path'] = $this->getLogoPath($vendor->getId()) ? '/'. \Magento\Framework\UrlInterface::URL_TYPE_MEDIA . '/' .$this->getLogoPath($vendor->getId()) : '';
        $data['banner'] = $this->getBannerUrl($vendor->getId());
        $data['banner_path'] = $this->getBannerPath($vendor->getId()) ? '/'. \Magento\Framework\UrlInterface::URL_TYPE_MEDIA . '/' .$this->getBannerPath($vendor->getId()) : '';
        $data['profile'] = $this->vendorHelper->getProfile($vendor->getId());
        $data['reviews'] = $this->getReviews($id);
        return array('data' => $data);
    }

    public function getReviews($vendorId){
        return $this->reviewHelper->getVendorReviews($vendorId, false);
    }

    

    /**
     * Vendor list api VnecomsVendor module
     * @return array | json
     */
    public function getVendorList(){
        $vendors = [];
        $this->_buildLimit();
        $vendorIds = $this->_request->getParam(self::VENDOR_IDS);
        $postData = $this->_request->getContent();
        if ($postData) {
            $postData = json_decode($postData, true);
            if (isset($postData[self::VENDOR_IDS]) && $postData[self::VENDOR_IDS]) {
                $vendorIds = $postData[self::VENDOR_IDS];
            }
        }
        if ($this->_collection) {
            if ($vendorIds) {
                $vendor_ids = explode(',', $vendorIds);
                if (count($vendor_ids)) {
                    $this->_collection->addFieldToFilter('entity_id', array('FINSET', $vendor_ids));
                }
            }
            // $this->_collection->getSelect()->joinLeft(
            //     ['vendor_config' => $this->_collection->getTable('ves_vendor_config')],
            //     'vendor_config.vendor_id = e.entity_id AND vendor_config.store_id = 0 AND vendor_config.path = "general/store_information/logo"',
            //     ['vendor_config.value AS logo']
            // );
            foreach ($this->_collection as $vendor) {
                $vendorData = $vendor->toArray();
                $vendorData['logo'] = $this->getLogoUrl($vendor->getId());
                $vendorData['profile'] = $this->vendorHelper->getProfile($vendor->getId());
                $vendors[] = $vendorData;
            }
        }
        if (!count($vendors)) {
            return false;
        }
        return $vendors;
    }

    protected function _buildLimit(){
        if ($this->_collection) {
            $parameters = $this->_request->getParams();
            $postContent = $this->_request->getContent();
            if ($postContent) {
                $parameters = json_decode($postContent, true);
            }
            $page       = 1;
            if (isset($parameters[self::PAGE]) && $parameters[self::PAGE]) {
                $page = $parameters[self::PAGE];
            }
    
            $limit = self::DEFAULT_LIMIT;
            if (isset($parameters[self::LIMIT]) && $parameters[self::LIMIT]) {
                $limit = $parameters[self::LIMIT];
            }
    
            $offset = $limit * ($page - 1);
            if (isset($parameters[self::OFFSET]) && $parameters[self::OFFSET]) {
                $offset = $parameters[self::OFFSET];
            }
            $this->_collection->setPageSize($offset + $limit);
        }
    }

    /**
     * Get Seller Logo Image URL
     *
     * @param void
     * @return string
     */
    protected function getLogoUrl($vendorId)
    {
        $path = $this->getLogoPath($vendorId);
        if ($this->checkIsFile($path)) {
            return $this ->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$path;
        }
        return '';
    }

    /**
     * Get Seller Logo Image URL
     *
     * @param void
     * @return string
     */
    protected function getLogoPath($vendorId)
    {
        if(!$this->_logoConfig){
            $this->_logoConfig = $this->_configHelper->getVendorConfig('general/store_information/logo', $vendorId);
        }
        if ($this->_logoConfig) {
            return 'ves_vendors/logo/' . $this->_logoConfig;
        }
        return '';
    }

    /**
     * Get Seller Logo Image URL
     *
     * @param void
     * @return string
     */
    protected function getBannerUrl($vendorId)
    {
        $path = $this->getBannerPath($vendorId);
        if ($this->checkIsFile($path)) {
            return $this ->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$path;
        }
        return '';
    }

    protected function getBannerPath($vendorId)
    {
        if(!$this->_bannerConfig){
            $this->_bannerConfig = $this->_configHelper->getVendorConfig('general/store_information/banner', $vendorId);
        }
        if ($this->_bannerConfig) {
            return 'ves_vendors/banner/' . $this->_bannerConfig;
        }
        return '';
    }

    /**
     * If DB file storage is on - find there, otherwise - just file_exists
     *
     * @param string $filename relative file path
     * @return bool
     */
    protected function checkIsFile($filename)
    {
        if ($this->_fileStorageDatabase->checkDbUsage() && !$this->_mediaDirectory->isFile($filename)) {
            $this->_fileStorageDatabase->saveFileToFilesystem($filename);
        }
        return $this->_mediaDirectory->isFile($filename);
    }
}
