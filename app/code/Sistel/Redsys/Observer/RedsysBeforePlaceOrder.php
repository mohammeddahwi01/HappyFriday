<?php
/**
 * Sistel
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the sistel.com license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Paymen Methods - Redsys
 * @package     Sistel_Redsys
 * @author      Juan Pedro Barba Soler || Developer at Sistel, Servicios Informáticos de Software y Telecomunicaciones
 * @copyright   Copyright (c) 2016 Sistel (http://www.sistel.es/)
 * @license     See root folder of this extension
**/

namespace Sistel\Redsys\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class RedsysBeforePlaceOrder implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
	public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig             = $scopeConfig;
    }

    /**
     * Prevent sending New Order Message when Create Order
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $order
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        // Gettin Notify Order Value
        $sendNewOrderEmail = $this->getConfigValue('payment/redsys/order_settings/new_order_notify');

        if($sendNewOrderEmail && $order->getPayment()->getMethodInstance()->getCode() == \Sistel\Redsys\Model\Redsys::METHOD_CODE){
            $order->setCanSendNewEmailFlag(false);
        }
        return $order;
    }

    /*
    * Helper Function to Get Config Store Values
    */
    private function getConfigValue($path){
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}
