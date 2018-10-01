<?php
    namespace Magebees\Navigationmenupro\Model;

class Product extends \Magento\Framework\Model\AbstractModel
{
    public function checkProductavailable($product_id)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $storemanager= $om->create('Magento\Store\Model\StoreManagerInterface');
        $current_storeid = $storemanager->getStore()->getStoreId();
        $product = $om->create('Magento\Catalog\Model\Product');
        $product->setStoreId($current_storeid);
        $product = $product->load($product_id);
        $pro_webiste = $product->getWebsiteIds();
        $website_id = $storemanager->getWebsite()->getId();
        $allow_pro = '0';
    /* Check Product is Enable Or Disable
    Check the Product Visibility is not Visible Individually
	*/
    
        if (($product->getStatus() == "1") && ($product->getVisibility() != "1")) {
            foreach ($pro_webiste as $key => $value) :
                if ($value == $website_id) {
                    $allow_pro = '1';
                }
            endforeach;
        }
        return $allow_pro;
    }
}
