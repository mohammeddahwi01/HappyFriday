<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Controller\Adminhtml\Profiles;

/**
 * Sample action
 */
class Sample extends \Wyomind\OrdersExportTool\Controller\Adminhtml\Profiles\AbstractProfiles
{

    /**
     * Execute action
     */
    public function execute()
    {
        $request = $this->getRequest();
        $id = $request->getParam('id');

        $model = clone $this->_profilesModel;
        $model->limit = $this->_coreHelper->getStoreConfig('ordersexporttool/system/preview');

        if ($model->load($id)) {
            try {
                $content = $model->generate($request, true);
                $data = ["data" => $content];
            } catch (\Exception $e) {
                $data = __("Unable to generate the profile\n") . $e->getMessage();
                $data = nl2br($data);
            }
            $this->getResponse()->representJson($this->_objectManager->create('Magento\Framework\Json\Helper\Data')->jsonEncode($data));
        }
    }
}
