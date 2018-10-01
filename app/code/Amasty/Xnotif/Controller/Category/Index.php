<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Controller\Category;

use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableModel;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Configurable
     */
    private $configurableBlock;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    private $stockRegistry;

    /**
     * @var \Amasty\Xnotif\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\Xnotif\Plugins\Catalog\Block\Product\AbstractProduct
     */
    private $abstractProduct;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    public function __construct(
        Context $context,
        Configurable $configurableBlock,
        ProductRepositoryInterface $productRepository,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        \Amasty\Xnotif\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        LayoutInterface $layout,
        \Amasty\Xnotif\Plugins\Catalog\Block\Product\AbstractProduct $abstractProduct,
        UrlHelper $urlHelper
    ) {
        parent::__construct($context);
        $this->configurableBlock = $configurableBlock;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->helper = $helper;
        $this->registry = $registry;
        $this->abstractProduct = $abstractProduct;
        $this->layout = $layout;
        $this->urlHelper = $urlHelper;
        $this->jsonEncoder = $jsonEncoder;
    }

    public function execute()
    {
        $aStockStatus = [];
        $product = $this->initProduct();
        if ($product) {
            $tmpProduct = $this->registry->registry('current_product');
            $this->registry->unregister('current_product');
            $this->registry->register('current_product', $product);

            $this->configurableBlock->setProduct($product);
            $allowAttributes = $this->configurableBlock->getAllowAttributes($product);

            foreach ($this->configurableBlock->getAllowProducts() as $simpleProduct) {
                $productId = $simpleProduct->getId();
                $key = [];
                foreach ($allowAttributes as $attribute) {
                    $productAttribute = $attribute->getProductAttribute();
                    $productAttributeId = $productAttribute->getId();
                    $attributeValue = $simpleProduct->getData($productAttribute->getAttributeCode());

                    $options[$productAttributeId][$attributeValue][] = $productId;
                    $options['index'][$productId][$productAttributeId] = $attributeValue;

                    /*Amasty code start - code here for improving performance*/
                    $key[] = $simpleProduct->getData(
                        $attribute->getData('product_attribute')->getData(
                            'attribute_code'
                        )
                    );
                }

                if ($key) {
                    $this->updateXnotifInfo($aStockStatus, $simpleProduct, $key);
                }
            }
            /*Amasty code end*/

            $this->registry->unregister('current_product');
            $this->registry->register('current_product', $tmpProduct);
        }

        return $this->getResponse()->representJson(
            $this->jsonEncoder->encode($aStockStatus)
        );
    }

    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product|false
     */
    private function initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            try {
                return $this->productRepository->getById($productId, false);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * @param array $aStockStatus
     * @param \Magento\Catalog\Model\Product $product
     * @param string $key
     */
    private function updateXnotifInfo(&$aStockStatus, $product, $key)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), 1);
        $verifyStock = $this->helper->verifyStock($stockItem);
        $saleable =  $stockItem->getIsInStock() && $verifyStock;

        if (!$saleable) {
            $stockAlert = $this->abstractProduct->generateAlertHtml(
                $this->layout,
                $product
            );
            $stockAlert = $this->replaceUenc($stockAlert);
            $key = is_array($key) ? implode(',', $key) : $key;
            $aStockStatus[$key] = [
                'is_in_stock' => $saleable,
                'custom_status' => __('Out of Stock')->render(),
                'product_id' => $product->getId(),
                'stockalert' => $stockAlert
            ];
        }
    }

    /**
     * Replace uenc for correct redirect after subscribe
     *
     * @param string $stockAlert
     * @return string
     */
    private function replaceUenc($stockAlert)
    {
        $currentUenc = $this->urlHelper->getEncodedUrl();
        $refererUrl = $this->getRequest()->getHeader('referer');
        $newUenc = $this->urlHelper->getEncodedUrl($refererUrl);
        $stockAlert = str_replace($currentUenc, $newUenc, $stockAlert);

        return $stockAlert;
    }
}
