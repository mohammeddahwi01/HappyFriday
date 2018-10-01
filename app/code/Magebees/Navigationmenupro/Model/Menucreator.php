<?php

namespace Magebees\Navigationmenupro\Model;

class Menucreator extends \Magento\Framework\Model\AbstractModel
{

   
    protected $parentitems = [];
    protected $optionData = "";
    protected $category_list = [];
    protected $child_menu_items = [];
    protected $item_type_label = "";
    public function _construct()
    {
        parent::_construct();
        $this->_init('Magebees\Navigationmenupro\Model\ResourceModel\Menucreator');
    }
    public function getMenuGroup()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menucreator= $om->create('Magebees\Navigationmenupro\Model\Menucreator');
        $menu_group=$menucreator->getCollection()->addFieldToSelect('group_id')->distinct(true);
        return $menu_group;
    }
    public function getMenuitem()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menucreator= $om->create('Magebees\Navigationmenupro\Model\Menucreator');
        $menu_items=$menucreator->getCollection()->setOrder("group_id", "asc")->setOrder("position", "asc");
        return $menu_items;
    }
    public function getChildMenuCollection($parentId)
    {
        
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menucreator= $om->create('Magebees\Navigationmenupro\Model\Menucreator');
        $chilMenu= $menucreator->getCollection()->setOrder("position", "asc");
        $chilMenu->addFieldToFilter('parent_id', $parentId);
        return $chilMenu;
    }
    public function getchild($parentID)
    {
        
            $childCollection=$this->getChildMenuCollection($parentID);
        foreach ($childCollection as $value) {
            $menuId = $value->getMenuId();
            //Check this menu has child or not
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $helper= $om->create('Magebees\Navigationmenupro\Helper\Data');
            $this->optionData =$helper->getMenuSpace($menuId);
            $this->parentoption[$menuId] = ['title' => '----' . $this->optionData['blank_space'] .
            $value->getTitle(), 'group_id' => $value->getGroupId(), 'level' => $this->optionData['level']];
            $hasChild = $this->getChildMenuCollection($menuId);
            if (count($hasChild)>0) {
                $this->getchild($menuId);
            }
        }
    }
    
    public function getNewFileName($destFile)
    {
        $fileInfo = pathinfo($destFile);
        if (file_exists($destFile)) {
            $index = 1;
            $baseName = $fileInfo['filename'] . '.' . $fileInfo['extension'];
            while (file_exists($fileInfo['dirname'] . DIRECTORY_SEPARATOR . $baseName)) {
                $baseName = $fileInfo['filename']. '_' . $index . '.' . $fileInfo['extension'];
                $index ++;
            }
            $destFileName = $baseName;
        } else {
            return $fileInfo['basename'];
        }

        return $destFileName;
    }
    function getCategorieslistform($parentId, $isChild)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $model_cat= $om->create('Magento\Catalog\Model\Category');
        $allCats = $model_cat->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('include_in_menu', ['eq' => 1])
                ->addAttributeToFilter('is_active', ['eq' => 1])
                ->addAttributeToFilter('parent_id', ['eq' => $parentId])
                ->addAttributeToSort('position', 'asc');
               
        $class = ($isChild) ? "sub-cat-list" : "cat-list";

        foreach ($allCats as $category) {
            $lable = '';
            if ($category->getLevel() > 2) {
                $lable = '';
                for ($i=2; $i<=$category->getLevel(); $i++) {
                    $lable .= "\t".' -';
                }
            }
            $lable = ($lable) ? $lable : "";
            $html = isset($html) ? $html : "";
        ?>
    
        <?php   $this->category_list[] = [
                    'value' => $category->getId(),
                    'label' => $lable . " ".$category->getName(),
                ];
if ($class == "sub-cat-list") {
            $html .= '<option value="'.$category->getId().'">'.$lable." ".$category->getName().' </option>';
} elseif ($class == "cat-list") {
            $html .= '<option value="'.$category->getId().'">'.$lable." ".$category->getName().'</option>';
}
           /*Remove Ul & Li End*/
            $lable = '';
            $subcats = $category->getChildren();
if ($subcats != '') {
    $html .= $this->getCategorieslistform($category->getId(), true);
}
        }
        return $this->category_list;
    }
    function getCategorieslist($parentId, $isChild)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $model_cat= $om->create('Magento\Catalog\Model\Category');
        $allCats = $model_cat->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('parent_id', ['eq' => $parentId])
                ->addAttributeToSort('position', 'asc');
              
        $class = ($isChild) ? "sub-cat-list" : "cat-list";
        $html = isset($html) ? $html : '';
        foreach ($allCats as $category) {
            if ($category->getLevel() > 2) {
                $lable = '';
                for ($i=2; $i<=$category->getLevel(); $i++) {
                    $lable .= "\t".' -';
                }
            } else {
                $lable = '';
            }

            ?>
            <?php
            if ($class == "sub-cat-list") {
                $html .= '<option value="'.$category->getId().'">'.$lable." ".$category->getName().' </option>';
            } elseif ($class == "cat-list") {
                $html .= '<option value="'.$category->getId().'">'.$lable." ".$category->getName().'</option>';
            }
               /*Remove Ul & Li End*/
             $lable = '';
            $subcats = $category->getChildren();
            if ($subcats != '') {
                $html .= $this->getCategorieslist($category->getId(), true);
            }
        }
        return $html;
    }

/*Display All the Parent Items On the Form Parent Item Drop Down*/
    public function getParentItems($group_id, $current_menu_id)
    {
        $cur_menu_id = $current_menu_id;
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menucreator= $om->create('Magebees\Navigationmenupro\Model\Menucreator');
        $allParent = $menucreator->getCollection()
                    ->addFieldToFilter('parent_id', "0")
                    ->setOrder("position", "asc")
                    ->addFieldToFilter('group_id', $group_id);
        $html = isset($html) ? $html : '';
        /* Use Current_menu_id to select the current Parent Element when the page is load for the edit the menu items*/
        $Currentmenu = $menucreator->load($current_menu_id);
        $current_parent_item = $Currentmenu->getParentId();
        if ($current_parent_item == "0") {
            $html = '<option value="">Please Select Parent</option><option value="0" selected>Root</option>';
        } else {
            $html = '<option value="">Please Select Parent</option><option value="0">Root</option>';
        }
        foreach ($allParent as $item) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $helper= $om->create('Magebees\Navigationmenupro\Helper\Data');
            $space = $helper->getMenuSpace($item->getMenuId());
            if ($cur_menu_id != $item->getMenuId()) {
                if ($current_parent_item == $item->getMenuId()) {
                    $html .= '<option value="'.$item->getMenuId().'" selected>'.$space.$item->getTitle().' </option>';
                } else {
                    $html .= '<option value="'.$item->getMenuId().'">'.$space.$item->getTitle().' </option>';
                }
            }

            $hasChild = $this->getChildMenuCollection($item->getMenuId());
            if (count($hasChild)>0) {
                $html .= $this->getChildlist($item->getMenuId(), true, $cur_menu_id);
            }
        }
        return $html;
    }
    function getChildlist($parentId, $isChild, $cur_menu_id)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menucreator= $om->create('Magebees\Navigationmenupro\Model\Menucreator');
        $allChild =$menucreator->getCollection()
                    ->setOrder("position", "asc")
                    ->addFieldToFilter('parent_id', $parentId);
        $Currentmenu = $menucreator->load($cur_menu_id);
        $current_parent_item = $Currentmenu->getParentId();

        $html = isset($html) ? $html : '';
        foreach ($allChild as $item) {
            $helper= $om->create('Magebees\Navigationmenupro\Helper\Data');
            $space = $helper->getMenuSpace($item->getMenuId());
            if ($cur_menu_id != $item->getMenuId()) {
                if ($current_parent_item == $item->getMenuId()) {
                    $html .= '<option value="'.$item->getMenuId().'" selected>'.$space.$item->getTitle().' </option>';
                } else {
                    $html .= '<option value="'.$item->getMenuId().'">'.$space.$item->getTitle().'</option>';
                }
            }
            $hasChild = $this->getChildMenuCollection($item->getMenuId());
            if (count($hasChild)>0) {
                $html .= $this->getChildlist($item->getMenuId(), true, $cur_menu_id);
            }
        }
        return $html;
    }
/*Here We use Another Function To Fatch the Parent Drop down value when we directly add the Sub Child of the Parent Items*/
    public function getAddSubParentItems($group_id, $current_menu_id)
    {
        $cur_menu_id = $current_menu_id;
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menucreator= $om->create('Magebees\Navigationmenupro\Model\Menucreator');
        $allParent = $menucreator->getCollection()
                    ->addFieldToFilter('parent_id', "0")
                    ->setOrder("position", "asc")
                    ->addFieldToFilter('group_id', $group_id);

        $html = '<option value="">Please Select Parent</option><option value="0">Root</option>';

        foreach ($allParent as $item) {
            $helper= $om->create('Magebees\Navigationmenupro\Helper\Data');
            $space = $helper->getMenuSpace($item->getMenuId());

            if ($cur_menu_id == $item->getMenuId()) {
                $html .= '<option value="'.$item->getMenuId().'" selected>'.$space.$item->getTitle().' </option>';
            } else {
                $html .= '<option value="'.$item->getMenuId().'">'.$space.$item->getTitle().' </option>';
            }

            $hasChild = $this->getChildMenuCollection($item->getMenuId());
            if (count($hasChild)>0) {
                $html .= $this->getAddSubChildlist($item->getMenuId(), true, $cur_menu_id);
            }
        }
        return $html;
    }
    function getAddSubChildlist($parentId, $isChild, $cur_menu_id)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menucreator= $om->create('Magebees\Navigationmenupro\Model\Menucreator');
        $allChild = $menucreator->getCollection()
                    ->setOrder("position", "asc")
                    ->addFieldToFilter('parent_id', $parentId);
        $Currentmenu = $menucreator->load($cur_menu_id);
        $current_parent_item = $Currentmenu->getParentId();
        $html = isset($html) ? $html : '';

        foreach ($allChild as $item) {
            $helper= $om->create('Magebees\Navigationmenupro\Helper\Data');
            $space = $helper->getMenuSpace($item->getMenuId());

            if ($cur_menu_id == $item->getMenuId()) {
                $html .= '<option value="'.$item->getMenuId().'" selected>'.$space.$item->getTitle().' </option>';
            } else {
                $html .= '<option value="'.$item->getMenuId().'">'.$space.$item->getTitle().'</option>';
            }
            $hasChild = $this->getChildMenuCollection($item->getMenuId());
            if (count($hasChild)>0) {
                $html .= $this->getAddSubChildlist($item->getMenuId(), true, $cur_menu_id);
            }
        }
        return $html;
    }
    public function getMenuTree($group_id)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menucreator= $om->create('Magebees\Navigationmenupro\Model\Menucreator');
        $allParent =$menucreator->getCollection()
                    ->addFieldToFilter('parent_id', "0")
                    ->setOrder("position", "asc")
                    /*->setOrder("created_time","asc")*/
                    ->addFieldToFilter('group_id', $group_id);
        $html = isset($html) ? $html : '';
        foreach ($allParent as $item) {
            $backend_helper= $om->create('Magento\Backend\Helper\Data');

            $url = $backend_helper->getUrl("navigationmenupro/menucreator/edit/", ["id" => $item->getMenuId()]);
            $add_sub_url = $backend_helper->getUrl('navigationmenupro/menudata/addsubparent');
            $editformurl = $backend_helper->getUrl("navigationmenupro/menucreator/editform/", ["id" => $item->getMenuId()]);
            $add_sub = $backend_helper->getUrl("navigationmenupro/menucreator/new/", ["group_id" => $item->getGroupId(),"parent_id" => $item->getMenuId()]);
            $delete_url = $backend_helper->getUrl("navigationmenupro/menucreator/deleteitems/", ["id" => $item->getMenuId()]);
            $url_helper= $om->create('Magento\Framework\Url');
            $current_url = $url_helper->getCurrentUrl();
            $helper= $om->create('Magebees\Navigationmenupro\Helper\Data');
            $space = $helper->getMenuSpace($item->getMenuId());
            $hasChild = $this->getChildMenuCollection($item->getMenuId());
            $menu_type = trim($item->getType());
        
            if (($item->getType() == "1")) {
                $this->item_type_label = 'cms';
            } elseif (($item->getType() == "2")) {
                $this->item_type_label = 'category';
            } elseif (($item->getType() == "3")) {
                $this->item_type_label = 'static-block';
            } /* For Product Pages*/
            elseif (($item->getType() == "4")) {
                $this->item_type_label = 'product';
            } elseif (($item->getType() == "5")) {
                $this->item_type_label = 'custom-url';
            } elseif (($item->getType() == "6")) {
                $this->item_type_label = 'alias';
            } elseif (($item->getType() == "7")) {
                $this->item_type_label = 'group';
            } elseif (($item->getType() == "account")) {
                $this->item_type_label = 'account';
            } elseif (($item->getType() == "cart")) {
                $this->item_type_label = 'cart';
            } elseif (($item->getType() == "wishlist")) {
                $this->item_type_label = 'wishlist';
            } elseif (($item->getType() == "checkout")) {
                $this->item_type_label = 'checkout';
            } elseif (($item->getType() == "login")) {
                $this->item_type_label = 'login';
            } elseif (($item->getType() == "logout")) {
                $this->item_type_label = 'logout';
            } elseif (($item->getType() == "register")) {
                $this->item_type_label = 'register';
            } elseif (($item->getType() == "contact")) {
                $this->item_type_label = 'contact';
            }
            if ($item->getStatus()=="1") {
                $status = ' enabled';
            } else {
                $status = ' disabled';
            }
            if ($item->getClassSubfix() != '') {
                $add_custom_class = $item->getClassSubfix();
            } else {
                $add_custom_class = '';
            }
            if (count($hasChild)>0) {
                $has_child_element = 'mjs-nestedSortable-branch mjs-nestedSortable-expanded ';
            } else {
                $has_child_element = 'mjs-nestedSortable-leaf ';
            }
            $column_layout = trim($item->getSubcolumnlayout());
            if (($column_layout == 'no-sub') && ($menu_type == "3")) {
                $html .= '<li class="'.$has_child_element.$status.' '.$add_custom_class.' no-sub" id="menuItem_'.$item->getMenuId().'" store="'.$item->getStoreids().'"> <div class="menuDiv">';
            } elseif (($menu_type == "7")) {
                $html .= '<li class="'.$has_child_element.$status.' '.$add_custom_class.' no-sub" id="menuItem_'.$item->getMenuId().'" store="'.$item->getStoreids().'"> <div class="menuDiv">';
            } else {
                $html .= '<li class="'.$has_child_element.$status.' '.$add_custom_class.'" id="menuItem_'.$item->getMenuId().'" store="'.$item->getStoreids().'"> <div class="menuDiv">';
            }

            if (count($hasChild)>0) {
                $html .= '<span class="disclose ui-icon ui-icon-minusthick" title="Click to show/hide children"><span></span></span>';
            }
            $html .= '<span class="itemTitle" data-id="'.$item->getMenuId().'">'.$item->getTitle().'</span><span class="mType"">'.$this->item_type_label.'</span><button data-editurl="'.$editformurl.'" class="scalable edit edit-menu-item" type="button" title="Edit '.$item->getTitle().'"><span>Edit Menu</span></button>
		<button data-groupid="'.$item->getGroupId().'" data-menuId="'.$item->getMenuId().'" data-addsuburl="'.$add_sub_url.'" class="scalable add add-menu-item" type="button" title="Add in '.$item->getTitle().'"><span>Add Sub</span></button>
		<button data-deleteurl="'.$delete_url.'" data-currenturl="'.$current_url.'" class="scalable delete delete-menu-item" type="button" title="Delete '.$item->getTitle().'"><span>Delete</span></button>
		</div>';
            $has_child_element = '';
            $parent_status = '';
            $add_custom_class = '';
        /* Use TO Get The Sub Category If set Autoi Sub On when the menu type is category*/

            if (count($hasChild)>0) {
                $html .= $this->getTreeChild($item->getMenuId(), true, $column_layout, $menu_type);
            }
            $html .= '</li>';
        }

        return $html;
    }
    
    function getTreeChild($parentId, $isChild, $column_layout, $menu_type)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menucreator= $om->create('Magebees\Navigationmenupro\Model\Menucreator');
        $allChild = $menucreator->getCollection()
                    ->setOrder("position", "asc")
                    ->addFieldToFilter('parent_id', $parentId);

        $html = isset($html) ? $html : '';
        $Parent_menu = $menucreator->load($parentId);
        if ($Parent_menu->getStatus()=="1") {
            $parent_status = ' enabled';
        } else {
            $parent_status = ' disabled';
        }

        $class = ($isChild) ? "sub-cat-list" : "cat-list";

        if (($column_layout == 'no-sub')) {
            $html .= '<ol class="'.$class.$parent_status.' '.$column_layout.'">';
        } elseif (($menu_type == "3") && ($column_layout == 'no-sub')) {
            $html .= '<ol class="'.$class.$parent_status.' no-sub-static-block">';
        } else {
            $html .= '<ol class="'.$class.$parent_status.'">';
        }

        $parent_status = '';
        foreach ($allChild as $item) {
            if (($item->getType() == "1")) {
                $this->item_type_label = 'cms';
            } elseif (($item->getType() == "2")) {
                $this->item_type_label = 'category';
            } elseif (($item->getType() == "3")) {
                $this->item_type_label = 'static-block';
            } /* For Product Pages*/
            elseif (($item->getType() == "4")) {
                $this->item_type_label = 'product';
            } elseif (($item->getType() == "5")) {
                $this->item_type_label = 'custom-url';
            } elseif (($item->getType() == "6")) {
                $this->item_type_label = 'alias';
            } elseif (($item->getType() == "7")) {
                $this->item_type_label = 'group';
            } elseif (($item->getType() == "account")) {
                $this->item_type_label = 'account';
            } elseif (($item->getType() == "cart")) {
                $this->item_type_label = 'cart';
            } elseif (($item->getType() == "wishlist")) {
                $this->item_type_label = 'wishlist';
            } elseif (($item->getType() == "checkout")) {
                $this->item_type_label = 'checkout';
            } elseif (($item->getType() == "login")) {
                $this->item_type_label = 'login';
            } elseif (($item->getType() == "logout")) {
                $this->item_type_label = 'logout';
            } elseif (($item->getType() == "register")) {
                $this->item_type_label = 'register';
            } elseif (($item->getType() == "contact")) {
                $this->item_type_label = 'contact';
            }
            if ($item->getStatus()=="1") {
                $child_status = ' enabled';
            } else {
                $child_status = ' disabled';
            }

            $hasChild = $this->getChildMenuCollection($item->getMenuId());
            $backend_helper= $om->create('Magento\Backend\Helper\Data');
            $url = $backend_helper->getUrl("navigationmenupro/menucreator/edit/", ["id" => $item->getMenuId()]);
            $add_sub_url = $backend_helper->getUrl('navigationmenupro/menudata/addsubparent');
            $editformurl = $backend_helper->getUrl("navigationmenupro/menucreator/editform/", ["id" => $item->getMenuId()]);
            $add_sub = $backend_helper->getUrl("navigationmenupro/menucreator/new/", ["group_id" => $item->getGroupId(),"parent_id" => $item->getMenuId()]);
            $delete_url = $backend_helper->getUrl("navigationmenupro/menucreator/deleteitems/", ["id" => $item->getMenuId()]);
            $url_helper= $om->create('Magento\Framework\Url');
            $current_url = $url_helper->getCurrentUrl();

            if (count($hasChild)>0) {
                $has_child_element = 'mjs-nestedSortable-branch mjs-nestedSortable-expanded ';
            } else {
                $has_child_element = 'mjs-nestedSortable-leaf ';
            }
            $sub_menu_type = trim($item->getType());
            $column_layout = trim($item->getSubcolumnlayout());
            if ($item->getClassSubfix() != '') {
                $add_custom_class = $item->getClassSubfix();
            } else {
                $add_custom_class = '';
            }
            if (($column_layout == 'no-sub') && ($sub_menu_type == "3")) {
                $html .= '<li class="'.$has_child_element.$child_status.' '.$add_custom_class.' no-sub" id="menuItem_'.$item->getMenuId().'" store="'.$item->getStoreids().'"><div class="menuDiv">';
            } else {
                $html .= '<li class="'.$has_child_element.$child_status.' '.$add_custom_class.'" id="menuItem_'.$item->getMenuId().'" store="'.$item->getStoreids().'"><div class="menuDiv">';
            }
            if (count($hasChild)>0) {
                $html .= '<span class="disclose ui-icon ui-icon-minusthick" title="Click to show/hide children"><span></span></span>';
            }
        
            $html .= '<span class="itemTitle" data-id="'.$item->getMenuId().'">'.$item->getTitle().'</span><span class="mType"">'.$this->item_type_label.'</span>
		<button data-editurl="'.$editformurl.'" class="scalable edit edit-menu-item" type="button" title="Edit '.$item->getTitle().'"><span>Edit Menu</span></button>
		<button data-groupid="'.$item->getGroupId().'" data-menuId="'.$item->getMenuId().'" data-addsuburl="'.$add_sub_url.'" class="scalable add add-menu-item" type="button" title="Add in '.$item->getTitle().'"><span>Add Sub</span></button>
		<button data-deleteurl="'.$delete_url.'" data-currenturl="'.$current_url.'" class="scalable delete delete-menu-item" type="button" title="Delete '.$item->getTitle().'"><span>Delete</span></button>
		</div>';
            $child_status = '';


            if (count($hasChild)>0) {
                $column_layout = trim($item->getSubcolumnlayout());
                $menu_type = trim($item->getType());
                $html .= $this->getTreeChild($item->getMenuId(), true, $column_layout, $menu_type);
            }

            $html .= '</li>';
        }
        $html .= '</ol>';
        return $html;
    }
    public function getMenuGroupdetails()
    {
        $group_menu = [];
            $menugroup_grid = $this->getMenuGroup();
            
        foreach ($menugroup_grid as $group) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $menucreatorgroup= $om->create('Magebees\Navigationmenupro\Model\Menucreatorgroup');
            $group_details =$menucreatorgroup->load($group->getGroupId());
            $group_id = $group->getGroupId();
            $group_menu[$group_id] = $group_details->getTitle();
        }
            return $group_menu;
    }
    function getCategoriesforTree($parentId, $isChild)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $model_cat= $om->create('Magento\Catalog\Model\Category');
        $allCats = $model_cat->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('parent_id', ['eq' => $parentId])
                ->addAttributeToSort('position', 'asc');
               
        $class = ($isChild) ? "sub-cat-list" : "cat-list";
        $html .= '<ul class="auto-sub '.$class.'">';
        foreach ($allCats as $category) {
            if ($category->getLevel() > 2) {
                $lable = '';
                for ($i=2; $i<=$category->getLevel(); $i++) {
                    $lable .= "\t".' -';
                }
            }

        ?>
        <?php
        $subcats = $category->getChildren();
        if ($subcats != '') {
            $has_child_element = 'hassub';
        }
        $html .= '<li class="'.$has_child_element.'"><a href=#><span>'.$lable.$category->getName().'</span></a>';
        $lable = '';
        $has_child_element = '';
        if ($subcats != '') {
            $html .= $this->getCategoriesforTree($category->getId(), true);
        }
         $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    public function getChildMenuItem($parentmenuid)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menucreator= $om->create('Magebees\Navigationmenupro\Model\Menucreator');
        $chilMenu= $menucreator->getCollection()
        ->addFieldToSelect('menu_id')
        ->addFieldToFilter('parent_id', $parentmenuid);
        $childmenuitems = $chilMenu->getData();
        $this->child_menu_items[] = $parentmenuid;
        foreach ($childmenuitems as $key => $value) {
            $this->child_menu_items[] = $value['menu_id'];
            $hasChild = $this->getChildMenuCollection($value['menu_id']);
            if (count($hasChild) > 0) {
                $this->child_menu_items[] = $this->getChildMenuItemTest($value['menu_id']);
            }
        }
        return $this->child_menu_items;
    }
    public function getChildMenuItemTest($parentmenuid)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menucreator= $om->create('Magebees\Navigationmenupro\Model\Menucreator');
        $chilMenu= $menucreator->getCollection()
        ->addFieldToSelect('menu_id')
        ->addFieldToFilter('parent_id', $parentmenuid);

        $childmenuitems = $chilMenu->getData();
        foreach ($childmenuitems as $key => $value) {
            $this->child_menu_items[] = $value['menu_id'];
            $hasChild = $this->getChildMenuCollection($value['menu_id']);
            if (count($hasChild) > 0) {
                $this->child_menu_items[] = $this->getChildMenuItemTest($value['menu_id']);
            }
        }
        return $this->child_menu_items;
    }
}
