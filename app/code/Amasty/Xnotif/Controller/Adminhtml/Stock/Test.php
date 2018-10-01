<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Controller\Adminhtml\Stock;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;

class Test extends \Magento\Backend\App\Action
{
    /**
     * @var \Amasty\Xnotif\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Amasty\Xnotif\Model\Observer
     */
    private $observer;

    public function __construct(
        Action\Context $context,
        \Amasty\Xnotif\Model\Observer $observer,
        \Amasty\Xnotif\Model\ResourceModel\Product\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->observer = $observer;
    }

    public function execute()
    {
        $count = 0;
        try {
            $selected = $this->getRequest()->getParam('massaction');
            if ($selected && !empty($selected)) {
                $collection = $this->collectionFactory->create()
                    ->joinStockTable()
                    ->addFieldToFilter('entity_id', $selected);

                $collectionSize = $collection->getSize();
                if ($collectionSize) {
                    foreach ($collection as $alert) {
                        $this->observer->sendTestNotification($alert);
                        $count++;
                    }
                } else {
                    $this->messageManager->addErrorMessage(
                        __('Please select item(s).')
                    );
                }
            }

        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        if ($count) {
            $this->messageManager->addSuccessMessage(__('Test notification has been successfully sent.'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Amasty_Xnotif::stock'
        );
    }
}
