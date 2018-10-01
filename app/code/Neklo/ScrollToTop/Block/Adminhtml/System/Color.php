<?php
namespace Neklo\ScrollToTop\Block\Adminhtml\System;


class Color extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setScope(false);
        $element->setCanUseWebsiteValue(false);
        $element->setCanUseDefaultValue(false);

        return parent::render($element);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        /** @var \Neklo\ScrollToTop\Block\Adminhtml\System\ColorList $colorList */
        $colorList = $this->getLayout()->createBlock('\Neklo\ScrollToTop\Block\Adminhtml\System\ColorList',
            'neklo_scrolltotop_colorlist');
        $colorList->setTemplate('system/colorlist.phtml');
        $colorList->setContainerId($element->getContainer()->getHtmlId());

        return $colorList->toHtml();
    }

}
