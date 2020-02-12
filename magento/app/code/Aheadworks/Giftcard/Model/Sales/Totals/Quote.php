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

        $giftcardProductPrices = 0; // totals of product item type is giftcard
        foreach($items as $item){
            if ($item->getProductType() == 'aw_giftcard'){
                $giftcardProductPrices += $item->getProduct()->getFinalPrice();
            }
        }
        // Apply discount by giftcard value for totals of normal product type (not giftcard) only.
        $baseGrandTotal = $baseGrandTotal - $giftcardProductPrices;
        $grandTotal = $grandTotal - $giftcardProductPrices;

        $baseTotalGiftcardAmount = $totalGiftcardAmount = 0;
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
            $baseGiftcardUsedAmount = min($giftcard->getGiftcardBalance(), $baseGrandTotal);
            $baseGrandTotal -= $baseGiftcardUsedAmount;

            $giftcardUsedAmount = min($this->priceCurrency->convert($baseGiftcardUsedAmount), $grandTotal);
            $grandTotal -= $giftcardUsedAmount;

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

        $realBaseTotalGiftcardAmount = $baseTotalGiftcardAmount;
        $realTotalGiftcardAmount = $totalGiftcardAmount;
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
