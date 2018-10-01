<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Block\Adminhtml\Profiles\Renderer;

/**
 * Render the type of profile (wml, csv, txt...)
 */
class Type extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render the column block
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $types = [
            'none',
            'xml',
            'txt',
            'csv',
            'tsv',
            'din',
        ];
        return $types[$row->getType()];
    }
}
