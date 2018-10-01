<?php
namespace Magebees\Navigationmenupro\Controller\Adminhtml\Menucreator;

class DeleteItems extends \Magento\Backend\App\Action
{
    
    public function execute()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            $model= $this->_objectManager->create('Magebees\Navigationmenupro\Model\Menucreator');
            $currentmenu_id = $this->getRequest()->getParam('id');
            $menu_delete_option = $this->getRequest()->getParam('deleteoption');
            $child_menu_item_list = [];
            $child_menu_item = $model->getChildMenuItem($currentmenu_id);
            $child_menu_item_list[$currentmenu_id] = $child_menu_item;
            $delete_menu = [];
            foreach ($child_menu_item as $key => $menuitem) {
                if (!is_array($menuitem)) {
                    array_push($delete_menu, $menuitem);
                }
            }
                 
            if ($menu_delete_option == "deleteparent") {
                try {
                    foreach ($delete_menu as $menuid) {
                        if ($currentmenu_id == $menuid) {
                            $menu= $this->_objectManager->create('Magebees\Navigationmenupro\Model\Menucreator')->load($menuid);
                            $menu->delete();
                        } else {
                            $model= $this->_objectManager->create('Magebees\Navigationmenupro\Model\Menucreator')->load($menuid);
                            if ($model->getParentId() == $currentmenu_id) {
                                $model->setParentId("0");
                                $model->setPosition("0");
                            }
                            $model->save();
                        }
                    }
                        $this->messageManager->addSuccess(__('Group was deleted successfully!'));
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                    $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                }
                        $this->_redirect('*/*/');
            } elseif ($menu_delete_option== "deleteparentchild") {
                try {
                    $count=0;
                    $count=count($delete_menu);
                    $i=0;
                    foreach ($delete_menu as $menuid) {
                        $i++;
                        $menu= $this->_objectManager->create('Magebees\Navigationmenupro\Model\Menucreator')->load($menuid);
                        $menu->delete();
                    }
                    $this->messageManager->addSuccess(
                        __('A total of   '.$count .'  record(s) were successfully deleted.', count($delete_menu))
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                    $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                }
            }
        }
                $this->_redirect('*/*/');
    }
    protected function _isAllowed()
    {
        return true;
    }
}
