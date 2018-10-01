<?php

namespace Magebees\Navigationmenupro\Controller\Adminhtml\Menucreator;

class EditForm extends \Magento\Backend\App\Action
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
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Magebees\Navigationmenupro\Model\Menucreator')->load($id);
        $this->getResponse()->setBody(json_encode($model->getData()));
    }
    protected function _isAllowed()
    {
        return true;
    }
}
