<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Block\Adminhtml\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\ModuleListInterface;

class Information extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var \Amasty\Base\Helper\CssChecker
     */
    private $cssChecker;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var string
     */
    private $userGuide = 'https://amasty.com/docs/doku.php?id=magento_2:improved_layered_navigation';

    /**
     * @var array
     */
    private $enemyExtensions = [];

    /**
     * @var string
     */
    private $content;


    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Amasty\Base\Helper\CssChecker $cssChecker,
        ModuleListInterface $moduleList,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->cssChecker = $cssChecker;
        $this->moduleList = $moduleList;
    }

    /**
     * Render fieldset html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);

        $this->setContent(__('Please update Amasty Base module. Re-upload it and replace all the files.'));

        $this->_eventManager->dispatch(
            'amasty_base_add_information_content',
            ['block' => $this]
        );

        $html .= $this->getContent();
        $html .= $this->_getChildrenElementsHtml($element);
        $html .= $this->_getFooterHtml($element);

        $html = str_replace(
            'amasty_information]" type="hidden" value="0"',
            'amasty_information]" type="hidden" value="1"',
            $html
        );
        $html = preg_replace('(onclick=\"Fieldset.toggleCollapse.*?\")', '', $html);

        return $html;
    }

    public function getAdditionalModuleContent()
    {
        $result = '';
        $corruptedWebsites = $this->cssChecker->getCorruptedWebsites();
        $enebleCss =  $this->_scopeConfig->getValue('amshopby/amasty_information/css_include', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($corruptedWebsites && !$enebleCss) {
            $message = [
                'type' => 'message-error',
                'text' =>
                    __(
                        'The default Magento LESS functionality is missing for the following website(s): %1. '
                        . 'Try to run ssh command "php bin/magento setup:static-content:deploy". '
                        . 'To add CSS please set "Include CSS" setting to "Yes".',
                        implode(', ', $corruptedWebsites)
                    )
            ];

            if ($this->getBaseVersion() < '1.3.4') {
                return $message['text'];
            }

            $result = [];
            $result[] = $message;
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getUserGuide()
    {
        return $this->userGuide;
    }

    /**
     * @param string $userGuide
     */
    public function setUserGuide($userGuide)
    {
        $this->userGuide = $userGuide;
    }

    /**
     * @return array
     */
    public function getEnemyExtensions()
    {
        return $this->enemyExtensions;
    }

    /**
     * @param array $enemyExtensions
     */
    public function setEnemyExtensions($enemyExtensions)
    {
        $this->enemyExtensions = $enemyExtensions;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    private function getBaseVersion()
    {
        $version = '';
        if (isset($this->moduleList->getOne('Amasty_Base')['setup_version'])) {
            $version = $this->moduleList->getOne('Amasty_Base')['setup_version'];
        }

        return $version;
    }
}
