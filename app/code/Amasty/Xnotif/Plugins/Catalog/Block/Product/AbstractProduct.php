<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Plugins\Catalog\Block\Product;

use Magento\Catalog\Block\Product\AbstractProduct as ProductBlock;
use Magento\Catalog\Model\Product as ProductModel;
use Amasty\Xnotif\Block\Catalog\Category\StockSubscribe;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableModel;

class AbstractProduct
{
    /**
     * @var string
     */
    private $loggedTemplate;

    /**
     * @var string
     */
    private $notLoggedTemplate;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $sessionFactory;

    /**
     * @var \Magento\ProductAlert\Helper\Data
     */
    private $alertHelper;

    /**
     * @var \Amasty\Xnotif\Helper\Data
     */
    private $xnotifHelper;

    /**
     * @var ProductModel|null
     */
    private $product;

    /**
     * @var array
     */
    private $processedProducts = [];

    /**
     * @var string
     */
    private $blockName;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    public function __construct(
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\ProductAlert\Helper\Data $alertHelper,
        \Amasty\Xnotif\Helper\Data $xnotifHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->loggedTemplate = 'Magento_ProductAlert::product/view.phtml';
        $this->notLoggedTemplate = 'Amasty_Xnotif::category/subscribe.phtml';
        $this->sessionFactory = $sessionFactory;
        $this->alertHelper = $alertHelper;
        $this->xnotifHelper = $xnotifHelper;
        $this->blockName = 'category.subscribe.block';
        $this->registry = $registry;
        $this->urlBuilder = $urlBuilder;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * @param ProductBlock $subject
     * @param ProductModel $product
     * @return array
     */
    public function beforeGetProductDetailsHtml(ProductBlock $subject, ProductModel $product)
    {
        $this->setProduct($product);

        return [$product];
    }

    /**
     * @param ProductBlock $subject
     * @param string $result
     * @return string
     */
    public function afterGetProductDetailsHtml(ProductBlock $subject, $result)
    {
        if ($this->enableSubscribe()) {
            $result .= $this->getSubscribeHtml($subject->getLayout());
        }

        return $result;
    }

    public function beforeGetReviewsSummaryHtml(
        ProductBlock $subject,
        ProductModel $product,
        $templateType = false,
        $displayIfNoReviews = false
    ) {
        $this->setProduct($product);

        return [$product, $templateType, $displayIfNoReviews];
    }

    public function afterGetReviewsSummaryHtml(ProductBlock $subject, $result)
    {
        /** for category subscribe field render in afterGetProductDetailsHtml method */
        if ($subject->getNameInLayout() != 'category.products.list'
            && $this->enableSubscribe()
        ) {
            $result .= $this->getSubscribeHtml($subject->getLayout());
        }

        return $result;
    }

    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @return string
     */
    public function getSubscribeHtml($layout)
    {
        $html = '';

        if (!$this->getProduct()->isSaleable()) {
            $html = $this->generateAlertHtml($layout, $this->getProduct());
        }

        if ($this->getProduct()->getTypeId() == ConfigurableModel::TYPE_CODE) {
            $html .= $this->generateConfigurableHtml($layout);
        }

        $this->processedProducts[] = $this->getProduct()->getId();

        return $html;
    }

    /**
     * @return string
     */
    private function generateConfigurableHtml()
    {
        $productId = $this->getProduct()->getId();
        $jsonConfig = $this->generateJsonConfig($productId);

        return '<div class="amxnotif-category-container" id="amxnotif-category-container-'
            . $productId . '"'
            . ' data-mage-init=\'{ "Amasty_Xnotif/js/category/configurable":' . $jsonConfig . ' }\'>'
            . '</div>';
    }

    /**
     * @param $productId
     * @return bool|string
     */
    private function generateJsonConfig($productId)
    {
        return $this->jsonEncoder->encode([
            'product' => $productId,
            'url' => $this->urlBuilder->getUrl('xnotif/category/index')
        ]);
    }
    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param ProductModel $product
     * @return string
     */
    public function generateAlertHtml($layout, $product)
    {
        $subscribeBlock = $layout->getBlock($this->blockName);
        if (!$subscribeBlock) {
            $subscribeBlock = $layout->createBlock(StockSubscribe::class, 'category.subscribe.block');
        }

        $currentProduct = $this->registry->registry('current_product');

        /*check if it is child product for replace product registered to child product.*/
        $isChildProduct = $currentProduct && ($currentProduct->getId() != $product->getId());
        if ($isChildProduct) {
            $subscribeBlock->setData('parent_product_id', $currentProduct->getId());
            $subscribeBlock->setOriginalProduct($product);
        }

        if ($this->sessionFactory->create()->isLoggedIn()) {
            $subscribeBlock->setTemplate($this->loggedTemplate);
            $subscribeBlock->setHtmlClass('alert stock link-stock-alert');
            $subscribeBlock->setSignupLabel(
                __('Sign up to get notified when this configuration is back in stock')
            );
            $subscribeBlock->setSignupUrl(
                $this->alertHelper->setProduct($product)->getSaveUrl('stock')
            );
        } else {
            $subscribeBlock->setTemplate($this->notLoggedTemplate);
            $subscribeBlock->setOriginalProduct($product);
        }

        return $subscribeBlock->toHtml();
    }

    /**
     * Check if need render subscribe block for current product
     *
     * @return bool
     */
    private function enableSubscribe()
    {
        $result = $this->xnotifHelper->allowForCurrentCustomerGroup('stock')
            && $this->xnotifHelper->getModuleConfig('stock/subscribe_category')
            && !in_array($this->getProduct()->getId(), $this->processedProducts)
            && !$this->registry->registry('current_product');

        return $result;
    }

    /**
     * @return ProductModel|null
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param ProductModel $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }
}
