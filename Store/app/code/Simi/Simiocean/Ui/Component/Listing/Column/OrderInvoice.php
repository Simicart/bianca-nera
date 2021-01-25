<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Ui\Component\Listing\Column;

// use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
// use \Magento\Framework\Api\SearchCriteriaBuilder;
// use \Magento\Store\Model\StoreManagerInterface;

class OrderInvoice extends Column
{
    /**
     * @var OrderRepositoryInterface
     */
    // protected $_orderRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    // protected $_searchCriteria;
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepository;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_requestInterface;
    /**
     * @var \Magento\Sales\Model\Order\InvoiceFactory
     */
    protected $_invoiceFactory;

    protected $oceanInvoice;
    

    /**
     * Monkey constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepository
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     * @param SearchCriteriaBuilder $criteria
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\View\Asset\Repository $assetRepository,
        \Magento\Framework\App\RequestInterface $requestInterface,
        // SearchCriteriaBuilder $criteria,
        \Magento\Sales\Model\Order\InvoiceFactory $invoiceFactory,
        \Simi\Simiocean\Model\Invoice $oceanInvoice,
        array $components = [],
        array $data = []
    ) {
    
        // $this->_searchCriteria   = $criteria;
        $this->_assetRepository  = $assetRepository;
        $this->_requestInterface = $requestInterface;
        $this->_invoiceFactory     = $invoiceFactory;
        $this->oceanInvoice      = $oceanInvoice;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $params = ['_secure' => $this->_requestInterface->isSecure()];
            foreach ($dataSource['data']['items'] as &$item) {

                

                if (isset($item['entity_id'])) {
                    // $invoice = $this->_invoiceFactory->create()->loadByIncrementId($item['entity_id']);
                    // var_dump('get_class($invoice)');die;
                //     var_dump($invoice->getBaseGrandTotal());die;
                // var_dump($item['increment_id']);die;

                    $oInvoice = $this->oceanInvoice->loadByInvoiceId($item['entity_id']);
                    if ($oInvoice) {
                        $item['ocean_no'] = $oInvoice->getInvoiceNo();
                    }
                }
            }
        }
        return $dataSource;
    }
}
