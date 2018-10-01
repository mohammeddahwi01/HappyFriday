<?php
namespace Magebees\Navigationmenupro\Controller\Adminhtml\Menucreatorgroup;

class MassDelete extends \Magento\Backend\App\Action
{
   
    
 
    public function execute()
    {
       
        $groupIds = $this->getRequest()->getParam('navigationmenupro');
        
        if (!is_array($groupIds) || empty($groupIds)) {
            $this->messageManager->addError(__('Please select items.'));
        } else {
            try {
                $count=0;
                 $count=count($groupIds);
                foreach ($groupIds as $groupId) {
                    $group= $this->_objectManager->get('Magebees\Navigationmenupro\Model\Menucreatorgroup')->load($groupId);
                    $group->delete();
                }
                $this->messageManager->addSuccess(
                    __('A total of   '.$count .'  record(s) have been deleted.', count($groupIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
         $this->_redirect('*/*/');
    }
    
    protected function _isAllowed()
    {
        return true;
    }
}
