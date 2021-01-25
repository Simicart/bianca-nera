<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Observer;

use Magento\Framework\Event\Observer;

class CustomerSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    protected $oceanCustomer;

    /** @var \Simi\Simiocean\Model\Logger */
    protected $logger;
    protected $registry;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Simi\Simiocean\Model\Customer $oceanCustomer,
        \Simi\Simiocean\Model\Logger $logger
    ){
        $this->registry = $registry;
        $this->oceanCustomer = $oceanCustomer;
        $this->logger              = $logger;
    }

    public function execute(Observer $observer)
    {
        if ($this->registry->registry(\Simi\Simiocean\Model\Service\Customer::CUSTOMER_SAVED)) {
            return;
        }
        try{
            $oceanCustomer = $this->oceanCustomer;
            $customer = $observer->getEvent()->getCustomer();
            $collection = $this->oceanCustomer->getCollection();
            $collection->addFieldToFilter('m_customer_id', $customer->getId())
                ->getSelect()
                // ->where('customer_id IS NOT NULL')
                ->order('customer_id desc')
                ->limit(1);
            if ($collection->getSize()) {
                $oceanCustomer = $collection->getFirstItem();
            }
            if ($oceanCustomer && $oceanCustomer->getId()) {
                if ($oceanCustomer->getStatus() != \Simi\Simiocean\Model\SyncStatus::MISSING) {
                    $oceanCustomer
                        ->setEmail($customer->getEmail())
                        ->setFirstName($customer->getFirstname())
                        ->setLastName($customer->getLastname())
                        ->setPoints((int)$customer->getPoints())
                        ->setBirthDate(date_format(date_create($customer->getDob()), 'Y-m-d\TH:i:s'))
                        ->setStatus(\Simi\Simiocean\Model\SyncStatus::PENDING)
                        ->setDirection(\Simi\Simiocean\Model\Customer::DIR_WEB_TO_OCEAN);
                    // Remove phone code
                    if ($customer->getMobilenumber() && $oceanCustomer->getAreaCode() &&
                        strpos($customer->getMobilenumber(), $oceanCustomer->getAreaCode()) == 0) {
                        $oceanCustomer->setMobilePhone(
                            substr($customer->getMobilenumber(), strlen($oceanCustomer->getAreaCode()))
                        );
                    }
                    $oceanCustomer->save();
                }
            } else {
                $date = gmdate('Y-m-d H:i:s');
                $this->oceanCustomer->setId(null)
                    ->setMCustomerId($customer->getId())
                    ->setSyncTime(null)
                    ->setCreatedAt($date)
                    ->setEmail($customer->getEmail())
                    ->setFirstName($customer->getFirstname())
                    ->setLastName($customer->getLastname())
                    ->setPoints((int)$customer->getPoints())
                    ->setMobilePhone($customer->getMobilenumber())
                    ->setBirthDate(date_format(date_create($customer->getDob()), 'Y-m-d\TH:i:s'))
                    ->setStatus(\Simi\Simiocean\Model\SyncStatus::MISSING)
                    ->setDirection(\Simi\Simiocean\Model\Customer::DIR_WEB_TO_OCEAN)
                    ->save();
            }
        }catch(\Exception $e){}
        return;
    }
}