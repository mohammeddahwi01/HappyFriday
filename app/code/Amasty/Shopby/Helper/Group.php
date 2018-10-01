<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Helper;

use Magento\Framework\App\Helper\Context;

class Group extends \Magento\Framework\App\Helper\AbstractHelper
{
    const LAST_POSSIBLE_OPTION_ID = (2 << 31) - 1;

    /**
     * @var \Amasty\Shopby\Model\ResourceModel\GroupAttr\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    public function __construct(
        Context $context,
        \Amasty\Shopby\Model\ResourceModel\GroupAttr\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Base\Model\Serializer $serializer
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    /**
     * @param $id
     * @return array
     */
    public function getGroupsWithOptions($id = null)
    {
        $collection = $this->getGroupCollection($id)->joinOptions();

        return $this->scopeData($collection, 'options', 'option_id');
    }

    /**
     * @param $id
     * @return array
     */
    public function getAttributeGroupsValues($id)
    {
        $collection = $this->getGroupCollection($id)->joinValues();

        return $this->scopeData($collection, 'values', 'value');
    }

    /**
     * @param $attributeId
     * @return \Amasty\Shopby\Model\ResourceModel\GroupAttr\Collection
     */
    public function getGroupCollection($attributeId = null)
    {
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('enabled', 1)
            ->addOrder('position', \Magento\Framework\Data\Collection\AbstractDb::SORT_ORDER_ASC);
        if ($attributeId) {
            $collection->addFieldToFilter('attribute_id', $attributeId);
        }

        return $collection;
    }

    public function getGroupOptions($attrId)
    {
        $collection = $this->getGroupCollection($attrId)
            ->joinOptions();

        return $this->getOptionsId($collection);
    }

    public function getOptionsId($collection)
    {
        $ids = [];
        foreach ($collection->getData() as $data) {
            if (!isset($ids[$data['group_code']])) {
                $ids[$data['group_code']] = $data['option_id'];
            }
        }

        return $ids;
    }

    /**
     * @param $collection
     * @param $config
     * @param $field
     * @return array
     */
    protected function scopeData($collection, $config, $field)
    {
        $options = [];
        if ($collection->getSize()) {
            foreach ($collection->getData() as $data) {
                if (!isset($options[$data['group_id']])) {
                    $options[$data['group_id']] = [
                        'code' => $data['group_code'],
                        'label' => $data['name'],
                        $config => []
                    ];
                }
                $options[$data['group_id']][$config][] = $data[$field];
            }
        }

        return $options;
    }

    /**
     * @param $options
     * @return array
     */
    public function getMinMax($options)
    {
        $groups = [];
        foreach ($options as $option) {
            list($min, $max) = $this->searchMinMax($option);
            $groups[$option['code']] = ['min' => $min, 'max' => $max];
        }

        return $groups;
    }

    /**
     * @param $id
     * @return array
     */
    public function getRanges($id)
    {
        $groups = [];
        $options = $this->getAttributeGroupsValues($id);
        if ($options) {
            foreach ($options as $option) {
                list($min, $max) = $this->searchMinMax($option);
                $groups[$min . "-" . $max] = $option['label'];
            }
        }

        return $groups;
    }

    /**
     * @param $option
     * @return array
     */
    public function searchMinMax($option)
    {
        $min = $option['values'][0];
        $max = $option['values'][1];
        if ($option['values'][1] < $min) {
            $min = $option['values'][1];
            $max = $option['values'][0];
        }

        return [$min, $max];
    }

    /**
     * @param $options
     * @param $value
     * @return array
     */
    public function getGroup($options, $value)
    {
        foreach ($options as $group) {
            if ($group['code'] == $value) {
                return $group['options'];
            }
        }

        return [];
    }

    /**
     * @param $id
     * @param $value
     * @return null
     */
    public function getGroupLabel($id, $value)
    {
        $collection = $this->getGroupCollection($id)->addFieldToFilter('group_code', $value);
        if ($collection->getSize()) {
            $item = $collection->getFirstItem();
            return $item->getName();
        }

        return null;
    }

    /**
     * @param $id
     * @return array
     */
    public function getAliasGroup($id)
    {
        $data = [];
        $collection = $this->getGroupCollection($id);
        if ($collection->getSize()) {
            foreach ($collection as $item) {
                $url = $item->getUrl();
                if (!$url) {
                    $url = $item->getGroupCode();
                }
                 $data[$item->getGroupCode()] = $url;
            }
        }

        return $data;
    }

    /**
     * @param $label
     * @param int|null $storeId
     * @return string
     */
    public function chooseGroupLabel($label, $storeId = null)
    {
        $storeId = $storeId ?: $this->storeManager->getStore()->getId();
        if (preg_match('/^\{\s*\".*\}$/', $label) || preg_match('^([adObis]:|N;)^', $label)) {
            $labels = $this->serializer->unserialize($label);
            if (isset($labels[$storeId])) {
                $label = $labels[$storeId] ?: $this->chooseDefaultLabel($labels);
            } else {
                $label = $this->chooseDefaultLabel($labels);
            }
        }

        return $label;
    }

    /**
     * @param $labels
     * @return string
     */
    private function chooseDefaultLabel($labels)
    {
        return isset($labels[$this->storeManager->getDefaultStoreView()->getId()])
            ? $labels[$this->storeManager->getDefaultStoreView()->getId()]
            : array_shift($labels);
    }
}
