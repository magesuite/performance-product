<?php

namespace MageSuite\PerformanceProduct\Plugin\Catalog\Model\ResourceModel\Product\Collection;

class PreloadChildren
{

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionProvider
     */
    protected $optionProvider;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionProvider $optionProvider
    )
    {
        $this->resource = $resource;
        $this->optionProvider = $optionProvider;
    }

    public function afterGetItems(\Magento\Catalog\Model\ResourceModel\Product\Collection $subject, $result)
    {
        if ($subject->hasFlag('children_ids_preloaded')) {
            return $result;
        }

        $subject->setFlag('children_ids_preloaded', true);

        $productIds = [];
        foreach ($result as $item) {
            $productIds[] = $item->getEntityId();
        }

        $childrenIds = $this->getChildProductIds($productIds);
        if (empty($childrenIds)) {
            return $result;
        }

        foreach ($result as $item) {
            $productId = $item->getEntityId();
            if (array_key_exists($productId, $childrenIds)) {
                $item->setChildrenProductIds($childrenIds[$productId]);
            }
        }

        return $result;
    }

    protected function getChildProductIds($productIds)
    {
        $connection = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $configurableRelationsTableName = $connection->getTableName('catalog_product_super_link');
        $productTable = $connection->getTableName('catalog_product_entity');

        $select = $connection->select()->from(
            ['l' => $configurableRelationsTableName],
            ['product_id', 'parent_id']
        )->join(
            ['p' => $productTable],
            'p.' . $this->optionProvider->getProductEntityLinkField() . ' = l.parent_id',
            ['entity_id']
        )->join(
            ['e' => $productTable],
            'e.entity_id = l.product_id AND e.required_options = 0',
            []
        )->where(
            'p.entity_id IN (?)',
            $productIds
        );
        $data = $connection->fetchAll($select);

        if (empty($data) || !is_array($data)) {
            return [];
        }

        $childrenIds = [];
        foreach ($data as $key => $value) {
            $parentId = $value['entity_id'];
            $productId = $value['product_id'];
            $childrenIds[$parentId][] = $productId;
        }

        return $childrenIds;
    }

}