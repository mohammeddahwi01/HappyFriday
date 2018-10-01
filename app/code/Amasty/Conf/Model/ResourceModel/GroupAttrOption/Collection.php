<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Model\ResourceModel\GroupAttrOption;

use Amasty\Conf\Model\ResourceModel\GroupAttr;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'group_option_id';

    protected function _construct()
    {
        $this->_init(
            'Amasty\Conf\Model\GroupAttrOption',
            'Amasty\Conf\Model\ResourceModel\GroupAttrOption'
        );
    }

    public function getOptionIdsByAttirbuteId($attributeId)
    {
        $this->getSelect()->joinInner(
            ['group_table' => $this->getTable('amasty_conf_group_attr')],
            'group_table.group_id=main_table.group_id AND group_table.attribute_id=' . (int)$attributeId
            . ' AND group_table.enabled=' . GroupAttr::IS_ENABLED,
            ['group_code']
        );

        return $this;
    }
}
