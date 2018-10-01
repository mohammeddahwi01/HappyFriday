<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Plugins\CatalogInventory\Observer;

use Magento\CatalogInventory\Observer\SubtractQuoteInventoryObserver;
use Magento\CatalogInventory\Observer\ItemsForReindex;
use Amasty\Xnotif\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Area;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Element\Template;

class LowStockAlert
{
    const XML_PATH_LOW_STOCK_CONFIG = 'admin_notifications/low_stock_alert';

    const XML_PATH_EMAIL_TO = 'admin_notifications/stock_alert_email';

    const XML_PATH_SENDER_EMAIL = 'admin_notifications/sender_email_identity';

    const EMAIL_TEMPLATE_VAR = 'low_stock_alert';

    const TEMPLATE_FILE = 'Amasty_Xnotif::notifications/low_stock_alert.phtml';

    /**
     * @var ItemsForReindex
     */
    private $itemsForReindex;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Layout
     */
    private $layout;

    public function __construct(
        ItemsForReindex $itemsForReindex,
        Data $helper,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
        Layout $layout
    ) {
        $this->itemsForReindex = $itemsForReindex;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->layout = $layout;
    }

    /**
     * @param SubtractQuoteInventoryObserver $subject
     * @param SubtractQuoteInventoryObserver $result
     */
    public function afterExecute($subject, $result)
    {
        $lowStockItems = $this->itemsForReindex->getItems();
        if ($this->helper->getModuleConfig(self::XML_PATH_LOW_STOCK_CONFIG)
            && count($lowStockItems) > 0
            && $emailTo = $this->helper->getModuleConfig(self::XML_PATH_EMAIL_TO)
        ) {
            $products = [];
            $store = $this->storeManager->getStore();
            $sender = $this->helper->getModuleConfig(self::XML_PATH_SENDER_EMAIL);
            if (strpos($emailTo, ',') !== false) {
                $emailTo = explode(',', $emailTo);
            }
            try {
                foreach ($lowStockItems as $lowStockItem) {
                    $product = $this->productRepository->getById($lowStockItem->getProductId());
                    $products[] = [
                        'name' => $product->getName(),
                        'sku' => $product->getSku(),
                        'qty' => $lowStockItem->getQty()
                    ];
                }
                /** @var Template $lowStockAlert */
                $lowStockAlert = $this->layout->createBlock(Template::class)
                    ->setTemplate(self::TEMPLATE_FILE)
                    ->setData('lowStockProducts', $products);

                $transport = $this->transportBuilder->setTemplateIdentifier(
                    self::EMAIL_TEMPLATE_VAR
                )->setTemplateOptions(
                    ['area' => Area::AREA_FRONTEND, 'store' => $store->getId()]
                )->setTemplateVars(
                    [
                        'alertGrid' => $lowStockAlert->toHtml()
                    ]
                )->setFrom(
                    $sender
                )->addTo(
                    $emailTo
                )->getTransport();

                $transport->sendMessage();
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
    }
}
