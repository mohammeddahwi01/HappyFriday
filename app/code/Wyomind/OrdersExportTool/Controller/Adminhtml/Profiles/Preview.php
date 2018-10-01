<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Controller\Adminhtml\Profiles;

/**
 * Preview action
 */
class Preview extends \Wyomind\OrdersExportTool\Controller\Adminhtml\Profiles\AbstractProfiles
{

    /**
     * Execute action
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
    }
}
