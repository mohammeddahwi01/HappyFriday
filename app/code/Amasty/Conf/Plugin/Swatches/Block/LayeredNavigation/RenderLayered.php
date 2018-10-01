<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
namespace Amasty\Conf\Plugin\Swatches\Block\LayeredNavigation;

use Magento\Swatches\Block\LayeredNavigation\RenderLayered as MagentoRenderLayered;

class RenderLayered
{
    /**
     * @var \Amasty\Conf\Helper\Data
     */
    private $helper;

    /**
     * @var \Amasty\Conf\Model\ResourceModel\GroupAttr\CollectionFactory
     */
    private $groupCollection;

    /**
     * @var \Amasty\Conf\Model\ResourceModel\GroupAttrOption\CollectionFactory
     */
    private $collectionOptionFactory;

    public function __construct(
        \Amasty\Conf\Helper\Data $helper,
        \Amasty\Conf\Model\ResourceModel\GroupAttrOption\CollectionFactory $collectionOptionFactory,
        \Amasty\Conf\Model\ResourceModel\GroupAttr\CollectionFactory $groupCollection
    ) {
        $this->helper = $helper;
        $this->groupCollection = $groupCollection;
        $this->collectionOptionFactory = $collectionOptionFactory;
    }

    public function afterGetSwatchData(
        MagentoRenderLayered $subject,
        $data
    ) {
        if (!$this->helper->isNavigationEnabled()) {
            $attributeId = $data['attribute_id'];
            $groupOptions = $this->groupCollection->create()->getGroupsByAttributeId($attributeId);
            if ($groupOptions->getSize()) {
                $options = $data['options'];
                $swatches = $data['swatches'];

                foreach ($groupOptions as $groupOption) {
                    $linkToOption = $subject->buildUrl($data['attribute_code'], $groupOption->getGroupCode());
                    $inserted = [
                        $groupOption->getGroupCode() => [
                            'label' => $groupOption->getName(),
                            'link' => $linkToOption,
                            'custom_style' => ''
                        ]
                    ];
                    $position = $groupOption->getPosition();
                    $previousItems = array_slice($options, 0, $position, true);
                    $nextItems     = array_slice($options, $position, null, true);
                    $options =  $previousItems + $inserted + $nextItems;

                    $swatches[$groupOption->getGroupCode()] = $this->getUnusedSwatchGroup($groupOption);
                }

                $duplicatedOptions = $this->collectionOptionFactory->create()
                    ->getOptionIdsByAttirbuteId($attributeId)->getData();
                foreach ($duplicatedOptions as $duplicatedOption) {
                    if (isset($options[$duplicatedOption['option_id']])) {
                        unset($options[$duplicatedOption['option_id']]);
                    }
                    if (isset($swatches[$duplicatedOption['option_id']])) {
                        unset($swatches[$duplicatedOption['option_id']]);
                    }
                }

                $data['options']  = $options;
                $data['swatches'] = $swatches;
            }
        }

        return  $data;
    }

    /**
     * @param $swatchOption
     * @return array
     */
    protected function getUnusedSwatchGroup($swatchOption)
    {
        return [
            "option_id" => $swatchOption->getGroupCode(),
            "type" => $swatchOption->getType(),
            "value" => $swatchOption->getVisual()
        ];
    }
}
