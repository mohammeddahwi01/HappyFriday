<?php
namespace Magebees\Navigationmenupro\Controller\Adminhtml\Menucreatorgroup;

class MassLevel extends \Magento\Backend\App\Action
{
   
    
    public function execute()
    {
        
        $delivered_level = (int)$this->getRequest()->getPost('level')-1;
        $groupIds = $this->getRequest()->getParam('navigationmenupro');
        
        if (!is_array($groupIds) || empty($groupIds)) {
            $this->messageManager->addError(__('Please select items.'));
        } else {
            try {
                $count=0;
                 $count=count($groupIds);
                foreach ($groupIds as $groupId) {
                    $group= $this->_objectManager->get('Magebees\Navigationmenupro\Model\Menucreatorgroup')
                        ->load($groupId)
                        ->setLevel($delivered_level)
                        ->save();
                }
                $this->messageManager->addSuccess(
                    __('A total of   '.$count .'  group(s) menu level were successfully updated.', count($groupIds))
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
