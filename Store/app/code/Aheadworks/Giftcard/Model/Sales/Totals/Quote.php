<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Giftcard\Model\Sales\Totals;

use Aheadworks\Giftcard\Api\Data\Giftcard\QuoteInterface as GiftcardQuoteInterface;
use Aheadworks\Giftcard\Model\ResourceModel\Giftcard as ResourceGiftCard;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote as ModelQuote;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Aheadworks\Giftcard\Model\Giftcard\Validator\Quote as GiftcardQuoteValidator;

/**
 * Class Quote
 *
 * @package Aheadworks\Giftcard\Model\Sales\Totals
 */
class Quote extends AbstractTotal
{
    /**
     * @var bool
     */
    private $isFirstTimeResetRun = true;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var GiftcardQuoteValidator
     */
    private $giftcardQuoteValidator;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param GiftcardQuoteValidator $giftcardQuoteValidator
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        GiftcardQuoteValidator $giftcardQuoteValidator
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->giftcardQuoteValidator = $giftcardQuoteValidator;
    }

    /**
     * {@inheritDoc}
     */
    public function collect(
        ModelQuote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $address = $shippingAssignment->getShipping()->getAddress();
        $items = $shippingAssignment->getItems();
        $this->reset($total, $quote, $address);

        if (!count($items)) {
            return $this;
        }

        $baseGrandTotal = $total->getBaseGrandTotal();
        $grandTotal = $total->getGrandTotal();

        if (!$quote->getExtensionAttributes()
            || ($quote->getExtensionAttributes() && !$quote->getExtensionAttributes()->getAwGiftcardCodes())
            || !$baseGrandTotal
        ) {
            $this->reset($total, $quote, $address, true);
            return $this;
        }

        $vendorModelResource = null;
        if (class_exists('\Vnecoms\Vendors\Model\ResourceModel\Vendor')) {
            $vendorModelResource = \Magento\Framework\App\ObjectManager::getInstance()->create('\Vnecoms\Vendors\Model\ResourceModel\Vendor');
        }

        $budgetVendor = array(); // the budget to discount of vendors
        $budgetVendorIdIsSet = array();
        $giftcardProductPrices = 0; // totals of product item type is giftcard
        /** @var \Magento\Quote\Model\Quote\Item[] $items */
        foreach($items as $item){
            if ($item->getProductType() == 'aw_giftcard'){
                $giftcardProductPrices += ($item->getQty() * $item->getProduct()->getFinalPrice());
            } elseif (class_exists('\Vnecoms\Vendors\Model\Vendor') && $item->getVendorId() && $vendorModelResource) {
                $connection = $vendorModelResource->getConnection();
                $select = $connection->select()
                    ->from($vendorModelResource->getTable('ves_vendor_entity'), array('entity_id', 'vendor_id'))
                    ->where('entity_id = :vendor_id')
                    ->limit(1);
                $bind = array('vendor_id' => $item->getVendorId());
                $vendorData = $connection->fetchRow($select, $bind);
                if (isset($vendorData['entity_id']) && isset($vendorData['vendor_id'])) {
                    if (!isset($budgetVendor[$vendorData['vendor_id']][$item->getId()])) {
                        $budgetVendor[$vendorData['vendor_id']][$item->getId()] = 0;
                    }
                    $budgetVendor[$vendorData['vendor_id']][$item->getId()] = ($item->getQty() * $item->getProduct()->getFinalPrice()); // add for multiple items of a vendor
                    $budgetVendorIdIsSet[$item->getVendorId()] = $vendorData['vendor_id'];
                }
            }
        }
        // Apply discount by giftcard value for totals of normal product type (not giftcard) only.
        $baseGrandTotal = $baseGrandTotal - $giftcardProductPrices;
        $grandTotal = $grandTotal - $giftcardProductPrices;

        $vendorItemDiscountAmounts = array(); // amount[vendor_id][item_id] = float
        $vendorItemBaseDiscountAmounts = array(); // amount[vendor_id][item_id] = float
        $baseTotalGiftcardAmount = $totalGiftcardAmount = 0; /* The giftcard total to reduce grand total */
        $giftcards = $quote->getExtensionAttributes()->getAwGiftcardCodes();
        /** @var $giftcard GiftcardQuoteInterface */
        foreach ($giftcards as $giftcard) {
            if ($giftcard->isRemove()) {
                continue;
            }
            $giftcardCode = $giftcard->getGiftcardCode();
            $websiteId = $quote->getStore()->getWebsiteId();
            if ($this->giftcardQuoteValidator->isValid($giftcardCode, $websiteId) == false) {
                $giftcard->setIsRemove(true);
                $giftcard->setIsInvalid(true);
                continue;
            }

            $giftcardUsedAmount = $baseGiftcardUsedAmount = 0;
            $giftcardModel = \Magento\Framework\App\ObjectManager::getInstance()->create('\Aheadworks\Giftcard\Model\Giftcard');
            $giftcardModel->load($giftcard->getGiftcardId());
            if ($giftcardModel->getVendorId()) {
                // Discount for budget of giftcard by vendor
                $giftcardVendorId = $giftcardModel->getVendorId();
                if (isset($budgetVendor[$giftcardVendorId])) {
                    $budgetGiftcardBalance = $giftcard->getGiftcardBalance();
                    foreach($budgetVendor[$giftcardVendorId] as $itemId => $budgetItem){
                        if ($budgetItem > 0) {
                            $itemBaseDiscountAmount = min($giftcard->getGiftcardBalance(), $budgetItem, $budgetGiftcardBalance, $baseGrandTotal);
                            $vendorItemBaseDiscountAmounts[$giftcardVendorId][$itemId] = $itemBaseDiscountAmount;
                            $budgetGiftcardBalance -= $itemBaseDiscountAmount;
                            $baseGiftcardUsedAmount += $itemBaseDiscountAmount;
                            $baseGrandTotal -= $baseGiftcardUsedAmount;
                            $itemDiscountAmount = min($this->priceCurrency->convert($itemBaseDiscountAmount), $grandTotal);
                            $vendorItemDiscountAmounts[$giftcardVendorId][$itemId] = $itemDiscountAmount;
                            $giftcardUsedAmount += $itemDiscountAmount;
                            $grandTotal -= $giftcardUsedAmount;
                        }
                    }
                }
            } else {
                $baseGiftcardUsedAmount = min($giftcard->getGiftcardBalance(), $baseGrandTotal);
                $baseGrandTotal -= $baseGiftcardUsedAmount;
                $giftcardUsedAmount = min($this->priceCurrency->convert($baseGiftcardUsedAmount), $grandTotal);
                $grandTotal -= $giftcardUsedAmount;
            }

            $baseTotalGiftcardAmount += $baseGiftcardUsedAmount;
            $totalGiftcardAmount += $giftcardUsedAmount;

            if ($baseGiftcardUsedAmount <= 0) {
                $giftcard->setIsRemove(true);
            } else {
                $giftcard
                    ->setBaseGiftcardAmount($baseGiftcardUsedAmount)
                    ->setGiftcardAmount($giftcardUsedAmount);
            }
        }

        // Set discount amount for each item
        foreach($items as $item) {
            if (isset($budgetVendorIdIsSet[$item->getVendorId()])) {
                $vendorId = $budgetVendorIdIsSet[$item->getVendorId()]; //not vendor identity_id
                if (isset($vendorItemBaseDiscountAmounts[$vendorId][$item->getId()])) {
                    $baseDiscountAmount = $vendorItemBaseDiscountAmounts[$vendorId][$item->getId()];
                    $item->setBaseDiscountAmount($baseDiscountAmount);
                    $item->setBaseOriginalDiscountAmount($baseDiscountAmount);
                }
                if (isset($vendorItemDiscountAmounts[$vendorId][$item->getId()])) {
                    $discountAmount = $vendorItemDiscountAmounts[$vendorId][$item->getId()];
                    $discountPercent = $item->getRowTotal() > 0 ? $discountAmount / $item->getRowTotal() * 100 : 0;
                    $item->setDiscountPercent($discountPercent);
                    $item->setDiscountAmount($discountAmount);
                    $item->setOriginalDiscountAmount($discountAmount);
                }
                $item->save();
            }
        }

        $realBaseGrandTotal = max($total->getBaseGrandTotal() - $baseTotalGiftcardAmount, 0);
        $realGrandTotal = max($total->getGrandTotal() - $totalGiftcardAmount, 0);

        $this
            ->_addBaseAmount($baseTotalGiftcardAmount)
            ->_addAmount($totalGiftcardAmount);
        $total
            ->setBaseAwGiftcardAmount($baseTotalGiftcardAmount)
            ->setAwGiftcardAmount($totalGiftcardAmount)
            ->setBaseGrandTotal($realBaseGrandTotal)
            ->setGrandTotal($realGrandTotal);
        $quote
            ->setBaseAwGiftcardAmount($baseTotalGiftcardAmount)
            ->setAwGiftcardAmount($totalGiftcardAmount);
        $address
            ->setBaseAwGiftcardAmount($baseTotalGiftcardAmount)
            ->setAwGiftcardAmount($totalGiftcardAmount);

        return $this;
    }

    /**
     * Reset Gift Crad total
     *
     * @param Total $total
     * @param ModelQuote $quote
     * @param AddressInterface $address
     * @param bool $reset
     * @return $this
     */
    private function reset(Total $total, ModelQuote $quote, AddressInterface $address, $reset = false)
    {
        if ($this->isFirstTimeResetRun || $reset) {
            $this->_addAmount(0);
            $this->_addBaseAmount(0);

            $total->setBaseAwGiftcardAmount(0);
            $total->setAwGiftcardAmount(0);

            $quote->setBaseAwGiftcardAmount(0);
            $quote->setAwGiftcardAmount(0);

            $address->setBaseAwGiftcardAmount(0);
            $address->setAwGiftcardAmount(0);

            if ($reset && $quote->getExtensionAttributes() && $quote->getExtensionAttributes()->getAwGiftcardCodes()) {
                $giftcards = $quote->getExtensionAttributes()->getAwGiftcardCodes();
                /** @var $giftcard GiftcardQuoteInterface */
                foreach ($giftcards as $giftcard) {
                    $giftcard->setIsRemove(true);
                }
            }

            $this->isFirstTimeResetRun = false;
        }
        return $this;
    }

    /**
     * Add Gift Card
     *
     * @param ModelQuote $quote
     * @param Total $total
     * @return []
     */
    public function fetch(ModelQuote $quote, Total $total)
    {
        $giftcards = [];
        if ($quote->getExtensionAttributes() && $quote->getExtensionAttributes()->getAwGiftcardCodes()) {
            $giftcards = $quote->getExtensionAttributes()->getAwGiftcardCodes();
        }
        if (!empty($giftcards)) {
            return [
                'code' => $this->getCode(),
                'aw_giftcard_codes' => $giftcards,
                'title' => __('Gift Card'),
                'value' => -$total->getAwGiftcardAmount()
            ];
        }

        return null;
    }
}
