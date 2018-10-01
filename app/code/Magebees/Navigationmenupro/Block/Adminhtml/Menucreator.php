<?php
namespace Magebees\Navigationmenupro\Block\Adminhtml;

class Menucreator extends \Magento\Backend\Block\Template
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_menucreator';
        $this->_blockGroup = 'Magebees_Navigationmenupro';
        $this->_headerText = 'Navigation Menu Pro Management';
        parent::_construct();
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $page = $om->get('Magento\Framework\View\Page\Config');
        $page->addPageAsset('Magebees_Navigationmenupro::css/popupbox.css');
        $this->setTemplate('grid.phtml');
    }
    public function group_menu_tree()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menucreatorgroup = $om->get('Magebees\Navigationmenupro\Model\Menucreatorgroup');
        $groupcollection = $menucreatorgroup->getCollection()
        ->setOrder("group_id", "asc");
        $menugroup_backend = $groupcollection->getData();
        $menubackend = "<div id=navmenu class=navmenusorted>";
        foreach ($menugroup_backend as $key => $group) {
            $group_id = $group['group_id'];
            $group_status = $group['status'];
            if ($group_status == "1") {
                $status = ' enabled';
            } elseif ($group_status == "2") {
                $status = ' disabled';
            }
            
            if ($group_id != "0") {
                $group_details = $menucreatorgroup->load($group_id);
                $editgroup_url = $this->getUrl("navigationmenupro/menucreatorgroup/edit/", ["id" => $group_id]);
                /* Add Li class 'mjs-nestedSortable-no-nesting' On the Group Li so can not add the sub child on the Group Li*/
                $menubackend .= "<h2 class='groupTitle' id=".$group_id."><a href=".$editgroup_url." title=".$group_details->getTitle()." class='edit'>Edit</a>".$group_details->getTitle()."</h2>";
                $menubackend .= "<ol class='sortable ui-sortable mjs-nestedSortable-branch mjs-nestedSortable-expanded groupid-".$group_id.' '.$status."' id=groupid-".$group_id.">";
                $menucreator= $om->get('Magebees\Navigationmenupro\Model\Menucreator');
                $menubackend .=$menucreator->getMenuTree($group_id);
                $menubackend .= "</ol>";
            }
        }
        $menubackend .= "</div>";
        return $menubackend;
    }
}
