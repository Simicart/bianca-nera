<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller\Adminhtml\Author;

use Aheadworks\Blog\Model\ResourceModel\Author\Collection;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 * @package Aheadworks\Blog\Controller\Adminhtml\Author
 */
class MassDelete extends AbstractMassAction
{
    /**
     * {@inheritdoc}
     */
    protected function massAction(Collection $collection)
    {
        $deletedRecords = 0;
        foreach ($collection->getAllIds() as $authorId) {
            $this->authorRepository->deleteById($authorId);
            $deletedRecords++;
        }
        if ($deletedRecords) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were deleted.', $deletedRecords));
        } else {
            $this->messageManager->addSuccessMessage(__('No records were deleted.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/');

        return $resultRedirect;
    }
}
