<?php

namespace Simi\VendorMapping\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Vendor extends \Simi\Simiconnector\Helper\Data
{
    /**
     * @var \Vnecoms\VendorsConfig\Helper\Data
     */
    protected $_vendorConfigHelper;

    protected $_bannerConfig;
    protected $_logoConfig;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDatabase,
        \Magento\Framework\ObjectManagerInterface $simiObjectManager,
        \Vnecoms\VendorsConfig\Helper\Data $vendorConfigHelper
    ){
        $this->simiObjectManager = $simiObjectManager;
        $this->_fileStorageDatabase = $fileStorageDatabase;
        $this->_mediaDirectory = $context->getFilesystem()->getDirectoryRead(DirectoryList::MEDIA);
        $this->_storeManager = $storeManager;
        $this->_vendorConfigHelper = $vendorConfigHelper;
    }

    protected function getProfileBlock($vendor)
    {
        if (!$vendor instanceof \Vnecoms\Vendors\Model\Vendor) {
            $vendor = $this->simiObjectManager->get('\Vnecoms\Vendors\Model\Vendor')->load($vendor);
        }
        $registry = $this->simiObjectManager->get('\Magento\Framework\Registry');
        $registry->unregister('vendor');
        $registry->register('vendor', $vendor);
        return $this->simiObjectManager->get('\Magento\Framework\View\LayoutInterface')
            ->createBlock('Vnecoms\Vendors\Block\Profile');
    }

    public function getProfile($vendor) {
        if (!$vendor instanceof \Vnecoms\Vendors\Model\Vendor) {
            $vendor = $this->simiObjectManager->get('\Vnecoms\Vendors\Model\Vendor')->load($vendor);
        }

        if (!$vendor || !$vendor->getId()) {
            return array();
        }

        $profileBlock = $this->getProfileBlock($vendor);
        $profile = array(
            'logo_width'=> $profileBlock->getLogoWidth(),
            'logo_height'=> $profileBlock->getLogoHeight(),
            'keep_transparency_logo'=> $profileBlock->keepTransparencyLogo(),
            'logo_url'=> $profileBlock->getLogoUrl(),
            'no_logo_url'=> $profileBlock->getNoLogoUrl(),
            'vendor_url'=> $profileBlock->getVendorUrl(),
            'store_name'=> $profileBlock->getStoreName(),
            'company'=> $this->_vendorConfigHelper->getVendorConfig('general/store_information/company', $vendor->getId()),
            'description'=> $profileBlock->getStoreDescription(),
            'can_show_vendor_short_description'=> $profileBlock->canShowVendorShortDescription(),
            'can_show_vendor_phone'=> $profileBlock->canShowVendorPhone(),
            'phone_number'=> $profileBlock->getPhoneNumber(),
            'can_show_operation_time'=> $profileBlock->canShowVendorOperationTime(),
            'operation_time'=> $profileBlock->getOperationTime(),
            'country'=> $profileBlock->getCountry(),
            'sales_count'=> $profileBlock->getSalesCount(),
            'joined_date'=> $profileBlock->getJoinedDate(),
            'address'=> $profileBlock->getAddress(),
        );
        $registry = $this->simiObjectManager->get('\Magento\Framework\Registry');
        $registry->unregister('vendor');
        return $profile;
    }

    /**
     * @param int $id
     * @return string
     */
    public function getStoreName($id){
        if (!$id) return '';
        $vendorHelper = $this->simiObjectManager->get('Vnecoms\Vendors\Helper\Data');
        return $vendorHelper->getVendorStoreName($id);
    }

    /**
     * Get vendor IDs saved in configuration
     */
    public function getHomeVendorsConfig(){
        $config = $this->scopeConfig->getValue('simiconnector/home/vendors', ScopeInterface::SCOPE_STORE);
        $serializer = $this->simiObjectManager->get('Magento\Framework\Serialize\SerializerInterface');
        if ($config) {
            return $serializer->unserialize($config);
        }
        return [];
    }

    /**
     * Get Seller Logo Image URL
     *
     * @param void
     * @return string
     */
    public function getBannerUrl($vendorId)
    {
        $path = $this->getBannerPath($vendorId);
        if ($this->checkIsFile($path)) {
            return $this ->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$path;
        }
        return '';
    }

    public function getBannerPath($vendorId)
    {
        if(!$this->_bannerConfig){
            $this->_bannerConfig = $this->_vendorConfigHelper->getVendorConfig('general/store_information/banner', $vendorId);
        }
        if ($this->_bannerConfig) {
            return 'ves_vendors/attribute/banner/' . $this->_bannerConfig;
        }
        return '';
    }

    /**
     * Get Seller Logo Image URL
     *
     * @param void
     * @return string
     */
    public function getLogoUrl($vendorId)
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
    public function getLogoPath($vendorId)
    {
        if(!$this->_logoConfig){
            $this->_logoConfig = $this->_vendorConfigHelper->getVendorConfig('general/store_information/logo', $vendorId);
        }
        if ($this->_logoConfig) {
            return 'ves_vendors/attribute/logo/' . $this->_logoConfig;
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
