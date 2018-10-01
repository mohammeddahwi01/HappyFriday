<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderEraser\Controller\Adminhtml\Orders;

class Delete extends \Wyomind\OrderEraser\Controller\Adminhtml\Orders
{

    public function execute()
    {

        $path = "sales/order/index";

        $params = $this->getRequest()->getParams();

        $ids = array();

        if (isset($params['excluded']) && $params['excluded'] == "false") {
            $ids[] = '*';
        } else {
            if (isset($params['selected'])) {
                if (is_array($params['selected'])) {
                    $ids = $params['selected'];
                } else {
                    $ids = array($params['selected']);
                }
            }
        }

        $countDeleteOrder = 0;
        if (count($ids) > 0) {
            $collection = $this->ordersCollectionFactory->create();
            if ($ids[0] == '*') {
                $countDeleteOrder = $collection->deleteAll();
                $this->messageManager->addSuccess($countDeleteOrder . __('All orders have been successfully deleted'));
            } else {
                foreach ($ids as $id) {
                    $collection->deleteOrder($id);
                    $countDeleteOrder++;
                }
                $this->messageManager->addSuccess($countDeleteOrder . __(' order(s) successfully deleted'));
            }
        } else {
            $this->messageManager->addError(__('Unable to delete orders.'));
        }


        return $this->resultRedirectFactory->create()->setPath($path, array());
    }
}
