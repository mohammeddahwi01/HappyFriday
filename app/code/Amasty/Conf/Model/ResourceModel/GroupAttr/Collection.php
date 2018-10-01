<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Model\ResourceModel\GroupAttr;

use Amasty\Conf\Model\ResourceModel\GroupAttr;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected $_idFieldName = 'group_id';

    protected function _construct()
    {
        $this->_init('Amasty\Conf\Model\GroupAttr', 'Amasty\Conf\Model\ResourceModel\GroupAttr');
    }

    public function getGroupsByAttributeId($attributeId)
    {
        $this->addFieldToFilter('attribute_id', (int)$attributeId);
        $this->addFieldToFilter('enabled', GroupAttr::IS_ENABLED);

        return $this;
    }
}
