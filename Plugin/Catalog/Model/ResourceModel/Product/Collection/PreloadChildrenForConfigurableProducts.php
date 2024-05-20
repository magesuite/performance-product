<?php

namespace MageSuite\PerformanceProduct\Plugin\Catalog\Model\ResourceModel\Product\Collection;

class PreloadChildrenForConfigurableProducts
{
    protected \Magento\Framework\App\ResourceConnection $resource;

    protected \Magento\Framework\EntityManager\MetadataPool $metadataPool;

    protected ?string $productEntityLinkField = null;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        $this->resource = $resource;
        $this->metadataPool = $metadataPool;
    }

    public function afterGetItems(\Magento\Catalog\Model\ResourceModel\Product\Collection $subject, array $result)
    {
        if ($subject->hasFlag('parent_and_children_ids_preloaded')) {
            return $result;
        }

        $subject->setFlag('parent_and_children_ids_preloaded', true);

        $configurableProductIds = [];
        $simpleProductIds = [];

        $productEntityLinkField = $this->getProductEntityLinkField();

        foreach ($subject->getItems() as $item) {
            if ($item->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                $configurableProductIds[$item->getData($productEntityLinkField)] = $item->getId();
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
            $parentId = $configurableProductIds[$value['parent_id']] ?? $value['parent_id'];
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

    protected function getProductEntityLinkField(): string
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->metadataPool
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
        }

        return $this->productEntityLinkField;
    }
}
