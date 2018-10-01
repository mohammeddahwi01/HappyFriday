<?php

namespace Magebees\Navigationmenupro\Block\Adminhtml\System\Config\Form;

class Button extends \Magento\Config\Block\System\Config\Form\Field
{
   
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('refreshmenu.phtml');
    }
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
         return $this->_toHtml();
    }
    public function getAjaxCheckUrl()
    {
        return $this->getUrl('navigationmenupro/menudata/refreshmenu');
    }
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
          ->setData([
          'id'        => 'refresh_button',
          'label'     => __('PUBLISH'),
          'onclick'   => 'javascript:check(); return false;'
          ]);
 
        return $button->toHtml();
    }
}
