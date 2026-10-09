<?php

declare(strict_types=1);

namespace MageSuite\PerformanceProduct\Model\Category;

class AddCategoriesCollectionToProductCollection
{
    protected ?\ReflectionMethod $setIsLoadedMethod = null;
    protected ?\ReflectionMethod $setCategoryCollectionMethod = null;
    protected ?\ReflectionProperty $productIdCachedProperty = null;

    public function __construct(
        protected \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        protected \MageSuite\PerformanceProduct\Model\ResourceModel\Category\CategoryProduct $categoryProduct,
        protected \Magento\Framework\DataObject\Factory $dataObjectFactory
    ) {
    }

    public function execute(mixed $products, array $productIds): mixed
    {
        $groupedCategories = $this->groupCategories(
            $this->categoryProduct->getCategoriesByProductIds($productIds)
        );

        $categoryIdsByProductId = $groupedCategories->getCategoryIdsByProductId();
        $allCategories = $this->loadCategories($groupedCategories->getCategoryIds());

        foreach ($products as $product) {
            $productId = (int)$product->getId();

            $this->assignCategoryCollection(
                $product,
                $this->buildCategoryCollection($categoryIdsByProductId[$productId] ?? [], $allCategories)
            );
        }

        return $products;
    }

    public function getPreloadedStoreId(): ?int
    {
        return null;
    }

    protected function groupCategories(array $categories): \Magento\Framework\DataObject
    {
        $categoryIdsByProductId = [];
        $categoryIds = [];

        foreach ($categories as $category) {
            $productId = (int)$category['product_id'];
            $categoryId = (int)$category['category_id'];

            $categoryIdsByProductId[$productId][] = $categoryId;
            $categoryIds[$categoryId] = true;
        }

        return $this->dataObjectFactory->create([
            'category_ids_by_product_id' => $categoryIdsByProductId,
            'category_ids' => array_keys($categoryIds)
        ]);
    }

    protected function loadCategories(array $categoryIds): array
    {
        $categoriesCollection = $this->categoryCollectionFactory->create();
        $categoriesCollection
            ->addIdFilter($categoryIds)
            ->addAttributeToSelect('name');

        if ($this->getPreloadedStoreId() !== null) {
            $categoriesCollection->setStoreId($this->getPreloadedStoreId());
        }

        return $categoriesCollection->getItems();
    }

    protected function buildCategoryCollection(array $categoryIds, array $allCategories): \Magento\Catalog\Model\ResourceModel\Category\Collection
    {
        $categoryCollection = $this->categoryCollectionFactory->create();

        foreach ($categoryIds as $categoryId) {
            if (!isset($allCategories[$categoryId])) {
                continue;
            }

            $categoryCollection->addItem($allCategories[$categoryId]);
        }

        $this->getSetIsLoadedMethod()->invokeArgs($categoryCollection, [true]);

        return $categoryCollection;
    }

    protected function assignCategoryCollection(
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection
    ): void {
        $this->getSetCategoryCollectionMethod()->invokeArgs($product, [$categoryCollection]);
        $this->getProductIdCachedProperty()->setValue($product, $product->getId());
    }

    protected function getSetIsLoadedMethod(): \ReflectionMethod
    {
        if ($this->setIsLoadedMethod === null) {
            $this->setIsLoadedMethod = new \ReflectionMethod(\Magento\Framework\Data\Collection::class, '_setIsLoaded');
        }

        return $this->setIsLoadedMethod;
    }

    protected function getSetCategoryCollectionMethod(): \ReflectionMethod
    {
        if ($this->setCategoryCollectionMethod === null) {
            $this->setCategoryCollectionMethod = new \ReflectionMethod(\Magento\Catalog\Model\Product::class, 'setCategoryCollection');
        }

        return $this->setCategoryCollectionMethod;
    }

    protected function getProductIdCachedProperty(): \ReflectionProperty
    {
        if ($this->productIdCachedProperty === null) {
            $this->productIdCachedProperty = new \ReflectionProperty(\Magento\Catalog\Model\Product::class, '_productIdCached');
        }

        return $this->productIdCachedProperty;
    }
}
