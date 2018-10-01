<?php
namespace Magebees\Navigationmenupro\Controller\Adminhtml\Menucreatorgroup;

class Delete extends \Magento\Backend\App\Action
{
   
   
    public function execute()
    {
        $groupId = $this->getRequest()->getParam('id');
        try {
                $group = $this->_objectManager->get('Magebees\Navigationmenupro\Model\Menucreatorgroup')->load($groupId);
                $group->delete();
                $this->messageManager->addSuccess(
                    __('Group was deleted successfully!')
                );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }
    protected function _isAllowed()
    {
        return true;
    }
}
