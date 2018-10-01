<?php
namespace Neklo\ScrollToTop\Block\Adminhtml\System;

class ColorList extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Neklo\ScrollToTop\Model\Source\Color
     */
    protected $_sourceColor;

    /**
     * @var \Neklo\ScrollToTop\Helper\Config
     */
    protected $_configHelper;


    public function __construct(
        \Neklo\ScrollToTop\Helper\Config $configHelper,
        \Neklo\ScrollToTop\Model\Source\Color $sourceColor,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        \Magento\Framework\View\Element\Template::__construct($context, $data);

        $this->_sourceColor = $sourceColor;
        $this->_configHelper = $configHelper;
    }

    /**
     * @return array
     */
    public function getColorList()
    {
        return $this->_sourceColor->getColorList();
    }

    public function getColorInputId()
    {
        return $this->getContainerId() . '_color';
    }

    /**
     * @param null $scopeType
     *
     * @return string
     */
    public function getColor($scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfig()->getColor($scopeType);
    }

    /**
     * @return \Neklo\ScrollToTop\Helper\Config
     */
    public function getConfig()
    {
        return $this->_configHelper;
    }

}
