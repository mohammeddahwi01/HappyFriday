<?php
namespace Magebees\Navigationmenupro\Model\ResourceModel\Menucreatorgroup;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magebees\Navigationmenupro\Model\Menucreatorgroup', 'Magebees\Navigationmenupro\Model\ResourceModel\Menucreatorgroup');
    }
}
