<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Plugin\Product\View\Type;

use Amasty\Conf\Helper\Data;
use Amasty\Conf\Model\Source\MatrixMode;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as TypeConfigurable;
use Magento\Swatches\Block\Product\Renderer\Listing\Configurable as ListingConfigurable;
use Amasty\Conf\Model\Source\Preselect;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableModel;
use Magento\Catalog\Model\Product;

class Configurable
{
    const PLUGIN_TYPE_PRODUCT = 'product';
    const PLUGIN_TYPE_CATEGORY = 'category';
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    private $output;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    private $stockRegistry;

    /**
     * @var ConfigurableModel
     */
    private $configurableModel;

    /**
     * @var array
     */
    private $preselectedInfo = [];

    public function __construct(
        Data $helper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Output $output,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        ConfigurableModel $configurableModel
    ) {
        $this->helper = $helper;
        $this->layoutFactory = $layoutFactory;
        $this->output = $output;
        $this->coreRegistry = $registry;
        $this->stockRegistry = $stockRegistry;
        $this->configurableModel = $configurableModel;
    }

    /**
     * @param TypeConfigurable $subject
     * @param $result
     * @return string
     */
    public function afterGetJsonConfig(
        TypeConfigurable $subject,
        $result
    ) {
        $availableNames = ['product.info.options.configurable', 'product.info.options.swatches'];
        if ($result && in_array($subject->getNameInLayout(), $availableNames)) {
            $config = $this->helper->decode($result);

            $config['product_information'] = $this->_getProductsInformation($subject);
            $config['show_prices'] = $this->helper->getModuleConfig('general/show_price');
            $config['show_dropdown_prices'] = $this->helper->getModuleConfig('general/dropdown_price');
            $config['change_mouseover'] = $this->helper->getModuleConfig('general/change_mouseover');
            $config['show_out_of_stock'] = $this->helper->getModuleConfig('general/show_out_of_stock');

            $config['matrix'] = $this->isMatrixEnabled($subject->getProduct());
            $config['titles'] = $config['matrix'] ? $this->getMatrixTitles() : [];

            $preselect = $this->helper->getModuleConfig('preselect/preselect');
            if ($preselect || $subject->getProduct()->getSimplePreselect()) {
                $preselectedData = $this->getPreselectData($preselect, $subject);
                $config['preselect']['attributes'] = $preselectedData['attributes'];
            }

            $result = $this->helper->encode($config);
        }

        if ($subject instanceof ListingConfigurable) {
            $config = $this->helper->decode($result);
            $config['change_mouseover'] = $this->helper->getModuleConfig('general/change_mouseover');
            $config['show_out_of_stock'] = $this->helper->getModuleConfig('general/show_out_of_stock');
            $config['product_information'] = $this->_getProductsInformation($subject, self::PLUGIN_TYPE_CATEGORY);
            $preselect = $this->helper->getModuleConfig('preselect/preselect');
            $categoryPreselect = $this->helper->getModuleConfig('preselect/preselect_category');
            if (($preselect || $subject->getProduct()->getSimplePreselect())
                && $categoryPreselect
            ) {
                $preselectedData = $this->getPreselectData($preselect, $subject);
                $config['preselect']['attributes'] = $preselectedData['attributes'];
                $config['blockedImage'] = true;
            }
            $config['preselected'] = false;
            $result = $this->helper->encode($config);
        }

        return $result;
    }

    /**
     * @param Product $product
     * @return bool
     */
    private function isMatrixEnabled(\Magento\Catalog\Model\Product $product)
    {
        $setting = $this->helper->getModuleConfig('matrix/enable');

        return $setting == MatrixMode::YES_FOR_ALL
            || ($setting == MatrixMode::YES && $product->getData(Data::MATRIX_ATTRIBUTE));
    }

    /**
     * @return array
     */
    private function getMatrixTitles()
    {
        $result = [
            'attribute' => __('Option'),
            'price' => __('Price'),
            'qty_available' => __('Available'),
            'qty' => __('Qty'),
            'subtotal' => __('Subtotal')
        ];

        if (!$this->isShowQtyAvailable()) {
            unset($result['qty_available']);
        }

        if (!$this->isShowSubtotal()) {
            unset($result['subtotal']);
        }

        return $result;
    }

    /**
     * @return bool
     */
    private function isShowQtyAvailable()
    {
        return (bool)$this->helper->getModuleConfig('matrix/available_qty');
    }

    /**
     * @return bool
     */
    private function isShowSubtotal()
    {
        return (bool)$this->helper->getModuleConfig('matrix/subtotal');
    }

    /**
     * @param TypeConfigurable $subject
     * @return array
     */
    protected function _getProductsInformation(TypeConfigurable $subject, $type = self::PLUGIN_TYPE_PRODUCT)
    {
        $info = [];
        $reloadValues = $this->helper->getModuleConfig('reload/content');
        if ($reloadValues && strpos($reloadValues, 'none') === false) {
            $reloadValues = explode(',', $reloadValues);

            $info['default'] = $this->_getProductInfo($subject->getProduct(), $reloadValues, $type);
            foreach ($subject->getAllowProducts() as $product) {
                $info[$product->getId()] = $this->_getProductInfo($product, $reloadValues, $type);
            }
        }

        return $info;
    }

    /**
     * @param $product
     * @param $reloadValues
     * @return array
     */
    protected function _getProductInfo($product, $reloadValues, $type)
    {
        $productInfo = [];
        if ($type == self::PLUGIN_TYPE_PRODUCT) {

            $layout = $this->layoutFactory->create();
            foreach ($reloadValues as $reloadValue) {
                $selector = $this->helper->getModuleConfig('reload/' . $reloadValue);
                if (!$selector) {
                    continue;
                }
                if ($reloadValue == 'attributes') {
                    $block = $layout->createBlock(
                        'Magento\Catalog\Block\Product\View\Attributes',
                        'product.attributes',
                        ['data' => []]
                    )->setTemplate('product/view/attributes.phtml');

                    $currentProduct = $this->coreRegistry->registry('product');
                    $this->coreRegistry->unregister('product');
                    $this->coreRegistry->register('product', $product);

                    $value = $block->setProduct($product)->toHtml();

                    $this->coreRegistry->unregister('product');
                    $this->coreRegistry->register('product', $currentProduct);
                } else {
                    $value = $this->output->productAttribute($product, $product->getData($reloadValue), $reloadValue);
                }
                if ($value) {
                    $productInfo[$reloadValue] = [
                        'selector' => $selector,
                        'value' => $value
                    ];
                }
            }
        }

        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $productInfo['is_in_stock'] = $stockItem->getIsInStock();
        $productInfo['qty'] = $stockItem->getQty();

        return $productInfo;
    }

    /**
     * @param integer $preselect
     * @param TypeConfigurable $subject
     * @return array
     */
    public function getPreselectData($preselect, $subject)
    {
        $productId = $subject->getProduct()->getId();
        if (!isset($this->preselectedInfo[$productId])) {
            $allowedAttributes = $subject->getAllowAttributes();
            $selectedOptions = [];
            $selectedProduct = null;

            if ($sku = $subject->getProduct()->getSimplePreselect()) {
                foreach ($subject->getAllowProducts() as $product) {
                    if ($product->getSku() == $sku) {
                        $selectedProduct = $product;
                        break;
                    }
                }
            } else {
                switch ($preselect) {
                    case Preselect::FIRST_OPTIONS:
                        foreach ($allowedAttributes as $attribute) {
                            $productAttribute = $attribute->getProductAttribute();
                            $attributeCode = $productAttribute->getAttributeCode();
                            $attributeId = $productAttribute->getId();
                            if (isset($attribute['options'][0]['value_index'])) {
                                $value = $attribute['options'][0]['value_index'];
                                $selectedOptions[$attributeCode] = $value;
                                $selectedIdsOptions[$attributeId] = $value;
                            }
                        }
                        if (isset($selectedIdsOptions)) {
                            $selectedProduct = $this->configurableModel->getProductByAttributes(
                                $selectedIdsOptions,
                                $subject->getProduct()
                            );
                        }
                        break;
                    case Preselect::CHEAPEST:
                        $selectedProduct = $this->getCheapestProduct($subject->getAllowProducts());
                        break;
                }
            }
            if (!$selectedOptions && $selectedProduct) {
                $selectedOptions = $this->getAttributesForProduct($allowedAttributes, $selectedProduct);
            }
            $this->preselectedInfo[$productId] = [
                'attributes' => $selectedOptions,
                'product' => $selectedProduct
            ];
        }

        return $this->preselectedInfo[$productId];
    }

    /**
     * @param array $allowedAttributes
     * @param Product $product
     * @return array
     */
    private  function getAttributesForProduct($allowedAttributes, $product)
    {
        $selectedOptions = [];
        foreach ($allowedAttributes as $attribute) {
            $attributeCode = $attribute->getProductAttribute()->getAttributeCode();
            if ($attributeValue = $product->getData($attributeCode)) {
                $selectedOptions[$attributeCode] = $attributeValue;
            }
        }

        return $selectedOptions;
    }

    /**
     * Return product with minimal price
     *
     * @param $allowedProducts
     * @return null|Product
     */
    private function getCheapestProduct($allowedProducts)
    {
        $selectedProduct = null;

        foreach ($allowedProducts as $product) {
            if (!$selectedProduct
                || $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue()
                < $selectedProduct->getPriceInfo()->getPrice('final_price')->getAmount()->getValue()
            ) {
                $selectedProduct = $product;
            }
        }

        return $selectedProduct;
    }
}
