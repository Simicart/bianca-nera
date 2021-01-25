<?php

namespace Simi\Simicustomize\Model;

/**
 * Simicustomize Model
 *
 * @method \Simi\Simicustomize\Model\Resource\Page _getResource()
 * @method \Simi\Simicustomize\Model\Resource\Page getResource()
 */
class Homesection extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Simi\Simicustomize\Helper\Website
     * */
    public $websiteHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\Key $resource
     * @param ResourceModel\Key\Collection $resourceCollection
     * @param \Simi\Simicustomize\Helper\Website $websiteHelper
     * @param AppFactory $app
     * @param PluginFactory $plugin
     * @param DesignFactory $design
     * @param ResourceModel\App\CollectionFactory $appCollection
     * @param ResourceModel\Key\CollectionFactory $keyCollection
     */
    public $simiObjectManager;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Simi\Simicustomize\Model\ResourceModel\Homesection $resource,
        \Simi\Simicustomize\Model\ResourceModel\Homesection\Collection $resourceCollection,
        \Magento\Framework\ObjectManagerInterface $simiObjectManager,
        \Simi\Simiconnector\Helper\Website $websiteHelper
    ) {
        $this->simiObjectManager = $simiObjectManager;
        $this->websiteHelper    = $websiteHelper;

        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {

        $this->_init('Simi\Simicustomize\Model\ResourceModel\Homesection');
    }

    /**
     * @return array Type
     */
    public function toOptionTypeHash()
    {
        $platform = [
            '1' => __('Product In-app'),
            // '2' => __('Category In-app'),
            '3' => __('Website Page'),
        ];
        return $platform;
    }

    /**
     * @return array Status
     */
    public function toOptionStatusHash()
    {
        $status = [
            '1' => __('Enable'),
            '2' => __('Disabled'),
        ];
        return $status;
    }

    /**
     * @return array Website
     */
    public function toOptionWebsiteHash()
    {
        $website_collection = $this->websiteHelper->getWebsiteCollection();
        $list               = [];
        $list[0]            = __('All');
        if ($this->simiObjectManager->get('Simi\Simiconnector\Helper\Data')->countArray($website_collection) > 0) {
            foreach ($website_collection as $website) {
                $list[$website->getId()] = $website->getName();
            }
        }
        return $list;
    }

    public function delete()
    {
        $typeID            = $this->simiObjectManager
                ->get('Simi\Simiconnector\Helper\Data')->getVisibilityTypeId('homesection');
        $visibleStoreViews = $this->simiObjectManager->create('Simi\Simiconnector\Model\Visibility')->getCollection()
                ->addFieldToFilter('content_type', $typeID)
                ->addFieldToFilter('item_id', $this->getId());
        foreach ($visibleStoreViews as $visibilityItem) {
            $this->simiObjectManager
                            ->get('Simi\Simiconnector\Helper\Data')->deleteModel($visibilityItem);
        }
        return parent::delete();
    }
}
