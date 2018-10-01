<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 *
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {

       
        if (version_compare($context->getVersion(), '5.2.0') < 0) {
            $installer = $setup;
            $installer->startSetup();

            $installer->getConnection()->addColumn(
                $installer->getTable('ordersexporttool_profiles'),
                'escaper',
                "VARCHAR(2) NOT NULL DEFAULT '\\\\' COMMENT 'Delimiter escaper' AFTER `separator`"
            );

            $installer->endSetup();
        }
    }
}
