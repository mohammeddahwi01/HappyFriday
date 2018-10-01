<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderEraser\Controller\Adminhtml;

abstract class Orders extends \Magento\Backend\App\Action
{

    public $ordersCollectionFactory = null;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Wyomind\OrderEraser\Model\ResourceModel\Orders\CollectionFactory $ordersCollectionFactory
    ) {
        $this->ordersCollectionFactory = $ordersCollectionFactory;
        parent::__construct($context);
    }

    abstract public function execute();
}
