<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Plugins\Backend\Model;

use Magento\Backend\Model\Url as BackendUrlModel;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Registry;

class Url
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(Http $request, Registry $registry)
    {

        $this->request = $request;
        $this->registry = $registry;
    }

    /**
     * @param BackendUrlModel $subject
     * @param string $areaFrontName
     * @return string
     */
    public function afterGetAreaFrontName(BackendUrlModel $subject, $areaFrontName)
    {
        if ($this->registry->registry('xnotif_test_notification')) {
            $areaFrontName = '';
        }

        return $areaFrontName;
    }
}
