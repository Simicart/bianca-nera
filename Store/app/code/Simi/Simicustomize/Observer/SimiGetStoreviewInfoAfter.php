<?php

namespace Simi\Simicustomize\Observer;

use Magento\Framework\Event\ObserverInterface;
use Simi\VendorMapping\Api\VendorListInterface;
use Magento\Framework\UrlInterface;

class SimiGetStoreviewInfoAfter implements ObserverInterface {
    public $simiObjectManager;
    public $vendorList;
    protected $_attributeFactory;
    protected $eavConfig;
    protected $storeManager;
    protected $swatchMediaHelper;
    
    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $config;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $simiObjectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Swatches\Helper\Media $swatchMediaHelper,
        VendorListInterface $vendorList,
        UrlInterface $urlBuilder
    ) {
        $this->simiObjectManager = $simiObjectManager;
        $this->vendorList = $vendorList;
        $this->config = $config;
        $this->_attributeFactory = $attributeFactory;
        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
        $this->swatchMediaHelper = $swatchMediaHelper;
        $this->urlBuilder = $urlBuilder;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $object = $observer->getEvent()->getData('object');
        if ($object->storeviewInfo) {
            //TODO: will be sync with Ocean system in the future
            $object->storeviewInfo['vendor_list'] = $this->vendorList->getVendorList(true); //get all vendors
            $object->storeviewInfo['delivery_returns'] = $this->config->getValue('sales/policy/delivery_returns', 'store'); //get all vendors
            $object->storeviewInfo['preorder_deposit'] = $this->config->getValue('sales/preorder/deposit_amount'); //get all vendors
            // add brands list to storeview api
            $descriptionArr = array();
            $brandBannerArr = array();
            $serializer = $this->simiObjectManager->get('Magento\Framework\Serialize\SerializerInterface');
            $brandDetails = $this->config->getValue('simiconnector/product_brands/brand_details', 'store');
            if ($brandDetails) {
                $brandsDetailsFromConfig = $serializer->unserialize($brandDetails);
                if ($brandsDetailsFromConfig && is_array($brandsDetailsFromConfig)) {
                    foreach ($brandsDetailsFromConfig as $brandDetailsFromConfig) {
                        if (isset($brandDetailsFromConfig['brand_title']) && isset($brandDetailsFromConfig['brand_description'])) {
                            $descriptionArr[$brandDetailsFromConfig['brand_title']] = $brandDetailsFromConfig['brand_description'];
                        }
                        if (isset($brandDetailsFromConfig['brand_title']) && isset($brandDetailsFromConfig['brand_banner'])) {
                            $brandBannerArr[$brandDetailsFromConfig['brand_title']] = $brandDetailsFromConfig['brand_banner'];
                        }
                    }
                }
            }
            $attributeInfo = $this->_attributeFactory->getCollection();
            $attributeInfo->addFieldToFilter('attribute_code', 'brand');
            $storeId = $this->storeManager->getStore()->getStoreId();
            foreach($attributeInfo as $brand){
                $optionCollection = $this->simiObjectManager->get('\Magento\Eav\Model\Entity\Attribute\Option')->getCollection();
                $optionCollection
                    ->getSelect()
                    ->joinLeft(
                        ['value_table' => $optionCollection->getTable('eav_attribute_option_value')],
                        'value_table.option_id = main_table.option_id AND value_table.store_id = '.$storeId,
                        ['value_table.value AS name']
                    )
                    ->joinLeft(
                        ['swatch_table' => $optionCollection->getTable('eav_attribute_option_swatch')],
                        'swatch_table.option_id = main_table.option_id AND swatch_table.store_id = 0',
                        ['swatch_table.value AS value', 'swatch_table.option_id AS option_id']
                    )
                    ->where('attribute_id = ?', $brand->getAttributeId());
                foreach($optionCollection as $option){
                    $brandName = $option->getData('name');
                    $brandDesc = isset($descriptionArr[$brandName])?$descriptionArr[$brandName]:'';
                    $brandBanner = isset($brandBannerArr[$brandName])?$brandBannerArr[$brandName]:'';
                    $object->storeviewInfo['brands'][] = [
                        'option_id' => $option->getData('option_id'),
                        'name' => $brandName,
                        'description' => $brandDesc,
                        'banner' => $brandBanner ? $this->urlBuilder->getBaseUrl().UrlInterface::URL_TYPE_MEDIA . '/' . $brandBanner : '',
                        'image' => $this->swatchMediaHelper->getSwatchMediaUrl() . $option->getData('value'),
                        'attribute_name' => $brand->getData('frontend_label'),
                        'attribute_code' => $brand->getData('attribute_code'),
                        'attribute_id' => $brand->getData('attribute_id'),
                        'is_required' => $brand->getData('is_required'),
                    ];
                }
            }
            $object->storeviewInfo['livechat'] = array(
                'enabled' => $this->config->getValue('simiconnector/customchat/enable', 'store'),
                'license' => $this->config->getValue('simiconnector/customchat/license', 'store'),
            );
            $object->storeviewInfo['instagram'] = array(
                'enabled' => $this->config->getValue('simiconnector/instagram/enable', 'store'),
                'userid' => $this->config->getValue('simiconnector/instagram/userid', 'store'),
            );
            $object->storeviewInfo['instant_contact'] = array(
                'enabled' => $this->config->getValue('simiconnector/instant_contact/enable', 'store'),
                'times' => $this->config->getValue('simiconnector/instant_contact/times', 'store'),
                'phone' => $this->config->getValue('simiconnector/instant_contact/phone', 'store'),
            );
            $sizeGuideFile = $this->config->getValue('simiconnector/sizeguide/image_file', 'store');
            $sizeGuideFileMobile = $this->config->getValue('simiconnector/sizeguide/image_file_mobile', 'store');
            $object->storeviewInfo['size_guide'] = array(
                'image_file' => array(
                    'src' => $this->urlBuilder->getBaseUrl().UrlInterface::URL_TYPE_MEDIA.'/sizeguide/'.$sizeGuideFile,
                    'path' => '/'.UrlInterface::URL_TYPE_MEDIA.'/sizeguide/'.$sizeGuideFile
                ),
                'image_file_mobile' => array(
                    'src' => $this->urlBuilder->getBaseUrl().UrlInterface::URL_TYPE_MEDIA.'/sizeguide_mobile/'.$sizeGuideFileMobile,
                    'path' => '/'.UrlInterface::URL_TYPE_MEDIA.'/sizeguide_mobile/'.$sizeGuideFileMobile
                ),
            );
            $object->storeviewInfo['service'] = array(
                'types' => $this->simiObjectManager->get('Simi\Simicustomize\Model\Source\Service\ServiceType')->getAllOptions(),
                'description' => $this->config->getValue('sales/service/description', 'store'),
            );
            $aw_blog_enable = $this->config->getValue('aw_blog/general/enabled');
            $aw_blog_posts_per_page = $this->config->getValue('aw_blog/general/posts_per_page', 'store');
            if ($aw_blog_enable && $aw_blog_posts_per_page){
                $object->storeviewInfo['blog_posts_per_page'] = $aw_blog_posts_per_page;
            }

            $this->simiObjectManager->get('Magento\Framework\Serialize\SerializerInterface');
            $customTitles = $this->config->getValue('pwa_titles/pwa_titles/pwa_titles', 'store');
            if ($customTitles) {
                $customTitles = $this->simiObjectManager->get('Magento\Framework\Serialize\SerializerInterface')
                    ->unserialize($customTitles);
                $customTitleDict = array();
                if ($customTitles && is_array($customTitles)) {
                    foreach ($customTitles as $customTitle) {
                        $customTitleDict[$customTitle['url_path']] = $customTitle;
                    }
                }
                $object->storeviewInfo['custom_pwa_titles'] = $customTitleDict;
            }
            // Nam customize
            $object->storeviewInfo['header_footer_config'] = array(
                'bianca_header_phone' => $this->config->getValue('simiconnector/pwa_header/bianca_header_phone', 'store'),
                'bianca_header_sale_title' => $this->config->getValue('simiconnector/pwa_header/bianca_header_sale_title', 'store'),
                'bianca_header_sale_link' => $this->config->getValue('simiconnector/pwa_header/bianca_header_sale_link', 'store'),
                'bianca_header_storelocator' => $this->config->getValue('simiconnector/pwa_header/bianca_header_storelocator', 'store'),
                'bianca_subcribe_description' => $this->config->getValue('simiconnector/pwa_footer_subcribe/bianca_subcribe_description', 'store'),
                'footer_logo' => $this->config->getValue('simiconnector/pwa_footer_subcribe/footer_logo', 'store'),
                'footer_logo_alt' => $this->config->getValue('simiconnector/pwa_footer_subcribe/footer_logo_alt', 'store'),
                'footer_customer_service' => $this->config->getValue('simiconnector/footer_customer_service/customer_service', 'store'),
                'footer_information' => $this->config->getValue('simiconnector/footer_customer_service/more_information', 'store'),
                'bianca_footer_phone' => $this->config->getValue('simiconnector/pwa_footer/bianca_footer_phone', 'store'),
                'bianca_footer_email' => $this->config->getValue('simiconnector/pwa_footer/bianca_footer_email', 'store'),
                'bianca_footer_facebook' => $this->config->getValue('simiconnector/pwa_footer/bianca_footer_facebook', 'store'),
                'bianca_footer_instagram' => $this->config->getValue('simiconnector/pwa_footer/bianca_footer_instagram', 'store'),
                'bianca_footer_twitter' => $this->config->getValue('simiconnector/pwa_footer/bianca_footer_twitter', 'store'),
                'bianca_footer_linkedin' => $this->config->getValue('simiconnector/pwa_footer/bianca_footer_linkedin', 'store'),
                'bianca_footer_google' => $this->config->getValue('simiconnector/pwa_footer/bianca_footer_google', 'store'),
                'bianca_footer_youtube' => $this->config->getValue('simiconnector/pwa_footer/bianca_footer_youtube', 'store'),
                'bianca_footer_snapchat' => $this->config->getValue('simiconnector/pwa_footer/bianca_footer_snapchat', 'store'),
                'bianca_android_app' => $this->config->getValue('simiconnector/footer_app/bianca_android_app', 'store'),
                'bianca_ios_app' => $this->config->getValue('simiconnector/footer_app/bianca_ios_app', 'store')
            );
            $object->storeviewInfo['social_login_config'] = array(
                'firebase_config' => $this->config->getValue('simiconnector/firebase/firebase_config', 'store')
            );

            $object->storeviewInfo['seo'] = array(
                'home_meta' => array(
                    'title' => $this->config->getValue('simiconnector/seo/home_meta_title', 'store'),
                    'desc' => $this->config->getValue('simiconnector/seo/home_meta_description', 'store'),
                )
            );

            $object->storeviewInfo['base']['pwa_studio_url'] = $this->config->getValue('simiconnector/general/pwa_studio_url', 'store');
        }
    }
}