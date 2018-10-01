<?php
namespace Neklo\ScrollToTop\Block;

class Arrow extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Neklo\ScrollToTop\Helper\Config
     */
    protected $_configHelper;

    /**
     * @var \Neklo\ScrollToTop\Helper\Page
     */
    protected $_pageHelper;

    public function __construct(
        \Neklo\ScrollToTop\Helper\Config $configHelper,
        \Neklo\ScrollToTop\Helper\Page $pageHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_configHelper = $configHelper;
        $this->_pageHelper = $pageHelper;
    }

    /**
     * @return bool
     */
    public function canShow()
    {
        if (!$this->getConfig()->isEnabled()) {
            return false;
        }
        if ($this->getConfig()->canDisplayOnAll()) {
            return true;
        }
        if ($this->getPageHelper()->isHomePage()) {
            return $this->getConfig()->canDisplayOnHome();
        }
        if ($this->getPageHelper()->isCmsPage()) {
            return $this->getConfig()->canDisplayOnCms();
        }
        if ($this->getPageHelper()->isCategoryPage()) {
            return $this->getConfig()->canDisplayOnCategory();
        }
        if ($this->getPageHelper()->isProductPage()) {
            return $this->getConfig()->canDisplayOnProduct();
        }
        if ($this->getPageHelper()->isCheckoutPage()) {
            return $this->getConfig()->canDisplayOnCheckout();
        }
        if ($this->getPageHelper()->isCartPage()) {
            return $this->getConfig()->canDisplayOnCart();
        }
        if ($this->getPageHelper()->isAccountPage()) {
            return $this->getConfig()->canDisplayOnAccount();
        }

        return $this->getConfig()->canDisplayOnOther();
    }

    /**
     * @param \Magento\Store\Model\ScopeInterface::SCOPE_STORE $scopeType
     *
     * @return string
     */
    public function getColor($scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfig()->getColor($scopeType);
    }

    /**
     * @param \Magento\Store\Model\ScopeInterface::SCOPE_STORE $scopeType
     *
     * @return string
     */
    public function getPosition($scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfig()->getPosition($scopeType);
    }

    /**
     * @return \Neklo\ScrollToTop\Helper\Config
     */
    public function getConfig()
    {
        return $this->_configHelper;
    }

    /**
     * @return \Neklo\ScrollToTop\Helper\Page
     */
    public function getPageHelper()
    {
        return $this->_pageHelper;
    }

}