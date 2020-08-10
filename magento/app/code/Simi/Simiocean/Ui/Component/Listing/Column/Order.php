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

class Order extends Column
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
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

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
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Simi\Simiocean\Model\Invoice $oceanInvoice,
        array $components = [],
        array $data = []
    ) {
    
        // $this->_searchCriteria   = $criteria;
        $this->_assetRepository  = $assetRepository;
        $this->_requestInterface = $requestInterface;
        $this->_orderFactory     = $orderFactory;
        $this->oceanInvoice      = $oceanInvoice;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $params = ['_secure' => $this->_requestInterface->isSecure()];
            foreach ($dataSource['data']['items'] as &$item) {
                $order = $this->_orderFactory->create()->loadByIncrementId($item['increment_id']);
                $invoices = $order->getInvoiceCollection();
                $oceanInvoiceNo = array();
                if ($invoices->count()) {
                    $invoiceId = null;
                    $status = null;
                    foreach($invoices as $invoice){
                        $status = $this->oceanInvoice->getStatusByInvoiceId($invoice->getId());
                        if ($status) break;
                    }
                    foreach($invoices as $invoice){
                        $oInvoice = $this->oceanInvoice->loadByInvoiceId($invoice->getId());
                        if ($oInvoice) {
                            $oceanInvoiceNo[] = $oInvoice->getInvoiceNo();
                        }
                    }
                    switch ($status) {
                        case \Simi\Simiocean\Model\SyncStatus::SUCCESS:
                            $url = $this->_assetRepository->getUrlWithParams(
                                'Simi_Simiocean::images/yes.png',
                                $params
                            );
                            $text = __('Synced');
                            break;
                        case \Simi\Simiocean\Model\SyncStatus::PENDING:
                            $url = $this->_assetRepository->getUrlWithParams(
                                'Simi_Simiocean::images/waiting.png',
                                $params
                            );
                            $text = __('Waiting');
                            break;
                        case \Simi\Simiocean\Model\SyncStatus::CONFLICT:
                            $url = $this->_assetRepository->getUrlWithParams(
                                'Simi_Simiocean::images/error.png',
                                $params
                            );
                            $text = __('Error');
                            break;
                        case \Simi\Simiocean\Model\SyncStatus::FAILED:
                            $url = $this->_assetRepository->getUrlWithParams(
                                'Simi_Simiocean::images/resync.png',
                                $params
                            );
                            $text = __('Resyncing');
                            break;
                        // case '':
                        //     $url = $this->_assetRepository->getUrlWithParams(
                        //         'Simi_Simiocean::images/never.png',
                        //         $params
                        //     );
                        //     $text = __('With error');
                        //     $alt = $syncData->getMailchimpSyncError();
                        //     break;
                        default:
                            $url = $this->_assetRepository->getUrlWithParams(
                                    'Simi_Simiocean::images/no.png',
                                    $params
                                );;
                            $text = '';
                    }
                    if ($status) {
                        $item['ocean_sync'] = 
                            "<div style='width: 80%;margin: 0 auto;text-align: center'><img src='".$url."' style='border: none; width: 5rem; text-align: center; max-width: 100%' title='' />$text</div>";
                    }

                    $item['ocean_invoice_no'] = implode(',', $oceanInvoiceNo);
                }
            }
        }

        return $dataSource;
    }
}
