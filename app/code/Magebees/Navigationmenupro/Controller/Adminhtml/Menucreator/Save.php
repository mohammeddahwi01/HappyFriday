<?php
namespace Magebees\Navigationmenupro\Controller\Adminhtml\Menucreator;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Backend\App\Action
{
	protected $_collection;
	
	public function __construct( 
	\Magento\Backend\App\Action\Context $context,
	\Magento\Catalog\Model\ResourceModel\Product\Collection $collection,

			array $data = [])
	{
		$this->_collection = $collection;
		parent::__construct($context);
	}
	
	
	public function execute()
    {	
		
		$currentDate = date('Y-m-d h:i:s');	
       	$data=$this->getRequest()->getPost()->toarray();
		
		if($data)
		{
         	$model = $this->_objectManager->create('Magebees\Navigationmenupro\Model\Menucreator');
			if(isset($data['menu_id']))
			{
			if ($data['menu_id'] != "") {
            	 $model->load($data['menu_id']);
				}
			}
			$files = $this->getRequest()->getFiles()->toArray();
           if(isset($files['image']['name']) && $files['image']['name'] != '') {
               try 
        		{
        			$image=$files['image']['name'];
        			$temp = explode(".", $image);
        			$file = current($temp);
        			$extension = end($temp);
        			$profile_new=$file. date('mdYHis').".".$extension;
        		    $files['image']['name']=$profile_new;
        			$uploader = $this->_objectManager->create('Magento\MediaStorage\Model\File\Uploader', array('fileId' =>'image'));
				    $uploader->setAllowCreateFolders(true);
        			$uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
        			$uploader->setAllowRenameFiles(false);
        			$uploader->setFilesDispersion(true);
        			$mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
        			->getDirectoryRead(DirectoryList::MEDIA);
        			$result = $uploader->save($mediaDirectory->getAbsolutePath('navigationmenupro/image'));
        			unset($result['tmp_name']);
        			$data['image'] = $result['file'];
        		}
        		
        		catch (\Exception $e)
				{
                	$this->messageManager->addException($e, __('Please Select Valid File.'));
				}
			}else{
                unset($data['image']);
            }
			
            $model->setTitle($data['title']);
            $model->setGroupId($data['group_id']);
            $model->setDescription($data['description']);
            //$model->setImage($data['image']);
            $model->setType($data['type']);
			if(isset($data['category_id']))
			{
            $model->setCategoryId($data['category_id']);
			}
			if(isset($data['cmspage_identifier']))
			{
            $model->setCmspageIdentifier($data['cmspage_identifier']);
			}
			if(isset($data['staticblock_identifier']))
			{
            $model->setStaticblockIdentifier($data['staticblock_identifier']);
			}
			if(isset($data['product_id']))
			{
            $model->setProductId($data['product_id']);
			}
            $model->setParentId($data['parent_id']);
			if(isset($data['url_value']))
			{
            $model->setUrlValue($data['url_value']);
			}
			if(isset($data['usedlink_identifier']))
			{
            $model->setUsedlinkIdentifier($data['usedlink_identifier']);
			}
			if(isset($data['image_status']))
			{
            $model->setImageStatus($data['image_status']);
			}
			if(isset($data['show_category_image']))
			{
            $model->setShowCategoryImage($data['show_category_image']);
			}
			if(isset($data['show_custom_category_image']))
			{
            $model->setShowCustomCategoryImage($data['show_custom_category_image']);
			}
			else
			{
				$model->setShowCustomCategoryImage("0");
			}
			if(isset($data['position']))
			{
            $model->setPosition($data['position']);
			}
			if(isset($data['class_subfix']))
			{
            $model->setClassSubfix($data['class_subfix']);
			}
			if(isset($data['permission']))
			{
            $model->setPermission($data['permission']);
			}
			if(isset($data['status']))
			{
            $model->setStatus($data['status']);
			}
           if(isset($data['target']))
			{
            $model->setTarget($data['target']);
			}
            $model->setTextAlign($data['text_align']);
            $model->setSubcolumnlayout($data['subcolumnlayout']);
           
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
				
				
				 if(isset($files['image']['name']) && ($files['image']['name'] != '') && isset($result['file'])){	
				$model->setImage($result['file']);
				}
				
				
				
			$storeids="";
			if(isset($data['storeids']))
			{
			if(in_array("0", $data['storeids']))
			{
			$defaultstore_id="0".",";
							$storeids = '';
							$storemanager= $this->_objectManager->create('Magento\Store\Model\StoreManagerInterface');
							$allStores =$storemanager->getStores();
							/* Get All Store Id in the storeids*/
							foreach ($allStores as $_eachStoreId => $val) 
							{
								$_storeId = $storemanager->getStore($_eachStoreId)->getId();
								$storeids.=$_storeId.",";
							}
							/* Add O as store Id for all the store
							*/
							$storeids = $defaultstore_id.$storeids;
							$model->setStoreids($storeids);
							$storeids="";
			}else
			{
			$storeids="";
			foreach($data['storeids'] as $store):
			$storeids.=$store.",";
			endforeach;
			$model->setStoreids($storeids);
							$storeids="";
			}
			
			}
			if(isset($data['remove_img_main']))
			{
			if($data['remove_img_main']=="1")
			{
				
			if(isset($data['menu_id']))
			{
			if ($data['menu_id'] != "") {
            	$id=$data['menu_id'];
				}
			}
			$model_image_remove= $this->_objectManager->create('Magebees\Navigationmenupro\Model\Menucreator')->load($id);
			
			$image_name = $model_image_remove->getImage();
					$mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
        			->getDirectoryRead(DirectoryList::MEDIA);
			$path=$mediaDirectory->getAbsolutePath('navigationmenupro/image').$image_name;
			unlink($path);
			$model_image_remove->setImage("");
			$model_image_remove->setShowCustomCategoryImage("");
			$model_image_remove->save();
			}
			}
				
			if(isset($data['setrel']))
			{
			$setrel="";
			foreach($data['setrel'] as $relation):
			$setrel.=$relation." ";
			endforeach;
			$model->setSetrel($setrel);
			$setrel="";
			}else
			{
			$setrel="";
			$model->setSetrel($setrel);
			}
				
				if(isset($data['title_show_hide'])){
					$model->setTitleShowHide($data['title_show_hide']);
			}
			if(isset($data['autosub'])){
					$model->setAutosub($data['autosub']);
				}else{
					$model->setAutosub(0);
				}
				
				if(isset($data['use_category_title'])){
					$model->setUseCategoryTitle($data['use_category_title']);
				}else{
					$model->setUseCategoryTitle(0);
				}
				if(isset($data['autosubimage'])){
					$model->setAutosubimage($data['autosubimage']);
				}else{
					$model->setAutosubimage(0);
				}
				if(isset($data['image_type']))
				{
				$model->setShowCategoryImage($data['image_type']);
				/*show_category_image*/
				}
				if(isset($data['useexternalurl'])){
					$model->setUseexternalurl($data['useexternalurl']);
				}else{
					$model->setUseexternalurl(0);
				}
				/*if(isset($data['label_show_hide'])){
					$model->setLabelShowHide($data['label_show_hide']);
				}else{
					$model->setLabelShowHide($data['label_show_hide']);
				}*/
				
				$model->setLabelTitle($data['label_title']);
				$model->setLabelHeight($data['height']);
				$model->setLabelColor($data['label_text_color']);
				$model->setLabelBgColor($data['label_text_bg_color']);
				$model->save();
				
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
                	$this->messageManager->addException($e, __('Something went wrong while saving the Record.'));
                	$this->messageManager->addError($e->getMessage());
            	}

				$this->_getSession()->setFormData($data);
				//$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				//return;
		}
		$this->_redirect('*/*/index');
    }
   
	 protected function _isAllowed()
    {
		return true;
        //return $this->_authorization->isAllowed('Magebees_Navigationmenupro::manageitems');
    }
}
