<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */
namespace Amasty\Cart\Plugin\CatalogSearch;

class Result
{
    /**
     * @var \Amasty\Cart\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    public function __construct(
        \Amasty\Cart\Helper\Data $helper,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        $this->helper = $helper;
        $this->layoutFactory = $layoutFactory;
    }

    public function afterGetProductListHtml(
        \Magento\CatalogSearch\Block\Result $subject,
        $result
    ) {
        $enable = $this->helper->getModuleConfig('general/enable');

        if ($enable) {
            $layout = $this->layoutFactory->create();
            $block = $layout->createBlock(
                'Amasty\Cart\Block\Config',
                'amasty.cart.config',
                [ 'data' => [] ]
            );

            $html = $block->setPageType('category')->toHtml();
            $result .= $html;
        }

        return  $result;
    }
}
