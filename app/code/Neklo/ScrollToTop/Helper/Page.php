<?php
namespace Neklo\ScrollToTop\Helper;

class Page extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Neklo\ScrollToTop\Helper\Config
     */
    protected $_configHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_page;

    /**
     * @var \Magento\Framework\View\Layout
     */
    protected $_layout;

    public function __construct(
        \Neklo\ScrollToTop\Helper\Config $configHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Layout $layout,
        \Magento\Cms\Model\Page $page,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);


        $this->_configHelper = $configHelper;
        $this->_registry = $registry;
        $this->_layout = $layout;
        $this->_page = $page;
    }

    /**
     * @return bool
     */
    public function isHomePage()
    {
        $currentUrl = $this->_getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        $homePageUrl = $this->_getUrl('');

        return $currentUrl === $homePageUrl;
    }

    /**
     * @return bool
     */
    public function isProductPage()
    {
        $allowedActionNameList = array(
            'catalog_product_view',
        );
        $product = $this->_registry->registry('current_product');

        return in_array($this->_getActionName(), $allowedActionNameList) && $product && $product->getId();
    }

    /**
     * @return int
     */
    public function isCmsPage()
    {
        return $this->_page->getId();
    }

    public function isCategoryPage()
    {
        $allowedActionNameList = array(
            'catalog_category_default',
            'catalog_category_layered',
        );
        $category = Mage::registry('current_category');

        return in_array($this->_getActionName(),
            $allowedActionNameList) && $category && $category->getId();
    }

    public function isCheckoutPage()
    {
        $allowedActionNameList = array(
            'checkout_onepage_index',
            'checkout_multishipping_login',
            'checkout_multishipping_index',
        );

        return in_array($this->_getActionName(), $allowedActionNameList);
    }

    public function isCartPage()
    {
        $allowedActionNameList = array(
            'checkout_cart_index',
        );

        return in_array($this->_getActionName(), $allowedActionNameList);
    }

    public function isAccountPage()
    {
        $customerAccountHandle = 'customer_account';
        if (in_array($customerAccountHandle, $this->_layout->getUpdate()->getHandles())) {
            return true;
        }
        $allowedActionNameList = array(
            'customer_account_logoutSuccess',
            'customer_account_login',
            'customer_account_create',
        );

        return in_array($this->_getActionName(), $allowedActionNameList);
    }

    public function isOtherPage()
    {
        return !$this->isHomePage()
        && !$this->isProductPage()
        && $this->isCmsPage()
        && !$this->isCategoryPage()
        && !$this->isCheckoutPage()
        && !$this->isCartPage()
        && !$this->isAccountPage();
    }

    /**
     * @return string
     */
    protected function _getActionName()
    {
        return $this->_request->getActionName();
    }

}