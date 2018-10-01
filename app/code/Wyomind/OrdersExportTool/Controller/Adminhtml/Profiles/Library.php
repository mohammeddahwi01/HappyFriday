<?php

/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Controller\Adminhtml\Profiles;

/**
 * Load library action
 */
class Library extends \Wyomind\OrdersExportTool\Controller\Adminhtml\Profiles\AbstractProfiles
{

    /**
     * Execute action
     */
    public function execute()
    {
        $contentOutput = "";
        $tabOutput = '<div class="items-library"><ul>';
        $types = $this->helperData->getEntities();
        foreach ($types as $type) {
            $tabOutput .= " <li id='" . $type['code'] . "' class='lib-type-link".($type['code']=="order"?" active":"")."'>" . $type['label'] . "</li>";
        }

        $tabOutput .= '</ul>';

        $x = 0;
        foreach ($types as $type) {
            $attributesList = [];
            $resource = clone $this->_resource;
            $read = $resource->getConnection($this->helperData->getConnection($type['connection']));
            $table = $resource->getTableName($type['table']);

            $sql = "SHOW FULL COLUMNS FROM $table";

            $r = $read->fetchAll($sql);

            foreach ($r as $data) {
                $attributesList[] = [
                    'field' => $data['Field'],
                    'comment' => $data['Comment']
                ];
            }

            $variables = clone $this->_variablesCollection;
            $variables->addFieldToFilter('scope', ['eq' => $type['syntax']]);

            foreach ($variables as $variable) {
                $attributesList[] = [
                    'field' => $variable->getName(),
                    'comment' => __("Custom variable") . ': ' . $variable->getComment()
                ];
            }

            usort($attributesList, ['Wyomind\OrdersExportTool\Helper\Data', 'cmp']);
            $display = (!$x) ? "table" : "none";
            $contentOutput .= "<table class='attr-list " . $type['code'] . "' style='display:" . $display . "'>";
            $contentOutput .= "<thead><tr><th colspan='2'>" . $type['label'] . "</th></tr></thead><tbody>";

            $i = 0;
            foreach ($attributesList as $attribute) {
                if (!empty($attribute['field'])) {
                    $contentOutput .= "<tr class='label  attribute-documentation_" . ($i % 2) . "'><td class='pink load-attr-sample' title='Load sample'  code='" . $type['code'] . "' field='" . $attribute['field'] . "'><span class=' tv closed'></span>{{" . $type['syntax'] . '.' . $attribute['field'] . "}}</td><td>" . $attribute['comment'] . "</td></tr><tr class='attribute-sample'><td colspan='2'></td></tr>";
                    $i++;
                }
            }

            $contentOutput .= "</tbody></table>";
            $x++;
        }

        $this->getResponse()->representJson($this->_objectManager->create('Magento\Framework\Json\Helper\Data')->jsonEncode(['data' => $tabOutput . $contentOutput]));
    }
}
