<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin\Quote\Model\Cart;

class CartTotalRepository
{
    /**
     * Cart totals factory.
     *
     * @var \Magento\Quote\Api\Data\TotalsInterfaceFactory
     */
    private $totalsFactory;

    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool
     */
    private $itemConverter;

    /**
     * @var \Magento\Quote\Api\CouponManagementInterface
     */
    private $couponService;

    /**
     * @var \Magento\Quote\Model\Cart\TotalsConverter
     */
    private $totalsConverter;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(
        \Magento\Quote\Api\Data\TotalsInterfaceFactory $totalsFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Quote\Api\CouponManagementInterface $couponService,
        \Magento\Quote\Model\Cart\TotalsConverter $totalsConverter,
        \Magento\Quote\Model\Cart\Totals\ItemConverter $converter,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->totalsFactory = $totalsFactory;
        $this->quoteRepository = $quoteRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->couponService = $couponService;
        $this->totalsConverter = $totalsConverter;
        $this->itemConverter = $converter;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param $subject
     * @param \Closure $proceed
     * @param $cartId
     * @return \Magento\Quote\Api\Data\TotalsInterface|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGet($subject, \Closure $proceed, $cartId)
    {
        if (version_compare($this->productMetadata->getVersion(), '2.2.4', '>=')) {
            return $proceed($cartId);
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if ($quote->isVirtual()) {
            $addressTotalsData = $quote->getBillingAddress()->getData();
            $addressTotals = $quote->getBillingAddress()->getTotals();
        } else {
            $addressTotalsData = $quote->getShippingAddress()->getData();
            $addressTotals = $quote->getShippingAddress()->getTotals();
        }
        unset($addressTotalsData[\Magento\Framework\Api\ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);

        /** @var \Magento\Quote\Api\Data\TotalsInterface $quoteTotals */
        $quoteTotals = $this->totalsFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $quoteTotals,
            $addressTotalsData,
            \Magento\Quote\Api\Data\TotalsInterface::class
        );
        $items = [];
        foreach ($quote->getAllVisibleItems() as $index => $item) {
            $items[$index] = $this->itemConverter->modelToDataObject($item);
        }
        $calculatedTotals = $this->totalsConverter->process($addressTotals);
        $quoteTotals->setTotalSegments($calculatedTotals);

        $amount = $quoteTotals->getGrandTotal() - $quoteTotals->getTaxAmount();
        $amount = $amount > 0 ? $amount : 0;
        $quoteTotals->setCouponCode($this->couponService->get($cartId));
        $quoteTotals->setGrandTotal($amount);
        $quoteTotals->setItems($items);
        $quoteTotals->setItemsQty($quote->getItemsQty());
        $quoteTotals->setBaseCurrencyCode($quote->getBaseCurrencyCode());
        $quoteTotals->setQuoteCurrencyCode($quote->getQuoteCurrencyCode());

        return $quoteTotals;
    }
}
