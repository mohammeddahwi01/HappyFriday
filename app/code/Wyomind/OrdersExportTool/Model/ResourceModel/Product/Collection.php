<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Model\ResourceModel\Product;

/**
 * Product collection
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{

    public function searchProducts($ids)
    {
        $this->addAttributeToSelect('export_to')->getSelect()->where("entity_id IN (?)", $ids);
        return $this;
    }
}
