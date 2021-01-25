<?php

namespace Simi\SimiKnetPayment\Controller;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order\Payment\Transaction\Builder;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Simi\SimiKnetPayment\Helper\Data as HelperData;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Model\OrderNotifier;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\DB\TransactionFactory;
use Magento\Sales\Model\Service\InvoiceService;

/**
 * Class Main
 * @package Simi\SimiKnetPayment\Controller
 */
abstract class Main extends Action
{
    /**
     * @var
     */
    protected $customerSession;
    /**
     * @var Session
     */
    protected $checkoutSession;
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var OrderFactory
     */
    protected $orderFactory;
    /**
     * @var HelperData
     */
    protected $helper;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var
     */
    protected $order;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var Http
     */
    protected $request;
    /**
     * @var Builder
     */
    protected $transactionBuilder;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;
    /**
     * @var OrderNotifier
     */
    protected $orderSender;

    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;
    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;


    /**
     * Main constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param OrderFactory $orderFactory
     * @param Http $request
     * @param Builder $transactionBuilder
     * @param HelperData $helper
     * @param PageFactory $resultPageFactory
     * @param OrderNotifier $orderSender
     * @param InvoiceSender $invoiceSender
     * @param TransactionFactory $transactionFactory
     * @param InvoiceService $invoiceService
     */
    public function __construct(Context $context, Registry $registry, Session $checkoutSession, StoreManagerInterface $storeManager, ScopeConfigInterface $scopeConfig, OrderFactory $orderFactory, Http $request, Builder $transactionBuilder, HelperData $helper, PageFactory $resultPageFactory, OrderNotifier $orderSender, InvoiceSender $invoiceSender, TransactionFactory $transactionFactory, InvoiceService $invoiceService)
    {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->registry = $registry;
        $this->orderFactory = $orderFactory;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->transactionBuilder = $transactionBuilder;
        $this->resultPageFactory = $resultPageFactory;
        $this->invoiceSender = $invoiceSender;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->orderSender = $orderSender;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if ($this->order == null) {
            $session = $this->checkoutSession;
            $this->order = $this->orderFactory->create();
            $this->order->loadByIncrementId($session->getLastRealOrderId());
        }
        return $this->order;
    }

    /**
     * @param $path
     * @return mixed
     */
    protected function getConfig($path)
    {
        $path = 'payment/simiknet/' . $path;
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    protected function getPwaStudioUrl(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(HelperData::XML_PATH_PWA_STUDIO_URL, $storeScope);
    }
}
