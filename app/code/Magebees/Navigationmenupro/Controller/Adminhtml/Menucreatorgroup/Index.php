<?php

namespace Magebees\Navigationmenupro\Controller\Adminhtml\Menucreatorgroup;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
    
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magebees_Navigationmenu::managegroup');
        $resultPage->getConfig()->getTitle()->prepend(__('Navigation Menu Pro Group Management'));

        return $resultPage;
    }
    protected function _isAllowed()
    {
        return true;
    }
}
