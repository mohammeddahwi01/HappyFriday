<?php

namespace Magebees\Navigationmenupro\Controller\Adminhtml\Menudata;

use Magento\Framework\Controller\ResultFactory;

class Parents extends \Magento\Backend\App\Action
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
        $params = $this->getRequest()->getParams();
        $groupId = $this->getRequest()->getParam('group_id');
        $current_menu_id = $this->getRequest()->getParam('current_menu');
        $model = $this->_objectManager->create('Magebees\Navigationmenupro\Model\Menucreator');
        $Parent_item = $model->getParentItems($groupId, $current_menu_id);
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($Parent_item);
        return $resultJson;
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_Navigationmenupro::Navigationmenupro');
    }
}
