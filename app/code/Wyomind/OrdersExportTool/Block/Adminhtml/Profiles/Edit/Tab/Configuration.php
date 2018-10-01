<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Block\Adminhtml\Profiles\Edit\Tab;

/**
 * Prepare the configuration tab
 */
class Configuration extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected $_systemStore;
    protected $_attributeFactory = null;
    protected $_attributeTypeFactory = null;
    protected $_coreDate = null;
    protected $_orderConfig = null;
    protected $_helper = null;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context     $context
     * @param \Magento\Framework\Registry                 $registry
     * @param \Magento\Framework\Data\FormFactory         $formFactory
     * @param \Magento\Store\Model\System\Store           $systemStore
     * @param \Magento\Sales\Model\Order\Config           $orderConfig
     * @param \Magento\Eav\Model\Entity\AttributeFactory  $attributeFactory
     * @param \Magento\Eav\Model\Entity\TypeFactory       $attributeTypeFactory
     * @param \Wyomind\OrdersExportTool\Helper\Data       $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param array                                       $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\Entity\TypeFactory $attributeTypeFactory,
        \Wyomind\OrdersExportTool\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_systemStore = $systemStore;
        $this->_attributeFactory = $attributeFactory;
        $this->_attributeTypeFactory = $attributeTypeFactory;
        $this->_coreDate = $coreDate;
        $this->_orderConfig = $orderConfig;
        $this->_helper = $helper;
    }

    //end __construct()

    /**
     * Prepare form
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('profile');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('');
        $fieldset = $form->addFieldset('ordersexporttool_profiles_edit_base', ['legend' => __('Configuration')]);

        if ($model->getId()) {
            $fieldset->addField('id', "hidden", ['name' => 'id', 'label' => 'id', "class" => "debug"]);
        }

        // ===================== action flags ==================================
        // save and generate flag
        $fieldset->addField('generate_i', "hidden", ['name' => 'generate_i', 'value' => '', "label" => 'generate_i', "class" => "debug"]);
        // save and continue flag
        $fieldset->addField('back_i', "hidden", ['name' => 'back_i', 'value' => '', "label" => 'back_i', "class" => "debug"]);


        // ===================== required hidden fields ========================

        $fieldset->addField(
            'attributes',
            "hidden",
            [
                'name' => 'attributes',
                'label' => 'attributes',
                'index' => 'attributes',
                "class" => "debug"
            ]
        );

        // ===================== required visible fields =======================
        // point d'interrogation sur le champs store view
        // $renderer = $this->getLayout()->createBlock('Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element');
        // $field->setRenderer($renderer);
        $fieldset->addField(
            'name',
            'text',
            [
                'label' => __('File name'),
                'class' => 'required-entry validate-code',
                'required' => true,
                'name' => 'name',
                'id' => 'name'
            ]
        );

        $fieldset->addField(
            'encoding',
            'select',
            [
                'label' => __('Encoding type'),
                'required' => true,
                'class' => 'required-entry',
                'name' => 'encoding',
                'id' => 'encoding',
                'values' => [
                    [
                        'value' => 'UTF-8',
                        'label' => 'UTF-8'
                    ],
                    [
                        'value' => 'Windows-1252',
                        'label' => 'Windows-1252 (ANSI)'
                    ]
                ]
            ]
        );

        $fieldset->addField(
            'type',
            'select',
            [
                'label' => __('File type'),
                'required' => true,
                'class' => 'required-entry',
                'name' => 'type',
                'id' => 'type',
                'values' => [
                    [
                        'value' => 1,
                        'label' => 'xml'
                    ],
                    [
                        'value' => 2,
                        'label' => 'txt'
                    ],
                    [
                        'value' => 3,
                        'label' => 'csv'
                    ],
                    [
                        'value' => 4,
                        'label' => 'tsv'
                    ],
                    [
                        'value' => 5,
                        'label' => 'din'
                    ]
                ]
            ]
        );

        ($model->getName()) ? $fn = $model->getName() : $fn = "filename";
        switch ($model->getType()) {
            case 1:
                $ext = ".xml";
                break;
            case 2:
                $ext = ".txt";
                break;
            case 3:
                $ext = ".csv";
                break;
            case 4:
                $ext = ".tsv";
                break;
            case 5:
                $ext = ".din";
                break;
            default:
                $ext = ".ext";
        }

        $fieldset->addField(
            'date_format',
            'select',
            [
                'label' => __('File name format '),
                'name' => 'date_format',
                'values' => [
                    [
                        'value' => '{f}',
                        'label' => __($fn) . $ext
                    ],
                    [
                        'value' => 'Y-m-d-{f}',
                        'label' => __($this->_coreDate->date('Y-m-d') . '-' . $fn . $ext)
                    ],
                    [
                        'value' => 'Y-m-d-H-i-s-{f}',
                        'label' => __($this->_coreDate->date('Y-m-d-H-i-s') . '-' . $fn . $ext)
                    ],
                    [
                        'value' => '{f}-Y-m-d',
                        'label' => __($fn . '-' . $this->_coreDate->date('Y-m-d') . $ext)
                    ],
                    [
                        'value' => '{f}-Y-m-d-H-i-s',
                        'label' => __($fn . '-' . $this->_coreDate->date('Y-m-d-H-i-s') . $ext)
                    ],
                    [
                        'value' => 'Y-m-d H-i-s',
                        'label' => __($this->_coreDate->date('Y-m-d H-i-s') . $ext)
                    ]
                ]
            ]
        );

        $fieldset->addField(
            'repeat_for_each',
            'select',
            [
                'label' => __('Create one file for each order'),
                'name' => 'repeat_for_each',
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
            'repeat_for_each_increment',
            'select',
            [
                'label' => __('File name suffix '),
                'required' => true,
                'class' => 'required-entry',
                'name' => 'repeat_for_each_increment',
                'values' => [
                    [
                        'value' => 1,
                        'label' => 'order #'
                    ],
                    [
                        'value' => 2,
                        'label' => 'Magento internal order ID'
                    ],
                    [
                        'value' => 3,
                        'label' => 'Module internal auto-increment'
                    ]
                ]
            ]
        );

        $fieldset->addField(
            'incremental_column',
            'select',
            [
                'label' => __('Add a counter as the 1st column'),
                'name' => 'incremental_column',
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
            'incremental_column_name',
            'text',
            [
                'label' => __('Increment column header'),
                'name' => 'incremental_column_name',
                'class' => ''
            ]
        );

        $fieldset = $form->addFieldset('ordersexporttool_profiles_edit_product_types', ['legend' => __('Products to export')]);

        $fieldset->addField(
            'product_type',
            'checkboxes',
            [
                'label' => 'Product types ',
                'name' => 'product_type[]',
                'values' => [
                    [
                        'value' => 'simple',
                        'label' => 'Simple, Virtual, Downloadable products'
                    ],
                    [
                        'value' => 'configurable',
                        'label' => 'Configurable products'
                    ],
                    [
                        'value' => 'grouped_parent',
                        'label' => 'Grouped products'
                    ],
                    [
                        'value' => 'bundle_parent',
                        'label' => 'Bundle products (main product)'
                    ],
                    [
                        'value' => 'bundle_children',
                        'label' => '<span style="color: #666666;font-style: italic;">Children of bundle products</span>'
                    ]
                ],
                'onchange' => "",
                'disabled' => false
            ]
        );

        $fieldset = $form->addFieldset('ordersexporttool_profiles_edit_settings', ['legend' => __('Orders to export')]);

        $fieldset->addField(
            'store_id',
            'multiselect',
            [
                'label' => __('Export from Store View'),
                'title' => __('Export from Store View'),
                'name' => 'store_id',
                'class' => 'required-entry',
                'required' => true,
                'values' => $this->_systemStore->getStoreValuesForForm(false, false)
            ]
        );

        $fieldset->addField(
            'last_exported_id',
            'text',
            [
                'label' => __('Start with order #'),
                'name' => 'last_exported_id'
            ]
        );

        $fieldset->addField(
            'automatically_update_last_order_id',
            'select',
            [
                'label' => __('Register the last exported order #'),
                'name' => 'automatically_update_last_order_id',
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
            'flag',
            'select',
            [
                'label' => __('Mark each exported order'),
                'name' => 'flag',
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
            'single_export',
            'select',
            [
                'label' => __('Export only unmarked orders '),
                'name' => 'single_export',
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
            'update_status',
            'select',
            [
                'label' => __('Update the order status'),
                'name' => 'update_status',
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

        foreach ($this->_orderConfig->getStates() as $key => $state) {
            $options = [];
            foreach ($this->_orderConfig->getStateStatuses($key) as $k => $s) {
                $options[] = [
                    "value" => $key . "-" . $k,
                    "label" => $s,
                ];
            }

            $values[] = [
                'value' => $options,
                'label' => $state,
            ];
        }

        $fieldset->addField(
            'update_status_to',
            'select',
            [
                'label' => __('New order status'),
                'name' => 'update_status_to',
                'values' => $values
            ]
        );
        $fieldset->addField(
            'update_status_message',
            'text',
            [
                'label' => __('Message in the order history'),
                'name' => 'update_status_message',
                'class' => ''
            ]
        );

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
            ->addFieldMap('flag', 'flag')
                        ->addFieldMap('single_export', 'single_export')
                        ->addFieldDependence('single_export', 'flag', 1)
                        ->addFieldMap('repeat_for_each', 'repeat_for_each')
                        ->addFieldMap('repeat_for_each_increment', 'repeat_for_each_increment')
                        ->addFieldMap('incremental_column', 'incremental_column')
                        ->addFieldMap('incremental_column_name', 'incremental_column_name')
                        ->addFieldDependence('repeat_for_each_increment', 'repeat_for_each', 1)
                        ->addFieldMap('type', 'type')
                        ->addFieldDependence('incremental_column_name', 'incremental_column', 1)
                        ->addFieldMap('update_status', 'update_status')
                        ->addFieldMap('update_status_to', 'update_status_to')
                        ->addFieldMap('update_status_message', 'update_status_message')
                        ->addFieldDependence('update_status_to', 'update_status', 1)
                        ->addFieldDependence('update_status_message', 'update_status', 1)
        );

        $model->setProductType(explode(',', $model->getProductType()));
        $model->setLibraryUrl($this->getUrl('*/*/library'));
        $model->setLibrarySampleUrl($this->getUrl('*/*/librarysample'));
        $model->setSampleUrl($this->getUrl('*/*/sample'));

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
        return __('Configuration');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Configuration');
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
