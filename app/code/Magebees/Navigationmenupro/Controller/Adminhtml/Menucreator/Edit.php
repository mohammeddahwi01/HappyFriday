<?php

namespace Magebees\Navigationmenupro\Controller\Adminhtml\Menucreator;

class Edit extends \Magento\Backend\App\Action
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
        $model = $this->_objectManager->create('Magebees\Navigationmenupro\Model\Menucreator');
        $registryObject = $this->_objectManager->create('Magento\Framework\Registry');
        
        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('Menu Item Information Not Available.'));
                $this->_redirect('*/*/');
                return;
            }
        }
        // 3. Set entered data if was error when we do save
        
        $data = $this->_objectManager->create('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $registryObject->register('menucreator', $model);
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
