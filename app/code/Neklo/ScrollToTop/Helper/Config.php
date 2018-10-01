<?php
namespace Neklo\ScrollToTop\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const GENERAL_IS_ENABLED = 'neklo_scrolltotop/general/is_enabled';

    const FRONTEND_COLOR = 'neklo_scrolltotop/frontend/color';
    const FRONTEND_POSITION = 'neklo_scrolltotop/frontend/position';

    const DISPLAY_ON_ALL = 'neklo_scrolltotop/display_on/all';
    const DISPLAY_ON_HOME = 'neklo_scrolltotop/display_on/home';
    const DISPLAY_ON_CMS = 'neklo_scrolltotop/display_on/cms';
    const DISPLAY_ON_CATEGORY = 'neklo_scrolltotop/display_on/category';
    const DISPLAY_ON_PRODUCT = 'neklo_scrolltotop/display_on/product';
    const DISPLAY_ON_CHECKOUT = 'neklo_scrolltotop/display_on/checkout';
    const DISPLAY_ON_CART = 'neklo_scrolltotop/display_on/cart';
    const DISPLAY_ON_ACCOUNT = 'neklo_scrolltotop/display_on/account';
    const DISPLAY_ON_OTHER = 'neklo_scrolltotop/display_on/other';

    const MODULE_NAME = 'Neklo_ScrollToTop';

    /**
     * @param \Magento\Store\Model\ScopeInterface::SCOPE_STORE $scopeType
     * @return bool
     */
    public function isEnabled($scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        $isConfigEnabled = $this->scopeConfig->getValue(self::GENERAL_IS_ENABLED, $scopeType);
        $isModuleEnabled = $this->_moduleManager->isEnabled(self::MODULE_NAME);
        $isModuleOutputEnabled = $this->_moduleManager->isOutputEnabled(self::MODULE_NAME);

        return $isConfigEnabled && $isModuleEnabled && $isModuleOutputEnabled;
    }

    /**
     * @param \Magento\Store\Model\ScopeInterface::SCOPE_STORE $scopeType
     *
     * @return bool
     */
    public function getColor($scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(self::FRONTEND_COLOR, $scopeType);
    }

    /**
     * @param \Magento\Store\Model\ScopeInterface::SCOPE_STORE $scopeType
     *
     * @return bool
     */
    public function getPosition($scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(self::FRONTEND_POSITION, $scopeType);
    }

    /**
     * @param \Magento\Store\Model\ScopeInterface::SCOPE_STORE $scopeType
     *
     * @return string
     */
    public function canDisplayOnAll($scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->isSetFlag(self::DISPLAY_ON_ALL, $scopeType);
    }

    /**
     * @param \Magento\Store\Model\ScopeInterface::SCOPE_STORE $scopeType
     *
     * @return bool
     */
    public function canDisplayOnHome($scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->isSetFlag(self::DISPLAY_ON_HOME, $scopeType);
    }

    /**
     * @param \Magento\Store\Model\ScopeInterface::SCOPE_STORE $scopeType
     *
     * @return bool
     */
    public function canDisplayOnCms($scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->isSetFlag(self::DISPLAY_ON_CMS, $scopeType);
    }

    /**
     * @param \Magento\Store\Model\ScopeInterface::SCOPE_STORE $scopeType
     *
     * @return bool
     */
    public function canDisplayOnCategory($scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->isSetFlag(self::DISPLAY_ON_CATEGORY, $scopeType);
    }

    /**
     * @param \Magento\Store\Model\ScopeInterface::SCOPE_STORE $scopeType
     *
     * @return bool
     */
    public function canDisplayOnProduct($scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->isSetFlag(self::DISPLAY_ON_PRODUCT, $scopeType);
    }

    /**
     * @param \Magento\Store\Model\ScopeInterface::SCOPE_STORE $scopeType
     *
     * @return bool
     */
    public function canDisplayOnCheckout($scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->isSetFlag(self::DISPLAY_ON_CHECKOUT, $scopeType);
    }

    /**
     * @param \Magento\Store\Model\ScopeInterface::SCOPE_STORE $scopeType
     *
     * @return bool
     */
    public function canDisplayOnCart($scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->isSetFlag(self::DISPLAY_ON_CART, $scopeType);
    }

    /**
     * @param \Magento\Store\Model\ScopeInterface::SCOPE_STORE $scopeType
     *
     * @return bool
     */
    public function canDisplayOnAccount($scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->isSetFlag(self::DISPLAY_ON_ACCOUNT, $scopeType);
    }

    /**
     * @param \Magento\Store\Model\ScopeInterface::SCOPE_STORE $scopeType
     *
     * @return bool
     */
    public function canDisplayOnOther($scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->isSetFlag(self::DISPLAY_ON_OTHER, $scopeType);
    }

}