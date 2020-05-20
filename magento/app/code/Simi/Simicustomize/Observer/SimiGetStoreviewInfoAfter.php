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
            $object->storeviewInfo['vendor_list'] = $this->vendorList->getVendorList(); //get all vendors
            $object->storeviewInfo['delivery_returns'] = $this->config->getValue('sales/policy/delivery_returns'); //get all vendors
            $object->storeviewInfo['preorder_deposit'] = $this->config->getValue('sales/preorder/deposit_amount'); //get all vendors
            // add brands list to storeview api
            $descriptionArr = array();
            $serializer = $this->simiObjectManager->get('Magento\Framework\Serialize\SerializerInterface');
            $brandDetails = $this->config->getValue('simiconnector/product_brands/brand_details');
            if ($brandDetails) {
                $brandsDetailsFromConfig = $serializer->unserialize($brandDetails);
                if ($brandsDetailsFromConfig && is_array($brandsDetailsFromConfig)) {
                    foreach ($brandsDetailsFromConfig as $brandDetailsFromConfig) {
                        $descriptionArr[$brandDetailsFromConfig['brand_title']] = $brandDetailsFromConfig['brand_description'];
                    }
                }
            }
            $attributeInfo = $this->_attributeFactory->getCollection();
            $attributeInfo->addFieldToFilter('attribute_code', 'brand');
            $storeId = $this->storeManager->getDefaultStoreView()->getStoreId();
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
                    $object->storeviewInfo['brands'][] = [
                        'option_id' => $option->getData('option_id'),
                        'name' => $brandName,
                        'description' => $brandDesc,
                        'image' => $this->swatchMediaHelper->getSwatchMediaUrl() . $option->getData('value'),
                        'attribute_name' => $brand->getData('frontend_label'),
                        'attribute_code' => $brand->getData('attribute_code'),
                        'attribute_id' => $brand->getData('attribute_id'),
                        'is_required' => $brand->getData('is_required'),
                    ];
                }
            }
            $object->storeviewInfo['livechat'] = array(
                'enabled' => $this->config->getValue('simiconnector/customchat/enable'),
                'license' => $this->config->getValue('simiconnector/customchat/license'),
            );
            $object->storeviewInfo['instagram'] = array(
                'enabled' => $this->config->getValue('simiconnector/instagram/enable'),
                'userid' => $this->config->getValue('simiconnector/instagram/userid'),
            );
            $object->storeviewInfo['contact_us'] = array(
                'enabled' => $this->config->getValue('contact/contact/enabled'),
                'times' => $this->config->getValue('contact/time/times'),
            );
            $sizeGuideFile = $this->config->getValue('simiconnector/sizeguide/image_file');
            $sizeGuideFileMobile = $this->config->getValue('simiconnector/sizeguide/image_file_mobile');
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
                'description' => $this->config->getValue('sales/service/description'),
            );
            $aw_blog_enable = $this->config->getValue('aw_blog/general/enabled');
            $aw_blog_posts_per_page = $this->config->getValue('aw_blog/general/posts_per_page');
            if ($aw_blog_enable && $aw_blog_posts_per_page){
                $object->storeviewInfo['blog_posts_per_page'] = $aw_blog_posts_per_page;
            }

            $this->simiObjectManager->get('Magento\Framework\Serialize\SerializerInterface');
            $customTitles = $this->config->getValue('pwa_titles/pwa_titles/pwa_titles');
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
                'bianca_header_phone' => $this->config->getValue('simiconnector/pwa_header/bianca_header_phone'),
                'bianca_header_sale_title' => $this->config->getValue('simiconnector/pwa_header/bianca_header_sale_title'),
                'bianca_header_sale_link' => $this->config->getValue('simiconnector/pwa_header/bianca_header_sale_link'),
                'bianca_header_storelocator' => $this->config->getValue('simiconnector/pwa_header/bianca_header_storelocator'),
                'bianca_subcribe_description' => $this->config->getValue('simiconnector/pwa_footer_subcribe/bianca_subcribe_description'),
                'footer_logo' => $this->config->getValue('simiconnector/pwa_footer_subcribe/footer_logo'),
                'footer_logo_alt' => $this->config->getValue('simiconnector/pwa_footer_subcribe/footer_logo_alt'),
                'footer_customer_service' => $this->config->getValue('simiconnector/footer_customer_service/customer_service'),
                'footer_information' => $this->config->getValue('simiconnector/footer_customer_service/more_information'),
                'bianca_footer_phone' => $this->config->getValue('simiconnector/pwa_footer/bianca_footer_phone'),
                'bianca_footer_email' => $this->config->getValue('simiconnector/pwa_footer/bianca_footer_email'),
                'bianca_footer_facebook' => $this->config->getValue('simiconnector/pwa_footer/bianca_footer_facebook'),
                'bianca_footer_instagram' => $this->config->getValue('simiconnector/pwa_footer/bianca_footer_instagram'),
                'bianca_footer_twitter' => $this->config->getValue('simiconnector/pwa_footer/bianca_footer_twitter'),
                'bianca_footer_linkedin' => $this->config->getValue('simiconnector/pwa_footer/bianca_footer_linkedin'),
                'bianca_footer_google' => $this->config->getValue('simiconnector/pwa_footer/bianca_footer_google'),
                'bianca_footer_snapchat' => $this->config->getValue('simiconnector/pwa_footer/bianca_footer_snapchat'),
                'bianca_android_app' => $this->config->getValue('simiconnector/footer_app/bianca_android_app'),
                'bianca_ios_app' => $this->config->getValue('simiconnector/footer_app/bianca_ios_app')
            );
            $object->storeviewInfo['social_login_config'] = array(
                'firebase_config' => $this->config->getValue('simiconnector/firebase/firebase_config')
            );

            $object->storeviewInfo['seo'] = array(
                'home_meta' => array(
                    'title' => $this->config->getValue('simiconnector/seo/home_meta_title'),
                    'desc' => $this->config->getValue('simiconnector/seo/home_meta_description'),
                )
            );
        }
    }
}