<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Plugin\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Ui\Component\Form\Fieldset;
use Amasty\Conf\Helper\Data;

class ConfigurablePanel
{
    private $actionsList = 'Amasty_Conf/components/actions-list';
    /**
     * @param $subject
     * @param $meta
     * @return array
     */
    public function afterModifyMeta($subject, $meta)
    {
        /** @var array $simplePreselectConfig Set data.product scope to simple_preselect attribute */
        $simplePreselectConfig
            = &$meta['configurable']['children']['container_' . Data::PRESELECT_ATTRIBUTE]['arguments']['data']['config'];
        if ($simplePreselectConfig) {
            $simplePreselectConfig['dataScope'] = $meta['configurable']['arguments']['data']['config']['dataScope'];
            unset($meta['configurable']['arguments']['data']['config']['dataScope']);

            /** @var array $actionListConfig Add preselect button in dropdown */
            $actionListConfig
                = &$meta['configurable']['children']['configurable-matrix']['children']['record']['children']['actionsList']['arguments']['data']['config'];
            $actionListConfig['component'] = 'amasty_preselect';
            $actionListConfig['template'] = $this->actionsList;

            /** @var array $configurableConfig Fix bug with merge eav and configurable data */
            $configurableConfig = &$meta['configurable']['arguments']['data']['config'];
            $configurableConfig['componentType'] = Fieldset::NAME;
            $configurableConfig['label'] = __('Configurations');
            $configurableConfig['collapsible'] = true;
            if (isset($configurableConfig['sortOrder'][1])) {
                $configurableConfig['sortOrder'] = $configurableConfig['sortOrder'][1];
            }
        } else {
            unset($meta['configurable']['children']['container_' . Data::PRESELECT_ATTRIBUTE]);
        }

        return $meta;
    }
}
