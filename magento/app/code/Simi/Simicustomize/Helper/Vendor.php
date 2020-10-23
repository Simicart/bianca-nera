<?php

namespace Simi\Simicustomize\Helper;


class Vendor extends \Simi\Simiconnector\Helper\Data
{
    protected $_configHelper;

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
        if (!$this->_configHelper) {
            $this->_confighelper = $this->simiObjectManager->get('Vnecoms\VendorsConfig\Helper\Data');
        }
        if (!$vendor instanceof \Vnecoms\Vendors\Model\Vendor) {
            $vendor = $this->simiObjectManager->get('\Vnecoms\Vendors\Model\Vendor')->load($vendor);
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
            'company'=> $this->_confighelper->getVendorConfig('general/store_information/company', $vendor->getId()),
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
}
