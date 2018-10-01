<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Controller\Adminhtml\Variables;

/**
 * Delete action (grid)
 */
class Delete extends \Wyomind\OrdersExportTool\Controller\Adminhtml\Variables\AbstractVariables
{

    /**
     * Execute action
     */
    public function execute()
    {
        return parent::delete();
    }
}
