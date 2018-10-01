<?php
    namespace Magebees\Navigationmenupro\Model;

class Customer extends \Magento\Framework\Model\AbstractModel
{
    public function isLoggedIn()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $om->create('Magento\Customer\Model\Session');
        $customer = $om->create('Magento\Customer\Model\Session');
        $id=$session->getCustomerId();
        if ($session->isLoggedIn()) {
            return 1;
        } else {
            return 0;
        }
    }
    public function getUserPermission()
    {
        $permission = [];
        $permission [] = -2; /* For Public Menu Items*/
        $customerGroup = null;
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $om->create('Magento\Customer\Model\Session');
        if ($session->isLoggedIn()) {
            $customerGroup =$session->getCustomerGroupId();
            $permission [] = -1;/* For Register User Menu Items*/
            $permission [] = $customerGroup;
        } else {
            $permission [] =$session->getCustomerGroupId();
        }
        return $permission;
    }
}
