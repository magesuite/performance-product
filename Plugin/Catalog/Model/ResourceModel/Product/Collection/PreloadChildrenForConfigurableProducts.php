<?php

namespace MageSuite\PerformanceProduct\Plugin\Catalog\Model\ResourceModel\Product\Collection;

class PreloadChildrenForConfigurableProducts
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
    ) {
        $this->resource = $resource;
        $this->optionProvider = $optionProvider;
    }

    public function afterGetItems(\Magento\Catalog\Model\ResourceModel\Product\Collection $subject, array $result)
    {
        if ($subject->hasFlag('parent_and_children_ids_preloaded')) {
            return $result;
        }

        $subject->setFlag('parent_and_children_ids_preloaded', true);

        $configurableProductIds = [];
        $simpleProductIds = [];

        foreach ($subject->getItems() as $item) {
            if ($item->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                $configurableProductIds[] = $item->getId();
            } elseif ($item->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
                $simpleProductIds[] = $item->getId();
            }
        }

        $parentAndChildProductIds = $this->getParentAndChildProductIds($configurableProductIds, $simpleProductIds);

        foreach ($result as $item) {
            $productId = $item->getEntityId();

            if (array_key_exists($productId, $parentAndChildProductIds['children_ids'])) {
                $item->setChildrenProductIds($parentAndChildProductIds['children_ids'][$productId]);
            }

            if (array_key_exists($productId, $parentAndChildProductIds['parent_ids'])) {
                $item->setParentProductIds($parentAndChildProductIds['parent_ids'][$productId]);
            }
        }

        return $result;
    }

    protected function getParentAndChildProductIds(array $configurableProductIds, array $simpleProductIds): array
    {
        $parentAndChildProductIds = [
            'children_ids' => [],
            'parent_ids' => []
        ];

        if (empty($configurableProductIds) && empty($simpleProductIds)) {
            return $parentAndChildProductIds;
        }

        $connection = $this->resource->getConnection();

        $select = $connection
            ->select()
            ->from(['cpsl' => $connection->getTableName('catalog_product_super_link')], ['product_id', 'parent_id'])
            ->where('cpsl.parent_id IN (?)', $configurableProductIds)
            ->orWhere('cpsl.product_id IN (?)', $simpleProductIds);

        $data = $connection->fetchAll($select);

        if (empty($data)) {
            return $parentAndChildProductIds;
        }

        $childrenIds = [];
        $parentIds = [];

        foreach ($data as $value) {
            $parentId = $value['parent_id'];
            $productId = $value['product_id'];

            if (in_array($parentId, $configurableProductIds)) {
                $childrenIds[$parentId][] = $productId;
            }

            if (in_array($productId, $simpleProductIds)) {
                $parentIds[$productId][] = $parentId;
            }
        }

        $parentAndChildProductIds['children_ids'] = $childrenIds;
        $parentAndChildProductIds['parent_ids'] = $parentIds;

        return $parentAndChildProductIds;
    }
}
