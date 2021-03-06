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
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\Framework\Event\Observer;


class RedsysResultSuccessSubtractStock implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\CatalogInventory\Observer\SubtractQuoteInventoryObserver
     */
    protected $subtractQuoteInventoryObserver;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\CatalogInventory\Observer\SubtractQuoteInventoryObserver $subtractQuoteInventoryObserver
     */
	public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\CatalogInventory\Observer\SubtractQuoteInventoryObserver $subtractQuoteInventoryObserver
    ) {
        $this->_scopeConfig                     = $scopeConfig;
        $this->subtractQuoteInventoryObserver   = $subtractQuoteInventoryObserver;
    }

    /**
     * Subtract stock when payment Success
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $order
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        // Gettin Notify Order Value
        $stockUpdate = $this->getConfigValue('payment/redsys/order_settings/success_payment_stock_update');

        if($stockUpdate && $order->getPayment()->getMethodInstance()->getCode() == \Sistel\Redsys\Model\Redsys::METHOD_CODE){
            $quote->getInventoryProcessed(false);
            $this->subtractQuoteInventoryObserver->execute($observer);
            $quote->getInventoryProcessed(true);
            return $this;
        }
    }

    /*
    * Helper Function to Get Config Store Values
    */
    private function getConfigValue($path){
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}
