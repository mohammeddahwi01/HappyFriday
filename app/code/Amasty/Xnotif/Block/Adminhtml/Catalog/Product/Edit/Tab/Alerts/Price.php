<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Block\Adminhtml\Catalog\Product\Edit\Tab\Alerts;

class Price extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts\Price
{

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn(
            'firstname',
            [
                'header' => __('First Name'),
                'index' => 'firstname',
                'renderer' => 'Amasty\Xnotif\Block\Adminhtml\Catalog\Product\Edit\Tab\Alerts\Renderer\FirstName',
            ]
        );

        $this->addColumn(
            'lastname',
            [
                'header' => __('Last Name'),
                'index' => 'lastname',
                'renderer' => 'Amasty\Xnotif\Block\Adminhtml\Catalog\Product\Edit\Tab\Alerts\Renderer\LastName',
            ]
        );

        $this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'index' => 'email',
                'renderer' => 'Amasty\Xnotif\Block\Adminhtml\Catalog\Product\Edit\Tab\Alerts\Renderer\Email',
            ]
        );

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'index' => 'price',
                'type' => 'currency',
                'currency_code'
                => $this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE
                )
            ]
        );

        $this->addColumn(
            'add_date',
            [
                'header' => __('Date Subscribed'),
                'index' => 'add_date',
                'type' => 'date'
            ]
        );

        $this->addColumn(
            'last_send_date',
            [
                'header' => __('Last Notification'),
                'index' => 'last_send_date',
                'type' => 'date'
            ]
        );

        $this->addColumn(
            'send_count',
            [
                'header' => __('Send Count'),
                'index' => 'send_count',
            ]
        );

        return $this;
    }
}
