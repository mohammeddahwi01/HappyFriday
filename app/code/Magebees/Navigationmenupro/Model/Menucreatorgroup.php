<?php
namespace Magebees\Navigationmenupro\Model;

class Menucreatorgroup extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Magebees\Navigationmenupro\Model\ResourceModel\Menucreatorgroup');
    }
    public function getAllGroup()
    {
        $groupData =  [];
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menucreatorgroup= $om->create('Magebees\Navigationmenupro\Model\Menucreatorgroup');
        $group_collection =$menucreatorgroup->getCollection();
            $groupData [] =  [
                    'value' => '',
                    'label' => 'Please Select Group',
            ];
            foreach ($group_collection as $group) {
                $groupData [] =  [
                    'value' => $group->getGroupId(),
                    'label' => $group->getTitle(),
                ];
            }
            return $groupData;
    }
}
