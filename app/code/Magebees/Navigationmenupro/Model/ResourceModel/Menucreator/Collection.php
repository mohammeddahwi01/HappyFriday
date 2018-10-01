<?php
namespace Magebees\Navigationmenupro\Model\ResourceModel\Menucreator;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magebees\Navigationmenupro\Model\Menucreator', 'Magebees\Navigationmenupro\Model\ResourceModel\Menucreator');
    }
}
