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
 	
 	protected $storeManager;


    /**
     * @param ScopeConfigInterface $appConfigScopeConfigInterface
     * @param Config               $paymentModelConfig
     */
    public function __construct(
        ScopeConfigInterface $appConfigScopeConfigInterface,
        Config $paymentModelConfig,
        \Magento\Shipping\Model\Config $shipconfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
 
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->_paymentModelConfig = $paymentModelConfig;
        $this->shipconfig = $shipconfig;
        $this->storeManager = $storeManager;
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
        $currentStore = $this->storeManager->getStore();
        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $paypalImage = $mediaUrl . 'wysiwyg/paypal.png';
        $paypalMessage = 'With your credit or debit card';
        $redsysImage = $mediaUrl . 'wysiwyg/icon_cards.jpg';
        $redsysMessage = 'Fill in the fields to complete the payment';

        //$unwanted_payment_methods = ['free', 'paypal_billing_agreement', 'sfm_shopping_feed_order'];
        foreach ($payments as $paymentCode => $paymentModel) {
        	//if (!in_array($paymentCode, $unwanted_payment_methods)) {
	            $paymentTitle = $this->_appConfigScopeConfigInterface
	                ->getValue('payment/'.$paymentCode.'/title');

	            if ($paymentCode == 'paypal_billing_agreement') {
		            $paymentMessage = $paypalMessage;
		            $paymentImage = $paypalImage;
	            }
	        	else if ($paymentCode == 'redsys') {
		            $paymentMessage = $redsysMessage;
		            $paymentImage = $redsysImage;
	            }
	            else {
	            	$paymentMessage = '';
		            $paymentImage = '';
	            }

	            $paymentMethods[] = array(
	                'label' => $paymentTitle,
	                'value' => $paymentCode,
	                'message' => $paymentMessage,
	                'image' => $paymentImage
	            );
        	//}
        }

        $selectedShippingRate = $result['selectedShippingMethod']['base_amount'];
        $selectedShippingRate = number_format((float)$selectedShippingRate, 2, '.', '');

		$result['selectedShippingRate'] = '$'.$selectedShippingRate;
		$result['activeShippingMethods'] = $shippingMethods;
		$result['activePaymentMethods'] = $paymentMethods;

		return $result;
	}
}