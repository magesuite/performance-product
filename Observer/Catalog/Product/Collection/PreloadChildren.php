<?php

namespace MageSuite\PerformanceProduct\Observer\Catalog\Product\Collection;

class PreloadChildren implements \Magento\Framework\Event\ObserverInterface
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

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $observer->getCollection();
        $productIds = $collection->getAllIds();
        $childrenIds = $this->getChildProductIds($productIds);

        if (empty($childrenIds)) {
            return;
        }

        foreach ($collection->getItems() as $item) {
            $configurableId = $item->getEntityId();
            if (array_key_exists($configurableId, $childrenIds)) {
                $item->setChildrenProductIds($childrenIds[$configurableId]);
            }
        }

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