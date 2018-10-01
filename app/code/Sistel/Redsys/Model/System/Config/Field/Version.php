<?php
/**
 * Sistel
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the sistel.com license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Paymen Methods - Redsys
 * @package     Sistel_Redsys
 * @author      Juan Pedro Barba Soler || Developer at Sistel, Servicios Informáticos de Software y Telecomunicaciones
 * @copyright   Copyright (c) 2016 Sistel (http://www.sistel.es/)
 * @license     See root folder of this extension
**/

namespace Sistel\Redsys\Model\System\Config\Field;

class Version extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    public function __construct(
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_moduleList = $moduleList;
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return (string) $this->_moduleList->getOne("Sistel_Redsys")['setup_version'];
    }
}
