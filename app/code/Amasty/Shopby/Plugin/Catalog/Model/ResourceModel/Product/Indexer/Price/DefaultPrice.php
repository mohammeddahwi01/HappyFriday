<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Catalog\Model\ResourceModel\Product\Indexer\Price;

use Magento\Framework\App\ResourceConnection;

class DefaultPrice
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $date;

    /**
     * @var array
     */
    private $entityIds;

    /**
     * @var string
     */
    private $productIdLink;

    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Framework\Stdlib\DateTime $date,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->resource = $resourceConnection;
        $this->date = $date;
        $this->productIdLink = $productMetadata->getEdition() == 'Enterprise' ? 'row_id' : 'entity_id';
    }

    /**
     * @param $subject
     * @param $entityIds
     * @return array
     */
    public function beforeReindexEntity($subject, $entityIds)
    {
        $this->entityIds = $entityIds;
        return [$entityIds];
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterReindexEntity($subject, $result)
    {
        $columns = [
            'entity_id' => 'main_table.entity_id',
            'customer_group_id' => 'main_table.customer_group_id',
            'website_id' => 'main_table.website_id',
            'tax_class_id' => 'main_table.tax_class_id',
            'price' => 'main_table.price',
            'final_price' => new \Zend_Db_Expr('LEAST(main_table.final_price, rule_index.rule_price)'),
            'min_price' => new \Zend_Db_Expr('LEAST(main_table.min_price, rule_index.rule_price)'),
            'max_price' => new \Zend_Db_Expr('LEAST(main_table.max_price, rule_index.rule_price)'),
            'tier_price' => 'main_table.tier_price',
        ];

        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            ['main_table' => $this->getIdxTable()],
            $columns
        );
        $conditions = [
            'rule_index.product_id = main_table.entity_id',
            'rule_index.website_id = main_table.website_id',
            'rule_index.customer_group_id = main_table.customer_group_id'

        ];
        $select->joinInner(
            ['rule_index' => $this->resource->getTableName('catalogrule_product_price')],
            implode(' AND ', $conditions),
            []
        );
        $now = new \DateTime();
        $select->where('rule_index.rule_date = ?', $this->date->formatDate($now, false));
        if ($this->entityIds) {
            $select->where('main_table.entity_id IN (?)', $this->entityIds);
        }

        $connection->insertOnDuplicate(
            $this->getIdxTable(),
            $connection->fetchAll($select),
            ['final_price', 'min_price', 'max_price']
        );

        $this->addSpecialPriceToConfigurable($columns);

        return $result;
    }

    /**
     * @param $columns
     */
    private function addSpecialPriceToConfigurable($columns)
    {
        $columns['final_price'] = 'product_price.value';
        $columns['min_price'] = 'main_table.min_price';
        $columns['max_price'] = 'main_table.max_price';

        $connection = $this->resource->getConnection();

        $select = $connection->select()->from(
            ['main_table' => $this->getIdxTable()],
            $columns
        );

        if ($this->productIdLink == 'row_id') {
            $select->joinInner(
                ['product_entity' => $this->resource->getTableName('catalog_product_entity')],
                'product_entity.entity_id=main_table.entity_id',
                []
            );
            $entityIdLink = 'product_entity.row_id';
        } else {
            $entityIdLink = 'main_table.entity_id';
        }

        $select->joinInner(
            ['simple_link' => $this->resource->getTableName('catalog_product_super_link')],
            'simple_link.parent_id=' . $entityIdLink,
            []
        );

        $select->joinInner(
            ['product_price' => $this->resource->getTableName('catalog_product_entity_decimal')],
            'simple_link.product_id=product_price.' . $this->productIdLink,
            []
        );

        $select->joinInner(
            ['eav' => $this->resource->getTableName('eav_attribute')],
            'eav.attribute_id=product_price.attribute_id AND eav.attribute_code="special_price"',
            []
        );

        if ($this->entityIds) {
            $select->where('main_table.entity_id IN (?)', $this->entityIds);
        }

        $connection->insertOnDuplicate(
            $this->getIdxTable(),
            $connection->fetchAll($select),
            ['final_price', 'min_price', 'max_price']
        );
    }

    /**
     * @return string
     */
    public function getIdxTable()
    {
        return $this->resource->getTableName('catalog_product_index_price_temp');
    }
}
