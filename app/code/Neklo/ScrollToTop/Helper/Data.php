<?php
namespace Neklo\ScrollToTop\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Neklo\ScrollToTop\Helper\Config
     */
    protected $_configHelper;

    public function __construct(
        \Neklo\ScrollToTop\Helper\Config $configHelper,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->_configHelper = $configHelper;

        parent::__construct($context);
    }


    /**
     * @param null $scopeType
     *
     * @return bool
     */
    public function isEnabled($scopeType = null)
    {
        return $this->getConfig()->isEnabled($scopeType);
    }

    /**
     * @return \Neklo\ScrollToTop\Helper\Config
     */
    public function getConfig()
    {
        return $this->_configHelper;
    }

}