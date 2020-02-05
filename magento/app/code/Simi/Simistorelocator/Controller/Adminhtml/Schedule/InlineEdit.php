<?php

namespace Simi\Simistorelocator\Controller\Adminhtml\Schedule;

class InlineEdit extends \Simi\Simistorelocator\Controller\Adminhtml\AbstractInlineEdit {

    /**
     * Check the permission to run it.
     *
     * @return bool
     */
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Simi_Simistorelocator::schedule');
    }

}
