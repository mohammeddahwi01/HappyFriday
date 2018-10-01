<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Block\Adminhtml\Profiles\Edit\Tab;

/**
 *  Template tab
 */
class Template extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        // @var $model \Excercise\Weblog\Model\Blogpost
        $model = $this->_coreRegistry->registry('profile');

        /*
         *
         * @var \Magento\Data\Form $form
         */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('');

        $fieldset = $form->addFieldset('ordersexporttool_form', ['legend' => __('File Template')]);

        $fieldset->addField(
            'include_header',
            'select',
            [
                'label' => __('Include header'),
                'required' => true,
                'class' => 'required-entry txt-type',
                'name' => 'include_header',
                'id' => 'include_header',
                'values' => [
                    [
                        'value' => 0,
                        'label' => __('no')
                    ],
                    [
                        'value' => 1,
                        'label' => __('yes')
                    ]
                ]
            ]
        );

        $fieldset->addField(
            'separator',
            'select',
            [
                'label' => __('Fields delimiter'),
                'class' => 'txt-type required-entry',
                'id' => 'separator',
                'required' => true,
                'name' => 'separator',
                'style' => '',
                'values' => [
                    [
                        'value' => ';',
                        'label' => ';'
                    ],
                    [
                        'value' => ',',
                        'label' => ','
                    ],
                    [
                        'value' => '|',
                        'label' => '|'
                    ],
                    [
                        'value' => '\t',
                        'label' => 'tab'
                    ],
                    [
                        'value' => '[|]',
                        'label' => '[|]'
                    ]
                ]
            ]
        );
        $fieldset->addField(
            'protector',
            'select',
            [
                'label' => __('Fields enclosure'),
                'class' => 'txt-type not-required',
                'maxlength' => 1,
                'name' => 'protector',
                'values' => [
                    [
                        'value' => '"',
                        'label' => '"'
                    ],
                    [
                        'value' => "'",
                        'label' => "'"
                    ],
                    [
                        'value' => "",
                        'label' => __('none')
                    ]
                ]
            ]
        );
        $fieldset->addField(
            'escaper',
            'select',
            [
                'label' => __('Delimiter Escaper'),
                'class' => 'txt-type not-required',
                'maxlength' => 1,
                'name' => 'escaper',
                'values' => [
                    [
                        'value' => '\\',
                        'label' => '\\ (backslashe)'
                    ],
                    [
                        'value' => "\"",
                        'label' => "\" (quotation mark)"
                    ],
                    [
                        'value' => "",
                        'label' => __('none')
                    ]
                ]
            ]
        );

        $fieldset->addField(
            'enclose_data',
            'select',
            [
            'label' => __('Enclose xml tag content inside CDATA (recommended)'),
            'required' => true,
            'class' => 'required-entry xml-type',
            'name' => 'enclose_data',
            'id' => 'enclose_data',
            'values' => [
                [
                    'value' => 1,
                    'label' => __('yes')
                ],
                [
                    'value' => 0,
                    'label' => __('no')
                ]
            ]
                ]
        );

        $fieldset->addField(
            'extra_header',
            'textarea',
            [
            'label' => __('Extra header'),
            'class' => 'txt-type not-required',
            'name' => 'extra_header',
            'style' => 'height:60px;width:500px'
                ]
        );

        $fieldset->addField(
            'header',
            'textarea',
            [
            'label' => __('Header'),
            'class' => '',
            'name' => 'header',
            'required' => true,
            'style' => 'height:120px;width:500px'
                ]
        );

        $fieldset->addField(
            'body',
            'textarea',
            [
            'label' => __('Body'),
            'class' => '',
            'required' => true,
            'name' => 'body',
            'style' => 'height:300px;width:500px'
                ]
        );

        $fieldset->addField(
            'footer',
            'textarea',
            [
            'label' => __('Footer'),
            'class' => 'xml-type',
            'required' => true,
            'id' => 'footer',
            'name' => 'footer',
            'style' => 'height:60px;width:500px'
                ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Template');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Template');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
