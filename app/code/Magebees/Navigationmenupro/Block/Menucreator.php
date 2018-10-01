<?php
    namespace Magebees\Navigationmenupro\Block;

class Menucreator extends \Magento\Framework\View\Element\Template
{
    protected $group_option = '';
    protected $_config;
    protected $_optimizeconfig;
    
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        array $data = []
    ) {
     
        parent::__construct($context, $data);
    }
    public function getConfig()
    {
           $this->_config = $this->_scopeConfig->getValue('navigationmenupro/general', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
           return $this->_config;
    }
    public function getOptimizeConfig()
    {
            $this->_optimizeconfig = $this->_scopeConfig->getValue('navigationmenupro/optimize_performance', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return $this->_optimizeconfig;
    }
        
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getMenutype($group_id)
    {

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $om->create('Magebees\Navigationmenupro\Model\Menucreatorgroup');
        $group_details = $model->load($group_id);
        $group_menutype = trim($group_details->getMenutype());
        return $group_menutype;
    }
    public function getMenuStatus($group_id)
    {

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $om->create('Magebees\Navigationmenupro\Model\Menucreatorgroup');
        $group_details = $model->load($group_id);
        $group_status = trim($group_details->getStatus());
        return $group_status;
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
                    $menufront = "<div id='cwsMenu-".$group_id."' class='".$direction_css."'>";
                } else {
                    $menufront = "<div id='cwsMenu-".$group_id."'>";
                }
            } elseif ($group_menutype == 'mega-menu') {
                if ($direction_css!='') {
                    $menufront = "<div id='cwsMenu-".$group_id."' class='cwsMenuOuter ".$group_position." ".$direction_css."' >";
                } else {
                    $menufront = "<div id='cwsMenu-".$group_id."' class='cwsMenuOuter'>";
                }
            } else {
                if ($direction_css!='') {
                    $menufront = "<div id='cwsMenu-".$group_id."' class='cwsMenuOuter ".$direction_css."' >";
                } else {
                    $menufront = "<div id='cwsMenu-".$group_id."' class='cwsMenuOuter'>";
                }
            }
        
            if ($group_details->getShowhidetitle() == "2") {
                $menufront .= '<h3 class="menuTitle">'.$group_details->getTitle().'</h3>';
            }
            if ($group_menutype != 'list-item') {
                $menusetting = '{"menu":{"responsive":true, "expanded":true, "position":{"my":"left top","at":"left bottom"}}}';
                $menusetting = '{"menu":{"responsive":true, "expanded":true, "position":{"my":"left top","at":"left bottom"}}}';
                
                $menufront .="<ul class='cwsMenu ".$this->group_option."'>";
            } else {
                $menufront .="<ul>";
            }
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $menu = $om->get('Magebees\Navigationmenupro\Model\Menuitem');
            $menufront .= $menu->getMenuContent($group_id);
            $menufront .= "</ul>";
            $menufront .= "</div>";
            return $menufront;
        } else {
            return;
        }
    }
    
    public function get_menu_css($group_id)
    {
    
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $page = $om->create('Magento\Framework\View\Page\Config');
        $page->addPageAsset('Magebees_Navigationmenupro::js/path/here.js');
        $model = $om->create('Magebees\Navigationmenupro\Model\Menucreatorgroup');
        
        $groupdata =$model->load($group_id);
        $alignment = $groupdata->getPosition();
        $menutype = $groupdata->getMenutype();
        $grouptitletextcolor = $groupdata->getTitletextcolor();
        $grouptitlebgcolor = $groupdata->getTitlebackcolor();
        $textcolor = $groupdata->getItemtextcolor();
        $texthovercolor = $groupdata->getItemtexthovercolor();
        $itembgcolor = $groupdata->getItembgcolor();
        $itembghovercolor = $groupdata->getItembghovercolor();
        $css = '';
        $css .='#menu-'.$group_id.' ul.cwsMenu.'.$alignment.' li { background-color:#'.$itembgcolor.'}';
        $css .='#menu-'.$group_id.' ul.cwsMenu.'.$alignment.' li:hover { background-color:#'.$itembghovercolor.'}';
        $css .='#menu-'.$group_id.' ul.cwsMenu.'.$alignment.' li a { color:#'.$textcolor.'}';
        $css .='#menu-'.$group_id.' ul.cwsMenu.'.$alignment.' li a:hover { color:#'.$texthovercolor.'}';
        return $css;
    }
    public function isJSON($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }
}
