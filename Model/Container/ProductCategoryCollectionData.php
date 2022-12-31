<?php

declare(strict_types=1);

namespace MageSuite\PerformanceProduct\Model\Container;

class ProductCategoryCollectionData extends \Magento\Framework\DataObject
{
    public const KEY_CATEGORY_ATTRIBUTES = 'category_attributes';

    protected \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory;
    protected \MageSuite\PerformanceProduct\Model\ResourceModel\Category\LoadedCollectionFactory $loadedCollectionFactory;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \MageSuite\PerformanceProduct\Model\ResourceModel\Category\LoadedCollectionFactory $loadedCollectionFactory,
        array $data
    ) {
        parent::__construct($data);

        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->loadedCollectionFactory = $loadedCollectionFactory;
    }

    public function initCollections(array $productIds, array $categoryAttributes = []): void
    {
        $collection = $this->getCategoriesCollection($productIds, $categoryAttributes);

        if (!$collection->count()) {
            return;
        }

        $categories = $collection->getItems();

        $groupedCategories = $this->groupCategoriesByProducts($categories);
        $this->addData($groupedCategories);
    }

    public function getProductCategoriesCollection(int $productId): ?\Magento\Catalog\Model\ResourceModel\Category\Collection
    {
        $categories = $this->_getData($productId);

        if (!$categories) {
            return null;
        }

        $collection = $this->loadedCollectionFactory->create();
        $collection->setItems($categories);

        return $collection;
    }

    protected function getCategoriesCollection(array $productIds, array $categoryAttributes): \Magento\Catalog\Model\ResourceModel\Category\Collection
    {
        $collection = $this->categoryCollectionFactory->create();

        $collection->joinField(
            'product_id',
            'catalog_category_product',
            'product_id',
            'category_id = entity_id'
        );
        $collection->addAttributeToFilter(
            'product_id',
            ['in' => $productIds]
        );
        $collection->getSelect()->columns(new \Zend_Db_Expr('GROUP_CONCAT(product_id) AS grouped_product_id'));

        $categoryAttributes = empty($categoryAttributes) ? $this->_getData(self::KEY_CATEGORY_ATTRIBUTES) : $categoryAttributes;
        $collection->addAttributeToSelect($categoryAttributes);
        $collection->groupByAttribute('entity_id');

        return $collection;
    }

    protected function groupCategoriesByProducts(array $categories): array
    {
        $grouped = [];

        /** @var \Magento\Framework\DataObject $category */
        foreach ($categories as $category) {
            $productIds = explode(',', $category->getData('grouped_product_id'));

            foreach ($productIds as $productId) {
                $grouped[$productId][] = $category;
            }
        }

        return $grouped;
    }
}
