<?php

/**
 * Copyright © 2016 Simi. All rights reserved.
 */

namespace Simi\Simicustomize\Model\Api;

use Magento\Framework\App\Filesystem\DirectoryList;

class Homes extends \Simi\Simiconnector\Model\Api\Apiabstract
{

    public $DEFAULT_ORDER = 'sort_order';

    /**
     * @var Magento\Framework\App\Filesystem\DirectoryList $directoryList ;
     */
    public $directoryList;

    public function __construct(\Magento\Framework\ObjectManagerInterface $simiObjectManager, DirectoryList $directoryList)
    {
        $this->directoryList = $directoryList;
        parent::__construct($simiObjectManager);
    }

    public function setBuilderQuery()
    {
        return null;
    }

    public function index()
    {
        return $this->show();
    }

    public function show()
    {
        $data = $this->getData();
        /*
         * Get Banners
         */
        $banners = $this->simiObjectManager->get('Simi\Simicustomize\Model\Api\Homebanners');
        $banners->builderQuery = $banners->getCollection();
        $banners->setPluralKey('homebanners');
        $banners = $banners->index();

        /*
         * Get Categories
         */
        // $categories = $this->simiObjectManager->get('Simi\Simicustomize\Model\Api\Homecategories');
        // $categories->setData($this->getData());
        // $categories->builderQuery = $categories->getCollection();
        // $categories->setPluralKey('homecategories');
        // $categories = $categories->index();

        /*
         * Get Product List
         */
        // $productlists = $this->simiObjectManager->get('Simi\Simiconnector\Model\Api\Homeproductlists');
        // $productlists->builderQuery = $productlists->getCollection();
        // if ($data['resourceid'] == 'lite') {
        //     $productlists->SHOW_PRODUCT_ARRAY = false;
        // }
        // $productlists->setPluralKey('homeproductlists');
        // $productlists->setData($data);
        // $productlists = $productlists->index();

        /**
         * Get Newcollections items
         */
        // $newcollections = $this->simiObjectManager->get('Simi\Simicustomize\Model\Api\HomeNewcollections');
        // $newcollections->setData($this->getData());
        // $newcollections->setPluralKey('homenewcollections');
        // $newcollections->builderQuery = $newcollections->getCollection();
        // $newcollections = $newcollections->index();

        $homesections = $this->simiObjectManager->get('Simi\Simicustomize\Model\Api\Homesections');
        $homesections->setData($this->getData());
        $homesections->setPluralKey('homesections');
        $homesections->builderQuery = $homesections->getCollection();
        $homesections = $homesections->index();

        $information = ['home' => [
            'homebanners' => $banners,
            // 'homecategories' => $categories,
            // 'homeproductlists' => $productlists,
            // 'homenewcollections' => $newcollections,
            'homesections' => $homesections,
        ]];
        return $information;
    }
}
