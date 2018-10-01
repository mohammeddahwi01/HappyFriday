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

class Result extends Action
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
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $transaction;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var RedsysApi
     */
    protected $redsysObj;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     * @param \Sistel\Redsys\Logger\Logger
     */
	public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        // \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Sistel\Redsys\Logger\Logger $logger,
        array $data = []
    ) {
        parent::__construct(
            $context
        );
        $this->resultPageFactory 		= $resultPageFactory;
        $this->_scopeConfig             = $scopeConfig;
        $this->_checkoutSession         = $checkoutSession;
        $this->_customerSession         = $customerSession;
        $this->_invoiceService          = $invoiceService;
        $this->_transaction             = $transaction;
        $this->_orderFactory            = $orderFactory;
        $this->_quoteRepository         = $quoteRepository;
        // $this->_eventManager            = $eventManager;
        $this->_orderSender             = $orderSender;
        $this->_invoiceSender           = $invoiceSender;
        $this->_logger				    = $logger;
    }

    public function execute()
    {
        $this->redsysLog(__('Comienza Result'));
        // Function getConfigValues get all module Configurations
        $commerceName               = $this->getConfigValue('payment/redsys/basic_settings/commerce_name');
        $key                        = $this->getConfigValue('payment/redsys/basic_settings/key256');
        $terminalConf               = $this->getConfigValue('payment/redsys/basic_settings/terminal');
        $codeConf                   = $this->getConfigValue('payment/redsys/basic_settings/commerce_num');
        $transactionTypeConf        = $this->getConfigValue('payment/redsys/basic_settings/transaction_type');

        $paymentStatus        			= $this->getConfigValue('payment/redsys/order_settings/payment_status');
        $restoreQuoteIfError        = $this->getConfigValue('payment/redsys/order_settings/pay_error');
        $generateInvoice            = $this->getConfigValue('payment/redsys/order_settings/generate_invoice');
        $customerNotifyInvoice      = $this->getConfigValue('payment/redsys/order_settings/customer_notify_invoice');
        $customerPaymentMessage     = $this->getConfigValue('payment/redsys/order_settings/payment_message');
        $keepStock                	= $this->getConfigValue('payment/redsys/order_settings/success_payment_stock_update');

        $failureCheckoutRedirect    = $this->getConfigValue('payment/redsys/advanced_settings/failure_checkout_redirect');
        $isActiveLog                = $this->getConfigValue('payment/redsys/advanced_settings/active_log');

        // Generates new ID for log messages
        $idLog = generateIdLog();

        // Get if GET or POST method
        $this->redsysLog(__('Get Request: ').$this->getRequest());
        if ($this->getRequest()->isPost())
        {
            // Get result Data
            $version         = $this->getRequest()->getParam("Ds_SignatureVersion");
            $DS_data         = $this->getRequest()->getParam("Ds_MerchantParameters");
            $signature    	 = $this->getRequest()->getParam("Ds_Signature");

            // Create RedsysAPI Object
            $redsysObj = new apiRedsys;
            // Decode MerchantParameters and create array
            $decodec = $redsysObj->decodeMerchantParameters($DS_data);
            // Create Signature
            $firma_local = $redsysObj->createMerchantSignatureNotif($key,$DS_data);

            // Getting order data from object
            $total     	= $redsysObj->getParameter('Ds_Amount');
            $getOrder   = $redsysObj->getParameter('Ds_Order'); // Order ID
            $code      	= $redsysObj->getParameter('Ds_MerchantCode');
            $terminal  	= $redsysObj->getParameter('Ds_Terminal');
            $currency  	= $redsysObj->getParameter('Ds_Currency');
            $response 	= $redsysObj->getParameter('Ds_Response');
            $date	   	= $redsysObj->getParameter('Ds_Date');
            $hour	   	= $redsysObj->getParameter('Ds_Hour');
            $id_trans  	= $redsysObj->getParameter('Ds_AuthorisationCode');
            $transactionType = $redsysObj->getParameter('Ds_TransactionType');

            // Getting Order By Id from object
            $orderId = substr($getOrder, 0, 11);
            if(is_numeric($getOrder)) $orderId = intval(substr($getOrder, 0, 11));
            $order = $this->_orderFactory->create()->loadByIncrementId($orderId);
            // Get Customer
            $customer = $this->_customerSession->isLoggedIn() ? $this->_customerSession->getCustomer() : $order->getBillingAddress();

            if ($firma_local === $signature
                && checkImporte($total)
                && checkPedidoNum($getOrder)
                && checkFuc($code)
                && checkMoneda($currency)
                && checkRespuesta($response)
                && $transactionType == $transactionTypeConf
                && $code == $codeConf
                && intval(strval($terminalConf)) == intval(strval($terminal))
                ) {

                $response     = intval($response);

                $this->redsysLog(__('New Order Log with ID').': '.$idLog);// Log Response
                $this->redsysLog($idLog.' - '.__("Response Code").': '.$response.'. '.__("For Order with ID").' '.$getOrder);// Log Response

                if($response == 9915) {
                    $this->redsysLog($idLog.' - '.__("User Canceled the Operation. ").__("The User Email for contact is: ").$customer->getEmail());// User Canceled
                }
                /*
                    CODE 9915 = User canceled
                */
                if ($response < 101){
                    $this->redsysLog($idLog.' - '.__("Payment Accepted"));// Payment Accepted But can be invalid!

                    $transaction_amount = number_format($order->getBaseGrandTotal(),2,'', '');
                    $amountConf = (float)$transaction_amount;
                    if ($amountConf != $total) {
                        // Log Diferent Amount
                        $this->redsysLog($idLog.' - '.__("Total ammount ( ").$amountConf.__(" ) isn't the same as server total ( ").$total.' )');
                        $this->redsysLog($idLog.' - '.__("Order with ID ").$orderId.__(" is invalid"));

                        // Setting New Status
                        $status = 'canceled';
                        $this->setOrderStatus(
                            $order,         // Order Object
                            'new',          // New State
                            $status,     // Status Value
                            __('Redsys updated the order status with value:').' "'.$status.'".',// Comment
                            false           // Is Customer Notified
                        );

                    } else {
                        try {
                            // New Status
                            $status = 'pending';

                            // Setting New Status
                            $this->setOrderStatus(
                                $order,         // Order Object
                                'new',          // New State
                                $status,        // Status Value
                                __('Redsys updated the order status with value:').' "'.$status.'".',// Comment
                                true            // Is Customer Notified
                            );

                            $this->redsysLog(__('Verifica la autogeneración de la factura'));
                            //Auto Generate Invoice
                            if($order->canInvoice() && $generateInvoice ) {
                                $this->redsysLog($idLog.' - '.__("Order with ID ").$orderId.__(" is going to be invoiced"));
                                $status = $this->generateInvoice( $order, $customerNotifyInvoice );
                                $this->redsysLog($idLog.' - '.__("Order with ID ").$orderId.__(" was invoiced"));
                            }

                            $this->redsysLog(__('Setea que se va a enviar el email'));
                            // Sending New Order Mail to Customer
                            if($order->setCanSendNewEmailFlag(true)){
                                $this->redsysLog(__('Va a enviar el email'));
                                $this->_orderSender->send($order);
                                $this->redsysLog(__('Envia el email'));
                            }

                            // Succsess Order
                            $this->redsysLog($idLog.' - '.__("Order with ID ").$orderId.__(" is valid and was recorded correctly."));

                            if($keepStock){
                                $quote = $this->_quoteRepository->get($order->getQuoteId());
                                $this->_eventManager->dispatch('redsys_result_success_redirect', ['order' => $order, 'quote' => $quote]);
                            };


                        } catch (\Magento\Framework\Exception\LocalizedException $e) {
                            $this->redsysLog( $idLog.' - '.__("Order with ID ").$orderId.__(" Excepction: ").$e );
                            // Add Order Status Comment
                            $order->addStatusHistoryComment(__('Redsys: Exception message: ').$e->getMessage(), false);
                            $order->save();

                        }
                    }
                } else {
                    // Error Response
                    $this->redsysLog($idLog.' - '.__("Order with ID ").$orderId.__(" has invalid response: ").$response);
                    // Setting New Status
                    $status = 'canceled';
                    $this->setOrderStatus(
                        $order,         // Order Object
                        'new',          // New State
                        $status,        // Status Value
                        __('Redsys updated the order status with value:').' "'.$status.'".',// Comment
                        false            // Is Customer Notified
                    );
                }
            } else {
                $this->redsysLog($idLog.' - '.__("Didn't pass the validation"));

                // Setting New Status
                $status = 'canceled';
                $this->setOrderStatus(
                      $order,         // Order Object
                      'new',          // New State
                      $status,        // Status Value
                      __('Redsys updated the order status with value:').' "'.$status.'".',// Comment
                      false            // Is Customer Notified
                );

            }
        } else {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__("Redsys don't respond"));
            return $resultPage;
        }
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
        if($status == 'canceled' && !$this->getConfigValue('payment/redsys/order_settings/success_payment_stock_update') && true ){
            $order->registerCancellation($comment);
        }
        else if ($status == 'canceled' && $this->getConfigValue('payment/redsys/order_settings/success_payment_stock_update') && true) {
          $order->setState('closed');// Set the new state
          $order->addStatusToHistory('closed',__('Closed Order for Error or Cancellation'),false);
        }
        else {
            $order->setState($state);// Set the new state
            $order->addStatusToHistory($status,$comment,$isCustomerNotified);// Set a histroy status
        }
        // Save the status
        $order->save();
		}

	  /*
	  * Helper Function to Send Email
	  */
	  protected function sendEmail($from, $to, $subject, $message) {
		if(mail($to, $subject, $message,"From:".$from)){
	          return true;
	      }
		}

    /*
    * Helper Function to Restore Quote
    */
    public function restoreQuote($order)
    {
        if ($order->getId()) {
            try {
                $quote = $this->_quoteRepository->get($order->getQuoteId());
                $quote->setIsActive(1)->setReservedOrderId(null);
                $this->_quoteRepository->save($quote);
                $this->_checkoutSession->replaceQuote($quote)->unsLastRealOrderId();
                $this->_eventManager->dispatch('redsys_restore_quote', ['order' => $order, 'quote' => $quote]);
                return true;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            }
        }
        return false;
    }

		/*
    * Generate Invoice
    */
		protected function generateInvoice($order, $customerNotifyInvoice)
    {
			//START Handle Invoice
			$invoice = $this->_invoiceService->prepareInvoice($order);
			$invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
			$invoice->register();
			$invoice->getOrder()->setCustomerNoteNotify($customerNotifyInvoice);
			$invoice->getOrder()->setIsInProcess(true);

			$transactionSave = $this->_transaction->addObject($invoice)->addObject($invoice->getOrder());
			$transactionSave->save();


			if($customerNotifyInvoice){
					$order->addStatusHistoryComment(__('Redsys generated Order Invoice and sent it to Customer'), 'processing');
					$this->_invoiceSender->send($invoice);
					$this->setOrderStatus(
							$order,         // Order Object
							'new',          // New State
							"processing",        // Status Value
							__('Redsys invoiced the order, send customer notification and updated status with value:').' "'.__("processing").'".',// Comment
							true            // Is Customer Notified
					);
			} else {
					$order->addStatusHistoryComment(__('Redsys generated Order Invoice'), 'processing');
					$this->setOrderStatus(
							$order,         // Order Object
							'new',          // New State
							"processing",        // Status Value
							__('Redsys invoiced the order and updated status with value:').' "'.__("processing").'".',// Comment
							false            // Is Customer Notified
					);
			}
			//END Handle Invoice

			// Update Status
			return 'processing';
		}
}
