<?php
namespace Magebees\Navigationmenupro\Controller\Adminhtml\Menucreatorgroup;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory; 

class Save extends \Magento\Backend\App\Action
{
	
	public function __construct( \Magento\Backend\App\Action\Context $context,
	\Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
	\Magento\Framework\App\Config\ScopeConfigInterface $_scopeConfig,
			array $data = [])
	{
		$this->_collection = $collection;
		$this->_scopeConfig = $_scopeConfig;
		parent::__construct($context);
	}
	
	public function execute()
	    {		

		$currentDate = date('Y-m-d h:i:s');	
       	$data=$this->getRequest()->getPost()->toarray();
		
		
		if($data)
		{
			$model = $this->_objectManager->create('Magebees\Navigationmenupro\Model\Menucreatorgroup');
           	$id = $this->getRequest()->getParam('id');
			
            if ($id)
			{
                $model->load($id);
				 if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong item is specified.'));
                }
            }
			/* Set blank value for the dependent field*/
			if(!isset($data['root']['lvl0dvcolor'])){
			$data['root']['lvl0dvcolor']='';	
			}
			if(!isset($data['sub']['sublvl1dvcolor'])){
			$data['sub']['sublvl1dvcolor']='';	
			}
			if(!isset($data['mega']['mmlvl1dvcolor'])){
			$data['mega']['mmlvl1dvcolor']='';	
			}
			if(!isset($data['mega']['mmlvl2dvcolor'])){
			$data['mega']['mmlvl2dvcolor']='';	
			}
			if(!isset($data['mega']['mmlvl3dvcolor'])){
			$data['mega']['mmlvl3dvcolor']='';	
			}
			if(!isset($data['fly']['ddlinkdvcolor'])){
			$data['fly']['ddlinkdvcolor']='';	
			}
			$data['rootoptions'] = json_encode($data['root']);
			$data['megaoptions'] = json_encode($data['mega']);
			$data['suboptions'] = json_encode($data['sub']);
			$data['flyoptions'] = json_encode($data['fly']);
			
			$model->setData($data);
			try {
					if ($model->getCreatedTime() == NULL || $model->getUpdateTime() == NULL) 
					{
						$model->setCreatedTime($currentDate)
								->setUpdateTime($currentDate);
					} 
					else 
					{
						$model->setUpdateTime($currentDate);
					}	
					$model->setDescription("This is descriptions");
					if(isset($data['alignment']))
					{
					$model->setAlignment($data['alignment']);
					}
					$model->save();
					
					/*Start Working to  Create & Update the Dynamic Css file for the menu items*/
					if($this->getRequest()->getParam('id') == "")
					{
					$group_id = $model->getData('group_id');
					$menu_type = $model->getData('menutype');
					}
					else
					{
					$group_id = $this->getRequest()->getParam('id');
					}
					
					$menu_type = $model->getData('menutype');
					$om = \Magento\Framework\App\ObjectManager::getInstance();
					$reader = $om->get('Magento\Framework\Module\Dir\Reader');
					$moduleviewDir=$reader->getModuleDir('view', 'Magebees_Navigationmenupro');
					$cssDir=$moduleviewDir.'/frontend/web/css/navigationmenupro';
			
					if (!file_exists($cssDir)) {
						mkdir($cssDir, 0777, true);
					}
					
					$path_less = $cssDir.'/';
					if($model->getMenutype()){
						$css_less = $this->get_less_variable($group_id);
						$menu_type = $model->getMenutype();
						//$oldfile = "-".$group_id.".less";
						$files1 = scandir($path_less);
						$list_lessfiles =  $cssDir.'/*.less';
						$file_name_check = "-".$group_id.".less";
						foreach (glob($list_lessfiles) as $filename) {
							if($this->endsWith($filename, $file_name_check)){
								if(is_file($filename)) {
								$result = unlink($filename); // delete Old Less file
								}
							}
						}
						$path_less .= $menu_type."-".$group_id.".less";
						if($menu_type=="mega-menu")
						{
								$master_less_file = $cssDir.'/'.'master-mega-menu.php';
								$master_less = file_get_contents($master_less_file);
								$content = $css_less.$master_less;
								file_put_contents($path_less,$content);
					
						}elseif(($menu_type=="smart-expand")||($menu_type=="always-expand")){
								$master_less_file = $cssDir.'/'.'master-expand-menu.php';
								$master_less = file_get_contents($master_less_file);
								$content = $css_less.$master_less;
								file_put_contents($path_less,$content);
						}
					}
					/*End Working to  Create & Update the Dynamic Css file for the menu items*/
				
					$this->messageManager->addSuccess(__('The Record has been saved.'));
					$this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
					if ($this->getRequest()->getParam('back')) {
						$this->_redirect('*/*/edit', array('id' => $model->getId(), '_current' => true));
						return;
					}
					$this->_redirect('*/*/');
					return;
            	}
				catch (\Magento\Framework\Model\Exception $e) 
				{
                	$this->messageManager->addError($e->getMessage());
            	}
				catch (\RuntimeException $e) 
				{
                	$this->messageManager->addError($e->getMessage());
            	} 
				catch (\Exception $e)
				{
					$this->messageManager->addError($e->getMessage());
                	//$this->messageManager->addException($e, __('Something went wrong while saving the Record.'));
            	}
				$this->_getSession()->setFormData($data);
				$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
				$resultRedirect->setUrl($this->_redirect->getRefererUrl());
				return $resultRedirect;
				//$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				//return;
		}
		$this->_redirect('*/*/');
    }
    public function get_menu_css($group_id)
	{
		$groupdata = $this->_objectManager->create('Magebees\Navigationmenupro\Model\Menucreatorgroup')->load($group_id);
		$alignment = $groupdata->getPosition();
		$menutype = $groupdata->getMenutype();
		$grouptitletextcolor = $groupdata->getTitletextcolor();
		$grouptitlebgcolor = $groupdata->getTitlebackcolor();
		$itemimageheight = $groupdata->getImageHeight();
		$itemimagewidth = $groupdata->getImageWidth();

		$textcolor = $groupdata->getItemtextcolor();
		$texthovercolor = $groupdata->getItemtexthovercolor();
		$textactivecolor = $groupdata->getrootactivecolor();
		$itembgcolor = $groupdata->getItembgcolor();
		$itembghovercolor = $groupdata->getItembghovercolor();
		$arrowcolor = $groupdata->getArrowcolor();
		$dividercolor = $groupdata->getDividercolor();
		$menu_bg_color = $groupdata->getMenubgcolor();
		$drop_bg_color = $groupdata->getSubitemsbgcolor();
		$drop_border_color = $groupdata->getSubitemsbordercolor();
		$megaparenttextcolor = $groupdata->getMegaparenttextcolor();
		$megaparenttexthovercolor = $groupdata->getMegaparenttexthovercolor();
		$megaparenttextactivecolor = $groupdata->getMegaparenttextactivecolor();
		$megaparenttextbgcolor = $groupdata->getMegaparenttextbgcolor();
		$megaparenttextbghovercolor = $groupdata->getMegaparenttextbghovercolor();
		$subitemtextcolor = $groupdata->getSubitemtextcolor();
		$subitemtexthovercolor = $groupdata->getSubitemtexthovercolor();
		$itemactivecolor = $groupdata->getItemactivecolor();
		$subitembgcolor = $groupdata->getSubitembgcolor();
		$subitembghovercolor = $groupdata->getSubitembghovercolor();
		$subarrowcolor = $groupdata->getSubarrowcolor();
		$subdividercolor = $groupdata->getSubitemdividercolor();


		$css = '';
		// Common class
		$css .= '#cwsMenu-'.$group_id.' { background-color:#'.$menu_bg_color.'; }';
		$css .= '#cwsMenu-'.$group_id.' .menuTitle { color:#'.$grouptitletextcolor.'; background-color:#'.$grouptitlebgcolor.'; }';
		$css .= '#cwsMenu-'.$group_id.' ul.cwsMenu span.img { max-height:'.$itemimageheight.'px; max-width:'.$itemimagewidth.'px; }';
		$css .='#cwsMenu-'.$group_id.' .cwsMenu.horizontal > li.parent > a:after { border-top-color:#'.$arrowcolor.'; }'; // Horizontal
		$css .='#cwsMenu-'.$group_id.' .cwsMenu.vertical > li.parent > a:after { border-left-color:#'.$arrowcolor.'; }'; // Verticle

		// First lavel
		$css .='#cwsMenu-'.$group_id.' ul.cwsMenu li.Level0 > a { color:#'.$textcolor.'; background-color:#'.$itembgcolor.'; }';
		$css .='#cwsMenu-'.$group_id.' ul.cwsMenu.mega-menu li.Level0:hover > a { color:#'.$texthovercolor.'; background-color:#'.$itembghovercolor.'; }';
		$css .='#cwsMenu-'.$group_id.' ul.cwsMenu.smart-expand li.Level0 > a:hover,';
		$css .='#cwsMenu-'.$group_id.' ul.cwsMenu.always-expand li.Level0 > a:hover { color:#'.$texthovercolor.'; background-color:#'.$itembghovercolor.'; }';
		$css .='#cwsMenu-'.$group_id.' ul.cwsMenu li.Level0.active > a { color:#'.$textactivecolor.'; }';

		$css .='#cwsMenu-'.$group_id.' .cwsMenu.horizontal > li.parent > a:after { border-top-color:#'.$arrowcolor.'; }';
		$css .='#cwsMenu-'.$group_id.' .cwsMenu.horizontal > li { border-right-color:#'.$dividercolor.'; }';
		$css .='#cwsMenu-'.$group_id.' .cwsMenu.vertical > li { border-top-color:#'.$dividercolor.'; }';

		

		// Second lavel
		$css .='#cwsMenu-'.$group_id.' ul.cwsMenu li > ul.subMenu { background-color:#'.$drop_bg_color.'; border-color:#'.$drop_border_color.'; }';
		$css .='#cwsMenu-'.$group_id.' ul.cwsMenu li li > a { color:#'.$subitemtextcolor.'; background-color:#'.$subitembgcolor.'; }';
		$css .='#cwsMenu-'.$group_id.' ul.cwsMenu.mega-menu li li:hover > a { color:#'.$subitemtexthovercolor.'; background-color:#'.$subitembghovercolor.'; }';
		$css .='#cwsMenu-'.$group_id.' ul.cwsMenu li li.active > a { color:#'.$itemactivecolor.'; }';

		$css .='#cwsMenu-'.$group_id.' ul.cwsMenu.mega-menu li.column-1 li.parent > a:after { border-left-color:#'.$subarrowcolor.'; }';
		$css .='#cwsMenu-'.$group_id.'.rtl ul.cwsMenu.mega-menu li.column-1 li.parent > a:after { border-right-color:#'.$subarrowcolor.'; }';
		$css .='#cwsMenu-'.$group_id.'.rtl .cwsMenu.vertical > li.parent.aRight > a:after { border-right-color:#'.$subarrowcolor.'; }';
		$css .='#cwsMenu-'.$group_id.'.rtl .cwsMenu.vertical li.column-1.aRight li.parent > a:after { border-right-color:#'.$subarrowcolor.'; }';
		$css .='#cwsMenu-'.$group_id.' ul.cwsMenu li ul > li { border-bottom-color:#'.$subdividercolor.'; }';
		$css .='#cwsMenu-'.$group_id.' .cwsMenu.vertical > li li { border-top-color:#'.$subdividercolor.'; }';

		// Megamenu column title Color
		$css .='#cwsMenu-'.$group_id.' ul.cwsMenu li.megamenu ul li.Level1 > a { color:#'.$megaparenttextcolor.'; background-color:#'.$megaparenttextbgcolor.'; }';
		$css .='#cwsMenu-'.$group_id.' ul.cwsMenu li.megamenu ul li.Level1:hover > a { color:#'.$megaparenttexthovercolor.'; background-color:#'.$megaparenttextbghovercolor.'; }';
		$css .='#cwsMenu-'.$group_id.' ul.cwsMenu li.megamenu li.Level1 ul.Level1 > li { border-bottom-color:#'.$subdividercolor.'; }';

		// Megamenu column When hide title of column
		$css .='#cwsMenu-'.$group_id.' ul.cwsMenu li.megamenu ul li.hideTitle li.Level2 > a { color:#'.$megaparenttextcolor.'; background-color:#'.$megaparenttextbgcolor.'; }';
		$css .='#cwsMenu-'.$group_id.' ul.cwsMenu li.megamenu ul li.hideTitle li.Level2:hover > a { color:#'.$megaparenttexthovercolor.'; background-color:#'.$megaparenttextbghovercolor.'; }';

		// Smart/Always Expand Color
		$css .='#cwsMenu-'.$group_id.' .cwsMenu.smart-expand li > span.arw { color:#'.$arrowcolor.'; }';
		$css .='#cwsMenu-'.$group_id.' .cwsMenu.smart-expand > li,';
		$css .='#cwsMenu-'.$group_id.' .cwsMenu.always-expand > li { border-top-color:#'.$dividercolor.'; }';

		$css .='#cwsMenu-'.$group_id.' .cwsMenu.smart-expand li li > span.arw { color:#'.$subarrowcolor.'; }';
		$css .='#cwsMenu-'.$group_id.' .cwsMenu.smart-expand > li li,';
		$css .='#cwsMenu-'.$group_id.' .cwsMenu.always-expand > li li { border-top-color:#'.$subdividercolor.'; }';

		$css .='#cwsMenu-'.$group_id.' ul.cwsMenu.always-expand li li a:hover { color:#'.$subitemtexthovercolor.'; background-color:#'.$subitembghovercolor.'; }';

		return $css;
	}
	public function get_less_variable($group_id)
	{
		$groupdata = $this->_objectManager->create('Magebees\Navigationmenupro\Model\Menucreatorgroup')->load($group_id);
		
		
		$scope_info = $this->_scopeConfig->getValue('navigationmenupro/general',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$responsive_breakpoint = '';
		$dynamic_variable = '';
		$dynamic_variable = '@group_id:'.$group_id.';'.PHP_EOL;
		$itemimageheight = $groupdata->getImageHeight().'px';
		$dynamic_variable .= '@itemimageheight:'.$itemimageheight.';'.PHP_EOL;
		$itemimagewidth = $groupdata->getImageWidth().'px';
		$dynamic_variable .= '@itemimagewidth:'.$itemimagewidth.';'.PHP_EOL;
		
		if(isset($scope_info['responsive_break_point']))
		{
		$dynamic_variable .= '@responsive_breakpoint:'.$scope_info['responsive_break_point'].';'.PHP_EOL;	
		}else{
			$dynamic_variable .= '@responsive_breakpoint:767px;'.PHP_EOL;
		}
		
		
		$informations = $groupdata->getData();
			foreach($informations as $key => $value):
					if($this->isJSON($value)){
					$sub_information = json_decode($value, true);
					foreach($sub_information as $subkey => $subvalue):
						if($subvalue==""){
						$dynamic_variable .= '@'.$subkey.':;'.PHP_EOL;	
						}else{
							$dynamic_variable .= '@'.$subkey.':'.$subvalue.';'.PHP_EOL;
						}
					endforeach;
			}
			endforeach;
		return $dynamic_variable;
		
		
		
	}

	protected function _isAllowed()
    {
		return true;
        
    }
	public function isJSON($string){
		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}
	function endsWith($haystack, $needle)
	{
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}

		return (substr($haystack, -$length) === $needle);
	}
}
