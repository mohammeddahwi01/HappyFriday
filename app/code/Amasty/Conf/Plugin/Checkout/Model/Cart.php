<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Plugin\Checkout\Model;

use Magento\Checkout\Model\Cart as MagentoCart;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Exception\LocalizedException;

class Cart
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $locale;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $locale,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductFactory $productFactory
    ) {
        $this->locale = $locale;
        $this->productFactory = $productFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param MagentoCart $subject
     * @param \Closure $closure
     * @param int|Product $productInfo
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     * @return MagentoCart
     */
    public function aroundAddProduct(MagentoCart $subject, \Closure $closure, $productInfo, $requestInfo)
    {
        if (isset($requestInfo['configurable-option'])) {
            $storeId = $this->storeManager->getStore()->getId();
            $flagAddedToCart = false;
            foreach ($requestInfo['configurable-option'] as $attributeId => $optionValues) {
                foreach ($optionValues as $optionValue => $qty) {
                    $tmpRequest = $requestInfo;
                    unset($tmpRequest['configurable-option']);

                    if (!isset($tmpRequest['super_attribute'])) {
                        $tmpRequest['super_attribute'] = [];
                    }
                    $tmpRequest['super_attribute'][$attributeId] = (string)$optionValue;

                    $filter = new \Zend_Filter_LocalizedToNormalized(
                        ['locale' => $this->locale->getLocale()]
                    );
                    $qty = $filter->filter($qty);
                    $tmpRequest['qty'] = $qty;
                    if ($qty <= 0) {
                        continue;
                    }

                    if ($productInfo instanceof Product) {
                        $productInfo = $productInfo->getId();
                    }

                    //should reinitialize product( without repository! )
                    $product = $this->productFactory->create();
                    $product->setData('store_id', $storeId)->load($productInfo);

                    $closure($product, $tmpRequest);
                    $flagAddedToCart = true;
                }
            }

            if (!$flagAddedToCart) {
                throw new LocalizedException(__('Please specify the quantity of product(s).'));
            }
        } else {
            $closure($productInfo, $requestInfo);
        }

        return $subject;
    }
}
