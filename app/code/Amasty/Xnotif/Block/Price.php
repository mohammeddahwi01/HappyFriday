<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Block;

class Price extends AbstractBlock
{

    public function _construct()
    {
        $this->setAlertType("price");
        parent::_construct();
    }
}
