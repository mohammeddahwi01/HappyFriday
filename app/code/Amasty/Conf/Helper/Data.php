<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
namespace Amasty\Conf\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Module\Manager;
use Magento\Catalog\Model\Product;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const FLIPPER_IMAGE_ID = 'amasty_conf_flipper_image';
    const PRESELECT_ATTRIBUTE = 'simple_preselect';
    const MATRIX_ATTRIBUTE = 'amasty_conf_matrix';

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\Json\Decoder
     */
    private $jsonDecoder;

    /**
     * @var \Magento\Swatches\Model\Swatch
     */
    private $swatchAttribute;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Swatches\Model\Swatch $swatchAttribute
    ) {
        parent::__construct($context);
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
        $this->swatchAttribute = $swatchAttribute;
    }

    /**
     * @param $path
     * @param int $storeId
     * @return mixed
     */
    public function getModuleConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            'amasty_conf/' . $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return bool
     */
    public function isFlipperEnabled()
    {
        return (bool)$this->getModuleConfig('general/flipper');
    }

    public function isNavigationEnabled()
    {
        return (bool)$this->_moduleManager->isEnabled('Amasty_Shopby');
    }

    public function decode($data)
    {
        return $this->jsonDecoder->decode($data);
    }

    public function encode($data)
    {
        return $this->jsonEncoder->encode($data);
    }

    public function isShowImageSwatchOnCheckout()
    {
        return $this->getModuleConfig('general/show_in_checkout');
    }

    public function generateColorSwatch($optionId, $value)
    {
        return
            '<div class="swatch-option" option-type="1"
                option-id="' . $optionId . '" option-label="' . $optionId . '" 
                option-tooltip-thumb="" option-tooltip-value="' . $value . '"
                style="float: none; background: ' . $value
                . ' no-repeat center; background-size: initial;"></div>';
    }

    public function generateTextSwatch($optionId, $value)
    {
        return
            '<div class="swatch-option text" style="float: none;" option-type="0" 
                option-id="' . $optionId . '" option-label="' . $optionId . '"
                option-tooltip-thumb="" option-tooltip-value="' . $value . '">'
                . $value . '</div>';
    }

    public function getFormatedSwatchLabel($optionId)
    {
        $result = '';
        $collection = $this->swatchAttribute->getCollection()
            ->addFieldToFilter('option_id', $optionId)
            ->addFieldToFilter('store_id', 0);
        //magento bug - should be $this->storeManager->getStore()->getStoreId()

        if ($collection->getSize()) {
            $option = $collection->getFirstItem();
            switch ($option->getType()) {
                case '1':
                    $result = $this->generateColorSwatch($optionId, $option->getValue());
                    break;
                case '0':
                    $result = $this->generateTextSwatch($optionId, $option->getValue());
                    break;
            }
        }

        return $result;
    }
}
