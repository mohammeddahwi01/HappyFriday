<?php
namespace  Magebees\Navigationmenupro\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function getallMenuTypes()
    {
        return [
            '1' => 'CMS Page',
            '2' => 'Category Page',
            '3' => 'Static Block',
            '4' => 'Product Page',
            '5' => 'Custom Url',
            '6' => 'Alias [href=#]',
            '7'=>   'Group'
        ];
    }
    public function getallLinks()
    {
        return [
            'account'   => 'My Account',
            'cart'      => 'My Cart',
            'wishlist'  => 'My Wishlist',
            'checkout'  => 'Checkout',
            'login'     => 'Login',
            'logout'    => 'Logout',
            'register'  => 'Register',
            'contact'   => 'Contact Us'
        ];
    }
    public function getShowHideTitle()
    {
        return [
            '' => 'Please Select Show Hide Menu Title',
            '1' => 'Hide Group Title',
            '2' => 'Show Group Title',
        ];
    }
    public function getOptionArray()
    {
        return [
            
            '1' => 'Enabled',
            '2' => 'Disabled',
        ];
    }
    public function getGroupMenuType()
    {
        return [
            '' => 'Please Select Menu Type',
            'mega-menu' => 'Mega Menu',
            'smart-expand' => 'Smart Expand',
            'always-expand' => 'Always Expand',
            'list-item' => 'List Item'
        ];
    }
    
    public function getAlignmentType()
    {
        return [
            '' => 'Please Select Alignment',
            'horizontal' => 'Horizontal',
            'vertical' => 'Vertical'
            
        ];
    }
    public function getFontTransform()
    {
        return [
            'inherit' => 'Inherit',
            'uppercase' => 'Uppercase',
            'lowercase' => 'Lowercase',
            'capitalize' => 'Capitalize'
            
        ];
    }
    public function getAlignment()
    {
        return [
            'left' => 'Left',
            'right' => 'Right',
            'full-width' => 'Full Width',
        ];
    }
    public function getMenuLevel()
    {
        return [
            '' => 'Please Select Level',
            '0' => 'Only Root Level',
            '1' => 'One Level',
            '2' => 'Second Level',
            '3' => 'Third Level',
            '4' => 'Fourth Level',
            '5' => 'Fifth Level',
        ];
    }
    public function getmassMenuLevel()
    {
        return [
            '0' => 'Only Root Level',
            '1' => 'One Level',
            '2' => 'Second Level',
            '3' => 'Third Level',
            '4' => 'Fourth Level',
            '5' => 'Fifth Level',
        ];
    }
    public function getDirection()
    {
        return [
        'ltr'   => 'Left To Right',
        'rtl'   => 'Right To Left',
        ];
    }
    public function getlevel($menu_id, $level)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menu = $om->create('Magebees\Navigationmenupro\Model\Menucreator');
        $p_id=$menu->load($menu_id)->getParentId();
        $parent_menu = $menu->load($p_id);
        if ($p_id!=0) {
            $this->menu_level = $level+1;
            $this->getlevel($p_id, $this->menu_level);
        } else {
            $level = '0';
            return $level;
        }
        
        return $this->menu_level;
    }
    public function getRelation()
    {
        $this->relations[]=['value'=>'alternate','label'=>'alternate'];
        $this->relations[]=['value'=>'author','label'=>'author'];
        $this->relations[]=['value'=>'bookmark','label'=>'bookmark'];
        $this->relations[]=['value'=>'help','label'=>'help'];
        $this->relations[]=['value'=>'license','label'=>'license'];
        $this->relations[]=['value'=>'next','label'=>'next'];
        $this->relations[]=['value'=>'nofollow','label'=>'nofollow'];
        $this->relations[]=['value'=>'noreferrer','label'=>'noreferrer'];
        $this->relations[]=['value'=>'prefetch','label'=>'prefetch'];
        $this->relations[]=['value'=>'prev','label'=>'prev'];
        $this->relations[]=['value'=>'search','label'=>'search'];
        $this->relations[]=['value'=>'tag','label'=>'tag'];
        return $this->relations;
    }
    
    public function get_menu_items($group_id)
    {
        $this->group_option = '';
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $om->create('Magebees\Navigationmenupro\Model\Menucreatorgroup');
        $group_details = $model->load($group_id);
        if ($group_details->getStatus() == "1") {
            $group_position = $group_details->getPosition();
            $group_menutype = $group_details->getMenutype();
            if (($group_menutype == 'mega-menu')) {
                $this->group_option = $group_menutype." ".$group_position;
            } else {
                $this->group_option = $group_menutype;
            }
            $group_level = $group_details->getLevel();
            $direction = $group_details->getDirection();
        
            if ($direction=='rtl') {
                $direction_css = 'rtl';
            } elseif ($direction=='ltr') {
                $direction_css = 'ltr';
            }
        
            if ($group_menutype == 'list-item') {
                if ($direction_css!='') {
                    $menufront = "<nav id='cwsMenu-".$group_id."' class='".$direction_css."'>";
                } else {
                    $menufront = "<nav id='cwsMenu-".$group_id."'>";
                }
            } elseif ($group_menutype == 'mega-menu') {
                if ($direction_css!='') {
                    $menufront = "<nav id='cwsMenu-".$group_id."' class='navigation cwsMenuOuter ".$group_position." ".$direction_css."' role='navigation'>";
                } else {
                    $menufront = "<nav id='cwsMenu-".$group_id."' class='cwsMenuOuter'>";
                }
            } else {
                if ($direction_css!='') {
                    $menufront = "<nav id='cwsMenu-".$group_id."' class='navigation cwsMenuOuter ".$direction_css."' role='navigation'>";
                } else {
                    $menufront = "<nav id='cwsMenu-".$group_id."' class='cwsMenuOuter'>";
                }
            }
        
            if ($group_details->getShowhidetitle() == "2") {
                $menufront .= '<h3 class="menuTitle">'.$group_details->getTitle().'</h3>';
            }
            if ($group_menutype != 'list-item') {
                $menufront .="<ul class='cwsMenu ".$this->group_option."'>";
            } else {
                $menufront .="<ul>";
            }
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $menu = $om->get('Magebees\Navigationmenupro\Model\Menuitem');
            $menufront .= $menu->getMenuContent($group_id);
            $menufront .= "</ul>";
            $menufront .= "</nav>";
            return $menufront;
        } else {
            return;
        }
    }
    
    public function getTemplatePath()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $storemanager= $om->create('Magento\Store\Model\StoreManagerInterface');
        $storeId = $storemanager->getStore()->getStoreId();
        $storeCode =$storemanager->getStore()->getCode();
        $reader = $om->create('Magento\Framework\Module\Dir\Reader');
        $moduleviewDir=$reader->getModuleDir('view', 'Magebees_Navigationmenupro');
        $template_dir=$moduleviewDir.'/frontend/templates/static/';
        return $template_dir;
    }
    public function getDirectoryPath()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $storemanager= $om->create('Magento\Store\Model\StoreManagerInterface');
        $storeId = $storemanager->getStore()->getStoreId();
        $storeCode =$storemanager->getStore()->getCode();
        $reader = $om->create('Magento\Framework\Module\Dir\Reader');
        $moduleviewDir=$reader->getModuleDir('view', 'Magebees_Navigationmenupro');
        $dir=$moduleviewDir.'/frontend/templates/static/';
        return $dir;
    }
    public function getStaticMenu($groupId)
    {
            
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $storemanager= $om->create('Magento\Store\Model\StoreManagerInterface');
        $storeId = $storemanager->getStore()->getStoreId();
        $storeCode =$storemanager->getStore()->getCode();
        $website_id = $storemanager->getWebsite()->getId();
        $menu_customer= $om->create('Magebees\Navigationmenupro\Model\Customer');
        $permission = $menu_customer->getUserPermission();
            
        $session = $om->create('Magento\Customer\Model\Session');
        if ($session->isLoggedIn()) {
            $customerGroupId = $session->getCustomerGroupId();
        } else {
            $customerGroupId = $session->getCustomerGroupId();
        }
        $template_dir = $this->getTemplatePath();
        $dir = $this->getDirectoryPath();
        if (!is_dir($template_dir)) {
            mkdir($template_dir);
        }
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        
        $myFile = $dir."navigationmenu-w-".$website_id."s-".$storeCode."-g-".$groupId."customer-".
        $customerGroupId.".phtml";
        
        if (!file_exists($myFile)) {
            $fh = fopen($myFile, 'w'); // or die("error");
            $menu_html = $this->get_menu_items($groupId);
            $menu_compressedhtml = $this->sanitize_output($menu_html);
            //$menu_html = trim(preg_replace('/\s\s+/', ' ', $menu_html));
            //fwrite($fh, $menu_html);
            fwrite($fh, $menu_compressedhtml);
            fclose($fh);
        } else {
            $menu_file = fopen($myFile, 'r'); // or die("error");
            $menu_html = fgets($menu_file);
            
            fclose($menu_file);
        }
        return $menu_html;
    }
    public function getParentIds($menu_id)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menu = $om->create('Magebees\Navigationmenupro\Model\Menucreator');
        $p_id=$menu->load($menu_id)->getParentId();
        $p_ids=$p_id;
        //Stop this function when it parent is root node
        if ($p_id!=0) {
            $p_ids=$p_ids."-".$this->getParentIds($p_id);
        }
        return $p_ids;
    }
    public function getMenuSpace($menu_id)
    {
        $space="";
        $parentIds=explode("-", $this->getParentIds($menu_id));
        for ($i=1; $i<count($parentIds); $i++) {
            $space = $space."--";
        }
        return $space;
    }
    public function getPermissionforgrid()
    {
        $permission = [];
        $permission["-2"] = 'Public';
        $permission["-1"] = 'Registered';
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $model_cust= $om->create('Magento\Customer\Model\Group');
        $collection = $model_cust->getCollection();
        foreach ($collection as $value) {
            $permission[$value->getCustomerGroupId()] = $value->getCustomerGroupCode();
        }
        return $permission;
    }
    public function getPermission()
    {
        
        $this->groups[]=['value'=>'-2','label'=>'Public'];
        $this->groups[]=['value'=>'-1','label'=>'Registered'];
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $model_cust= $om->create('Magento\Customer\Model\Group');
        $collection = $model_cust->getCollection();
        foreach ($collection as $value) {
            $this->groups[] = [
                    'value'=>$value->getCustomerGroupId(),
                    'label' => $value->getCustomerGroupCode()
            ];
        }
        return $this->groups;
    }
    public function getstore_swatcher()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $model_store= $om->create('Magento\Store\Model\System\Store');
        $store_info     =$model_store->getStoreValuesForForm(false, true);
        return $store_info;
    }
    public function columnLayout()
    {
        return [
            '' => 'Please Select Sub Column Layout',
            'no-sub' => 'No Sub Item',
            'column-1' => '1 Column Layout',
            'column-2' => '2 Column Layout',
            'column-3' => '3 Column Layout',
            'column-4' => '4 Column Layout',
            'column-5' => '5 Column Layout',
            'column-6' => '6 Column Layout',
            'column-7' => '7 Column Layout',
            'column-8' => '8 Column Layout',
        ];
    }
    function sanitize_output($buffer)
    {

        $search = [
        '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
        '/[^\S ]+\</s',  // strip whitespaces before tags, except space
        '/(\s)+/s'       // shorten multiple whitespace sequences
        ];

        $replace = [
        '>',
        '<',
        '\\1'
        ];

        $buffer = preg_replace($search, $replace, $buffer);

        return $buffer;
    }
}
