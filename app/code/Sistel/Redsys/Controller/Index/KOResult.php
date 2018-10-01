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

class KOResult extends Action
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
				$this->resultPageFactory 				= $resultPageFactory;
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
				$this->_logger				          = $logger;
    }

    public function execute()
    {
        $this->redsysLog(__('Comienza KO Result'));
        // Function getConfigValues get all module Configurations
        $restoreQuoteIfError        = $this->getConfigValue('payment/redsys/order_settings/pay_error');
        $customerNotifyInvoice      = $this->getConfigValue('payment/redsys/order_settings/customer_notify_invoice');
        $failureCheckoutRedirect    = $this->getConfigValue('payment/redsys/advanced_settings/failure_checkout_redirect');
        $custom_redirect			= $this->getConfigValue('payment/redsys/advanced_settings/custom_redirect');
        $koResult            		= $custom_redirect && $this->getConfigValue('payment/redsys/advanced_settings/kourl')
																			? $this->getConfigValue('payment/redsys/advanced_settings/kourl')
																			: 'checkout/onepage/failure';


        // Generates new ID for log messages
        $idLog = generateIdLog();

        $this->redsysLog(__('Get Request: ').$this->getRequest());
        if($this->getRequest()->isGet() && $this->getRequest()->getParam('orderId')){
            $orderId = $this->getRequest()->getParam('orderId');
            $order = $this->_orderFactory->create()->loadByIncrementId($orderId);
//            $this->redsysLog(__('Order ID: ').$orderId.': '.__('User comes from Redsys to KO URL without Redsys Parameters.'));// Log Response

            $this->redsysLog(__('Order ID: ').$orderId.__(', with status ').$order->getStatus().','._(' seems to be WRONG, then redirect to failure'));// Log Response

			$this->redsysLog(__('Order ID: ').$orderId.__('canCancel: ').$order->canCancel());
            if( $order->canCancel() ){
                    // Setting New Status
                    $status = 'canceled';
                    $this->setOrderStatus(
                        $order,         // Order Object
                        'new',          // New State
                        $status,     // Status Value
                        __('Redsys updated the order status with value:').' "'.$status.'".',// Comment
                        false           // Is Customer Notified
                    );
            }

            $this->messageManager->addError(__('We are sorry, something was wrong with payment. Try again or select another payment method.'));

            if($restoreQuoteIfError) $this->restoreQuote($order);

            if($failureCheckoutRedirect){
                $this->_redirect('checkout/cart'); // Redirect failure
            }else {
                $this->_redirect($koResult); // Redirect failure
            }
        } else {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__("Redsys don't respond"));
            return $resultPage;
        }
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
}
