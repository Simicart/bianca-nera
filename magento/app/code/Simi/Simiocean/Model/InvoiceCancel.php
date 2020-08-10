<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simi\Simiocean\Model;

use Magento\Framework\Model\AbstractModel;

class InvoiceCancel extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Simi\Simiocean\Model\ResourceModel\InvoiceCancel::class);
    }

    /**
     * Get invoice sync status by invoice id
     * @param int $invoiceId
     * @return string
     */
    public function getStatusByInvoiceId($invoiceId){
        if ($invoiceId) {
            $connection = $this->getResource()->getConnection();
            $bind = ['invoice_id' => $invoiceId];
            $select = $connection->select()
                ->from($this->getResource()->getMainTable(), 'status')
                ->where('invoice_id = :invoice_id')
                // ->where('invoice_no IS NOT NULL')
                ->limit(1);
            return $connection->fetchOne($select, $bind);
        }
        return '';
    }
}