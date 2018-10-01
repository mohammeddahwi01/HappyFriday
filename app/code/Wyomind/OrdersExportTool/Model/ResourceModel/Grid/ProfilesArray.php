<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Model\ResourceModel\Grid;

/**
 *
 */
class ProfilesArray implements \Magento\Framework\Option\ArrayInterface
{

    protected $_collectionFactory;

    public function __construct(\Wyomind\OrdersExportTool\Model\ResourceModel\Profiles\Collection $collectionFactory)
    {
        $this->_collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $profiles = [];
        $profiles[] = __('Not exported');
        foreach ($this->_collectionFactory as $profile) {
            $profiles[] = [
                "value" => $profile->getId(),
                "label" => $profile->getName(),
            ];
        }

        return $profiles;
    }
}
