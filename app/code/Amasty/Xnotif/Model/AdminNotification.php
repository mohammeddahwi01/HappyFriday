<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Model;

use Amasty\Xnotif\Model\ResourceModel\AdminNotification\CollectionFactory as NotificationCollectionFactory;
use Amasty\Xnotif\Helper\Data;
use Amasty\Xnotif\Block\AdminNotify;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\State;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\Store;

class AdminNotification
{
    /**
     * @var NotificationCollectionFactory
     */
    private $notificationsFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var AdminNotify
     */
    private $adminNotify;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    public function __construct(
        NotificationCollectionFactory $notificationsFactory,
        Data $helper,
        AdminNotify $adminNotify,
        Emulation $appEmulation,
        State $appState,
        LoggerInterface $logger,
        TransportBuilder $transportBuilder
    ) {
        $this->notificationsFactory = $notificationsFactory;
        $this->helper = $helper;
        $this->adminNotify = $adminNotify;
        $this->appEmulation = $appEmulation;
        $this->appState = $appState;
        $this->logger = $logger;
        $this->transportBuilder = $transportBuilder;
    }

    public function sendAdminNotifications()
    {
        $emailTo = $this->helper->getModuleConfig('admin_notifications/stock_alert_email');
        if (strpos($emailTo, ',') !== false) {
            $emailTo = explode(',', $emailTo);
        }
        $sender = $this->helper->getModuleConfig('admin_notifications/sender_email_identity');
        if ($emailTo && $sender) {
            try {
                $this->adminNotify->setSubscriptionCollection(
                    $this->notificationsFactory->create()->getCollection()
                );

                $this->appEmulation->startEnvironmentEmulation(Store::DEFAULT_STORE_ID);
                $subscriptionGrid = $this->appState->emulateAreaCode(
                    Area::AREA_FRONTEND,
                    [$this->adminNotify, 'toHtml']
                );
                $this->appEmulation->stopEnvironmentEmulation();

                $transport = $this->transportBuilder->setTemplateIdentifier(
                    $this->helper->getModuleConfig('admin_notifications/notify_admin_template')
                )->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => Store::DEFAULT_STORE_ID
                    ]
                )->setTemplateVars(
                    [
                        'subscriptionGrid' => $subscriptionGrid
                    ]
                )->setFrom(
                    $sender
                )->addTo(
                    $emailTo
                )->getTransport();

                $transport->sendMessage();
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
