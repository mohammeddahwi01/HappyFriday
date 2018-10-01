<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Block\Adminhtml\Profiles\Edit\Tab;

/**
 * Filter tab
 */
class Filters extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected $_orderConfig = null;
    protected $_customerGroup = null;
    protected $_resource = null;
    protected $_helper = null;
    protected $_coreHelper = null;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context            $context
     * @param \Magento\Framework\Registry                        $registry
     * @param \Magento\Framework\Data\FormFactory                $formFactory
     * @param \Magento\Sales\Model\Order\Config                  $orderConfig
     * @param \Magento\Framework\App\ResourceConnection          $resource
     * @param \Wyomind\OrdersExportTool\Helper\Data              $helper
     * @param \Wyomind\Core\Helper\Data                          $coreHelper
     * @param \Magento\Customer\Model\Group                      $customerGroup
     * @param array                                              $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Wyomind\OrdersExportTool\Helper\Data $helper,
        \Wyomind\Core\Helper\Data $coreHelper,
        \Magento\Customer\Model\Group $customerGroup,
        array $data = []
    ) {
        $this->_orderConfig = $orderConfig;
        $this->_customerGroup = $customerGroup;
        $this->_resource = $resource;
        $this->_helper = $helper;
        $this->_coreHelper = $coreHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getOrderConfig()
    {
        return $this->_orderConfig;
    }
    
    public function getCustomerGroup()
    {
        return $this->_customerGroup;
    }

    public function getHelper()
    {
        return $this->_helper;
    }
    
    public function getResource()
    {
        return $this->_resource;
    }

    public function getCoreHelper()
    {
        return $this->_coreHelper;
    }
        
    /**
     * Prepare form
     * @return $this
     */
    protected function _prepareForm()
    {
        // @var $model \Excercise\Weblog\Model\Blogpost
        $model = $this->_coreRegistry->registry('profile');

        /*
         * @var \Magento\Data\Form $form
         */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('');

        $form->setValues($model->getData());
        $this->setForm($form);

        $this->setTemplate('profiles/edit/filters.phtml');

        return parent::_prepareForm();
    }

    /**
     * Return tab label
     * @return string
     */
    public function getTabLabel()
    {
        return __('Filters');
    }

    /**
     * Return tab title
     * @return string
     */
    public function getTabTitle()
    {
        return __('Filters');
    }

    /**
     * can show tab
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Is visible
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * get customer groups
     * @return string
     */
    public function getCustomerGroups()
    {
        $model = $this->_coreRegistry->registry('profile');
        return $model->getCustomerGroups();
    }

    /**
     * get order states
     * @return string
     */
    public function getStates()
    {
        $model = $this->_coreRegistry->registry('profile');
        return $model->getStates();
    }
}
