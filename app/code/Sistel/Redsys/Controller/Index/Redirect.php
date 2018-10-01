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

namespace Sistel\Redsys\Controller\Index;

// Redsys Api Log
if(!function_exists("escribirLog")) {
	require_once('redsysLibrary.php');
}

use Magento\Framework\App\Action\Action;

class Redirect extends Action
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;
    /**
     * @var Order
     */
    protected $order;
    /**
     * @var RedsysObj
     */
    protected $redsysObj;

		protected $currencies  = [
			'EUR' => "978",
			'USD' => "840",
			'GBP' => "826",
			'JPY' => "392"
		];

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
	public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Checkout\Model\Session $checkoutSession,
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Sales\Model\OrderFactory $orderFactory,
            \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
            \Sistel\Redsys\Logger\Logger $logger,
            array $data = []
    ) {
            parent::__construct(
                $context
            );
            $this->_scopeConfig             = $scopeConfig;
            $this->_checkoutSession         = $checkoutSession;
            $this->_customerSession         = $customerSession;
            $this->_orderFactory            = $orderFactory;
            $this->_logger				    = $logger;
            $this->_storeManager			= $storeManagerInterface;
    }

    public function execute(){
            $this->redsysLog(__("Inicio del Redirect"));// Prevent
			// Function getConfigValues get all module Configurations
			$enviroment           = $this->getConfigValue('payment/redsys/basic_settings/enviroment');
			$commerce_name        = $this->getConfigValue('payment/redsys/basic_settings/commerce_name');
			$commerce_num         = $this->getConfigValue('payment/redsys/basic_settings/commerce_num');
			$key256               = $this->getConfigValue('payment/redsys/basic_settings/key256');
			$terminal             = $this->getConfigValue('payment/redsys/basic_settings/terminal');
			$trans                = $this->getConfigValue('payment/redsys/basic_settings/transaction_type');
			$languages            = $this->getConfigValue('payment/redsys/basic_settings/languages');
			// $payment_type         = $this->getConfigValue('payment/redsys/basic_settings/payment_type');
			$payment_type 		  = "C";
			$paymentStatus        = $this->getConfigValue('payment/redsys/order_settings/payment_status');

            // Get ISO Code from Currency
            $currency   = $this->getConfigValue('payment/redsys/basic_settings/currency');
			$currentCurrency = $this->_storeManager->getStore()->getCurrentCurrencyCode();
			$currentCurrencyCode = $this->currencies[$currentCurrency];

			if($currentCurrency && $currency !== $currentCurrencyCode) $currency = $currentCurrencyCode;

			// Setting custom terminal if exist
			$currencyTerminal = $this->getConfigValue('payment/redsys/advanced_currency/'.strtolower($currentCurrency));
			if($currencyTerminal !== NULL) $terminal = $currencyTerminal;

			// Setting custom SHA256 if exist
			$currencySHA = $this->getConfigValue('payment/redsys/advanced_currency/'.strtolower($currentCurrency).'_key');
			if($currencySHA !== NULL) $key256 = $currencySHA;

			//Get Order
			$orderId  = $this->_getCheckout()->getLastRealOrderId();
			// This could be interesting for check and send email to pending payment order emails
			// if($this->getRequest()->isGet() && $this->getRequest()->getParam('orderId')){
			// 	$orderId = $this->getRequest()->getParam('orderId');
			// }
	        $order    = $this->_orderFactory->create()->loadByIncrementId($orderId);

			// Redirect Result URL
			$commerce_url = $this->_url->getUrl('redsys/index/result', ['orderId' => $orderId]);
            $this->redsysLog(__("Commerce url: ").$commerce_url);// Prevent

			// If didn't access from checkout redirect to result
			if( !$orderId || !$order->canInvoice() || $order->getStatus() == 'pending'){
				$ip = isset($_SERVER['REMOTE_ADDR'])? $_SERVER['REMOTE_ADDR'] : 'unknown';
				$this->redsysLog(__("Someone tryed to access directly to redsys/index/redirect from ").$ip.' IP.');// Prevent
				return $this->_redirect('redsys/index/result');
			}
	        // Setting New Order Status
            $status = $paymentStatus;
            $this->setOrderStatus(
                $order,         // Order Object
                'new',          // New State
                $status,        // Status Value
                __('Redsys updated the order status with value:').' "'.$status.'".',// Comment
                false            // Is Customer Notified
            );

			// Get Customer
			$customer = $this->_customerSession->isLoggedIn()
	                ? $this->_customerSession->getCustomer()
	                : $order->getBillingAddress();
			$titular = $customer->getFirstname()." ".$customer->getMastname()." ".$customer->getLastname()."/ Correo:".$customer->getEmail();

	        //Get Order Products
			$products = '';
			$items = $order->getAllVisibleItems();
            foreach ($items as $itemId => $item) {
                $products .= $item->getName();
                $products .="X".$item->getQtyToInvoice();
                $products .="/";
            }

            // Total Order Format Price
			$transaction_amount = number_format($order->getBaseGrandTotal(),2,'', '');
			$amount = (float)$transaction_amount;
            $this->redsysLog(__("Amount ").$amount);// Prevent

            // Registry Customer and Order Values
			if($this->_checkoutSession->{"getP".$orderId}()){
				$sec_order = $this->_checkoutSession->{"getP".$orderId}();
			} else {
				$sec_order = -1;
			}
			if ($sec_order < 9) {
				$this->_checkoutSession->{"setP".$orderId}(++$sec_order);
			}
			$orderNumber = str_pad($orderId.$sec_order, 12, "0", STR_PAD_LEFT); // Setting Order Number

	        // Language Options
			if($languages == "0"){
	    	    $language_tpv="0";
			}
			else {
	        $language_web = substr($this->getConfigValue('general/locale/code'),0,2);
				switch ($language_web) {
					case 'es':
					$language_tpv='001';
					break;
					case 'en':
					$language_tpv='002';
					break;
					case 'ca':
					$language_tpv='003';
					break;
					case 'fr':
					$language_tpv='004';
					break;
					case 'de':
					$language_tpv='005';
					break;
					case 'nl':
					$language_tpv='006';
					break;
					case 'it':
					$language_tpv='007';
					break;
					case 'sv':
					$language_tpv='008';
					break;
					case 'pt':
					$language_tpv='009';
					break;
					case 'pl':
					$language_tpv='011';
					break;
					case 'gl':
					$language_tpv='012';
					break;
					case 'eu':
					$language_tpv='013';
					break;
					default:
					$language_tpv='002';
				}
			}
			$KOcommerce_url = $this->_url->getUrl('redsys/index/koresult', ['orderId' => $orderId]);
            $this->redsysLog(__("KO URL: ").$KOcommerce_url);// Prevent
			$OKcommerce_url = $this->_url->getUrl('redsys/index/okresult', ['orderId' => $orderId]);
            $this->redsysLog(__("OK URL: ").$OKcommerce_url);// Prevent

            // Setting Parameters to Redsys
            $redsysObj = new apiRedsys;
            $redsysObj->setParameter("DS_MERCHANT_AMOUNT",$amount);
            $redsysObj->setParameter("DS_MERCHANT_ORDER",strval($orderNumber));
            $redsysObj->setParameter("DS_MERCHANT_MERCHANTCODE",$commerce_num);
            $redsysObj->setParameter("DS_MERCHANT_CURRENCY",$currency);
            $redsysObj->setParameter("DS_MERCHANT_TRANSACTIONTYPE",$trans);
            $redsysObj->setParameter("DS_MERCHANT_TERMINAL",$terminal);
            $redsysObj->setParameter("DS_MERCHANT_MERCHANTURL",$commerce_url);
            $redsysObj->setParameter("DS_MERCHANT_URLOK",$OKcommerce_url);
            $redsysObj->setParameter("DS_MERCHANT_URLKO",$KOcommerce_url);
            $redsysObj->setParameter("Ds_Merchant_ConsumerLanguage",$language_tpv);
            $redsysObj->setParameter("Ds_Merchant_ProductDescription",$products);
            $redsysObj->setParameter("Ds_Merchant_Titular",$titular);
            $redsysObj->setParameter("Ds_Merchant_MerchantData",sha1($commerce_url));
            $redsysObj->setParameter("Ds_Merchant_MerchantName",$commerce_name);
            $redsysObj->setParameter("Ds_Merchant_PayMethods",$payment_type);
            $redsysObj->setParameter("Ds_Merchant_Module","sistel_redsys");

            // Data Settings
			//$version = getVersionClave();
			$version = "HMAC_SHA256_V1";

			// Create Merchant Signature and Parameters
			$paramsBase64 = $redsysObj->createMerchantParameters();
			$signatureMac = $redsysObj->createMerchantSignature($key256);

			// Checking Enviroment
			if ( $enviroment == "1" ){
				echo ('<form action="https://sis-t.redsys.es:25443/sis/realizarPago/utf-8" method="post" id="redsys_form" name="redsys_form">');}
			else {
				echo ('<form action="https://sis.redsys.es/sis/realizarPago/utf-8" method="post" id="redsys_form" name="redsys_form">');
			}

			// Setting Input Values
			echo
			'<style>
				.error-redsys-message {
					background: #f7f7f7;
					margin: auto;
					margin-top: 40px;
					padding: 40px 24px 40px;
					color: #666;
					text-align: center;
					width: 100%;
					max-width: 480px;
					font-family: "Open Sans", Hevetica, Arial;
				}
			</style>';
			echo ('
					<input type="hidden" name="Ds_SignatureVersion" value="'.$version.'" />
					<input type="hidden" name="Ds_MerchantParameters" value="'.$paramsBase64.'" />
					<input type="hidden" name="Ds_Signature" value="'.$signatureMac.'" />
					</form>'

					.'<div class="error-redsys-message">
					<h4>'.__("Redirecting to Redsys TPV... Please wait.").'</h4>
					</div>
					<script type="text/javascript">
						document.redsys_form.submit();
					</script>'
            );

            $this->redsysLog(__("Fin del Redirect"));// Prevent
    }

    /**
    * Get frontend checkout session object
    *
    * @return \Magento\Checkout\Model\Session
    */
    protected function _getCheckout()
    {
        return $this->_checkoutSession;
    }

    /*
    * Helper Function to Get Config Store Values
    */
    private function getConfigValue($path){
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

	/*
	* Helper Function to write Log Comments
	*/
	public function redsysLog($message) {
		if( $this->getConfigValue('payment/redsys/advanced_settings/active_log') ){
			$this->_logger->info($message);
		}
	}

    /*
    * Helper Function to Set Order State
    */
    protected function setOrderStatus(&$order, $state, $status, $comment, $isCustomerNotified ) {

        if($status == 'canceled'){
            $order->registerCancellation($comment);
        }
        else {
            $order->setState($state);// Set the new state
            $order->addStatusToHistory($status,$comment,$isCustomerNotified);// Set a histroy status
        }
        // Save the status
        $order->save();
	}
}
