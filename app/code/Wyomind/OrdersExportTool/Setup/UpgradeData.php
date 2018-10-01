<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 *
 */
class UpgradeData implements UpgradeDataInterface
{

    private $_profileCollection = null;
    private $_state = null;

    public function __construct(
    \Wyomind\OrdersExportTool\Model\ResourceModel\Profiles\CollectionFactory $profileCollectionFactory,
            \Magento\Framework\App\State $state
    )
    {
        $this->_profileCollection = $profileCollectionFactory->create();
        $this->_state = $state;
    }

    public function upgrade(
    ModuleDataSetupInterface $setup,
            ModuleContextInterface $context
    )
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * upgrade to 7.0.0
         */
        if (version_compare($context->getVersion(), '7.0.0') < 0) {
            try {
                $this->_state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
            } catch (\Exception $e) {
                
            }
            foreach ($this->_profileCollection as $profile) {
                $pattern = str_replace(["php="], ["output="], $profile->getBody());
                $profile->setBody($pattern);
                $profile->save();
            }
        }
        $installer->endSetup();
    }

}
