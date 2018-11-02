<?php

namespace Happyfriday\Customerlogin\Plugin;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Payment\Model\Config;

class DefaultConfigProvider
{
	/**
     * @var ScopeConfigInterface
     */
    protected $_appConfigScopeConfigInterface;
    /**
     * @var Config
     */
    protected $_paymentModelConfig;
    protected $shipconfig;
     
    /**
     * @param ScopeConfigInterface $appConfigScopeConfigInterface
     * @param Config               $paymentModelConfig
     */
    public function __construct(
        ScopeConfigInterface $appConfigScopeConfigInterface,
        Config $paymentModelConfig,
        \Magento\Shipping\Model\Config $shipconfig
    ) {
 
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->_paymentModelConfig = $paymentModelConfig;
        $this->shipconfig = $shipconfig;
    }
	
	public function afterGetConfig
	(
		\Magento\Checkout\Model\DefaultConfigProvider $subject,
		array $result
	) {
		$activeCarriers = $this->shipconfig->getActiveCarriers();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

		$shippingMethods = [];
		foreach ($activeCarriers as $carrier) {
			$carrierTitle = $this->_appConfigScopeConfigInterface
	                ->getValue('carriers/'.$carrier->getId().'/title');

	        $shippingMessage = 'Shipping in 1-5 business days';
			$shippingMethods[] = array(
				'label' => $carrierTitle,
				'value' => $carrier->getId(),
				'message' => $shippingMessage
			);
		}

		$payments = $this->_paymentModelConfig->getActiveMethods();
        $paymentMethods = array();

        //$unwanted_payment_methods = ['free', 'paypal_billing_agreement', 'sfm_shopping_feed_order'];
        foreach ($payments as $paymentCode => $paymentModel) {
        	//if (!in_array($paymentCode, $unwanted_payment_methods)) {
	            $paymentTitle = $this->_appConfigScopeConfigInterface
	                ->getValue('payment/'.$paymentCode.'/title');

	            $paymentMessage = 'Fill in the fields to complete the payment';
	            $paymentImage = 'https://happyfridaypruebas.factoriadigitalpremium.es/pub/static/version1541079571/frontend/Abbacus/happyfriday/en_GB/Sistel_Redsys/images/icon_cards.png';

	            $paymentMethods[] = array(
	                'label' => $paymentTitle,
	                'value' => $paymentCode,
	                'message' => $paymentMessage,
	                'image' => $paymentImage
	            );
        	//}
        }

		$result['activeShippingMethods'] = $shippingMethods;
		$result['activePaymentMethods'] = $paymentMethods;

		return $result;
	}
}