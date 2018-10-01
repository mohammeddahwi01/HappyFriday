<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Controller\Adminhtml\Profiles;

/**
 * Generate action
 */
class Generate extends \Wyomind\OrdersExportTool\Controller\Adminhtml\Profiles\AbstractProfiles
{

    /**
     * Execute action
     */
    public function execute()
    {
        $request = $this->getRequest();
        $id = $request->getParam('id');
        $model = $this->_objectManager->create($this->model);

        $model->limit = 0;
        if ($model->load($id)) {
            try {
                $model->generate($request);
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Unable to generate the export file.') . '<br/><br/>' . nl2br($e->getMessage()));
            }
        } else {
            $this->messageManager->addError(__('Unable to find a profile to generate.'));
        }

        if ($request->getParam('generate_i')) {
            $resultForward = $this->_resultForwardFactory->create();
            $resultForward->setParams(['id' => $id]);
            $return = $resultForward->forward("edit");
        } else {
            $return = $this->_resultForwardFactory->create()->forward("index");
        }

        return $return;
    }
}
