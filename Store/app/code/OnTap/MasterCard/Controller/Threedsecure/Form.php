<?php
/**
 * Copyright (c) 2016-2019 Mastercard
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OnTap\MasterCard\Controller\Threedsecure;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutFactory;
use Magento\Checkout\Model\Session;
use OnTap\MasterCard\Gateway\Response\ThreeDSecure\CheckHandler;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;

class Form extends Action
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var Session
     */
    protected $session;

    private $maskedQuoteId;

    /**
     * Acs constructor.
     * @param Context $context
     * @param RawFactory $pageFactory
     * @param LayoutFactory $layoutFactory
     * @param Session $session
     */
    public function __construct(
        Context $context,
        RawFactory $pageFactory,
        LayoutFactory $layoutFactory,
        Session $session,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteId
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $pageFactory;
        $this->layoutFactory = $layoutFactory;
        $this->session = $session;
        $this->maskedQuoteId = $maskedQuoteId;
    }

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        /* @var Template $block */
        $block = $this->layoutFactory
            ->create()
            ->createBlock(\OnTap\MasterCard\Block\Threedsecure\Form::class);
        $quote = $this->session->getQuote();
        
        if ($this->getRequest()->getParam('quote_id')) { // Customize add quote directory in param
            $quote_id = $this->getRequest()->getParam('quote_id');
            $quote->load($quote_id);
            if (!$quote->getId()) {
                $quote_id = $this->maskedQuoteId->execute($quote_id);
                $quote->load($quote_id);
            }
        }

        $payment = $quote->getPayment();

        if ($this->getRequest()->getParam('return_url')) { // Customize 3D secure return url
            $block->setReturnUrl($this->getRequest()->getParam('return_url'));
        }
        if ($this->getRequest()->getParam('return_url_base64')) { // Customize 3D secure return url
            $block->setReturnUrl(base64_decode($this->getRequest()->getParam('return_url_base64')));
        }

        $block
            ->setTemplate('OnTap_MasterCard::threedsecure/form.phtml')
            ->setData($payment->getAdditionalInformation(CheckHandler::THREEDSECURE_CHECK));

        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $block->toHtml()
        );
    }
}
