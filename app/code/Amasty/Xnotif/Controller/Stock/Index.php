<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Controller\Stock;

class Index extends \Amasty\Xnotif\Controller\AbstractIndex
{
    const TYPE = "stock";

    public function getTitle()
    {
        return __("My Out of Stock Subscriptions");
    }
}
