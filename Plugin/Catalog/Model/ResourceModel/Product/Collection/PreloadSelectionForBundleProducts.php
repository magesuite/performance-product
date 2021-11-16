<?php

namespace MageSuite\PerformanceProduct\Plugin\Catalog\Model\ResourceModel\Product\Collection;

class PreloadSelectionForBundleProducts
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    public function afterGetItems(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $subject,
        $result
    ) {
        if ($subject->hasFlag('selection_ids_preloaded')) {
            return $result;
        }

        $subject->setFlag('selection_ids_preloaded', true);
        $productIds = [];

        foreach ($subject->getItems() as $item) {
            if ($item->getTypeId() != \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
                continue;
            }

            $productIds[] = $item->getId();
        }

        if (empty($productIds)) {
            return $result;
        }

        $selectionProductIds = $this->getSelectionProductIds($productIds);

        if (empty($selectionProductIds)) {
            return $result;
        }

        foreach ($result as $item) {
            $productId = $item->getId();

            if (array_key_exists($productId, $selectionProductIds)) {
                $item->setSelectionProductIds($selectionProductIds[$productId]);
            }
        }

        return $result;
    }

    protected function getSelectionProductIds(array $productIds)
    {
        $linkField = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)->getLinkField();
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(
            ['tbl_selection' => $connection->getTableName('catalog_product_bundle_selection')],
            ['product_id', 'parent_product_id']
        )->join(
            ['e' => $connection->getTableName('catalog_product_entity')],
            'e.entity_id = tbl_selection.product_id AND e.required_options=0',
            []
        )->join(
            ['parent' => $connection->getTableName('catalog_product_entity')],
            'tbl_selection.parent_product_id = parent.' . $linkField
        )->join(
            ['tbl_option' => $connection->getTableName('catalog_product_bundle_option')],
            'tbl_option.option_id = tbl_selection.option_id AND tbl_option.required=1',
            []
        )->where('parent.entity_id IN (?)', $productIds);
        $data = (array)$connection->fetchAll($select);

        if (empty($data)) {
            return [];
        }

        $selectionProductIds = [];

        foreach ($data as $item) {
            $selectionProductIds[$item['parent_product_id']][] = $item['product_id'];
        }

        return $selectionProductIds;
    }
}
