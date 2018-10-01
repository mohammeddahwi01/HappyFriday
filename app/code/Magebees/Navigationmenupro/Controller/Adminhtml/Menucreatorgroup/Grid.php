<?php
namespace  Magebees\Navigationmenupro\Controller\Adminhtml\Menucreatorgroup;

class Grid extends \Magento\Backend\App\Action
{

    public function execute()
    {
        
            $this->getResponse()->setBody(
                $this->_view->getLayout()->createBlock('Magebees\Navigationmenupro\Block\Adminhtml\Menucreatorgroup\Grid')->toHtml()
            );
    }
    protected function _isAllowed()
    {
        return true;
    }
}
