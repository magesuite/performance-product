<?php

namespace MageSuite\PerformanceProduct\Model\ResourceModel\Category;

class CategoryProduct
{
    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;

    public function __construct(\Magento\Framework\App\ResourceConnection $resource)
    {
        $this->connection = $resource->getConnection();
    }

    public function getCategoriesByProductIds(array $productIds): array
    {
        $select = $this->connection->select();
        $select
            ->from(
                $this->connection->getTableName('catalog_category_product'),
                ['product_id', 'category_id']
            )
            ->where('product_id IN (?)', $productIds);

        return $this->connection->fetchAll($select);
    }
}
