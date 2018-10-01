<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
namespace Amasty\Conf\Plugin\Catalog\Helper\Product;

use Magento\Catalog\Helper\Product\Configuration as MagentoConfiguration;

class Configuration
{
    /**
     * @var \Amasty\Conf\Helper\Data
     */
    private $helper;

    public function __construct(
        \Amasty\Conf\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    public function aroundGetFormattedOptionValue(
        MagentoConfiguration $subject,
        \Closure $proceed,
        $optionValue,
        $params = null
    ) {
        $result = $proceed($optionValue, $params);
        if ($this->helper->isShowImageSwatchOnCheckout()) {
            $optionId = isset($optionValue['option_value'])? $optionValue['option_value']: 0;
            $newLabel = $this->helper->getFormatedSwatchLabel($optionId);
            if ($newLabel) {
                $result['full_view'] = $newLabel;
            }
        }

        return  $result;
    }
}
