<?php
    namespace Magebees\Navigationmenupro\Model;

class Category extends \Magento\Framework\Model\AbstractModel
{
    public function checkCategoryAvailable($cat_id)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $storemanager= $om->create('Magento\Store\Model\StoreManagerInterface');
        $current_storeid = $storemanager->getStore()->getStoreId();
        $category =$om->create('Magento\Catalog\Model\Category');
        $category->setStoreId($current_storeid);
        $category = $category->load($cat_id);
        $allow_cat = '0';
    
    /* Check Category Is Available Or not is_active*/
        if (($category->getIsActive() == "1") && ($category->getIncludeInMenu()=="1")) {
            $rootCategoryId =$storemanager->getStore()->getRootCategoryId();
            /*Check Root Category Is available in the Category Path Or not*/
            $pos = strpos($category->getPath(), "/".$rootCategoryId."/");
            if ($pos != '') {
                $allow_cat = '1';
            }
            /* Here If the Current Category is the Root Category then Also it will allow*/
            if ($rootCategoryId == $cat_id) {
                $allow_cat = '1';
            }
        }
        return $allow_cat;
    }
    public function getChildCategoryCount($cat_id)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $storemanager= $om->get('Magento\Store\Model\StoreManagerInterface');
        $current_storeid = $storemanager->getStore()->getStoreId();
        $rootCategoryId = $storemanager->getStore()->getRootCategoryId();
        $rootpath =$om->get('Magento\Catalog\Model\Category')
                    ->setStoreId($current_storeid)
                    ->load($rootCategoryId)
                    ->getPath();
        $childCats = $om->get('Magento\Catalog\Model\Category')->setStoreId($current_storeid)
                    ->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('include_in_menu', ['eq' => 1])
                    ->addAttributeToFilter('is_active', ['eq' => 1])
                    ->addAttributeToFilter('parent_id', ['eq' => $cat_id])
                    ->addAttributeToFilter('path', ["like"=>$rootpath."/"."%"]);
        return count($childCats->getData());
    }
}
