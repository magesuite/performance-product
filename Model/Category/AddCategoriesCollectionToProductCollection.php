<?php

namespace MageSuite\PerformanceProduct\Model\Category;

class AddCategoriesCollectionToProductCollection
{
    protected \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory;
    protected \MageSuite\PerformanceProduct\Model\ResourceModel\Category\CategoryProduct $categoryProduct;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \MageSuite\PerformanceProduct\Model\ResourceModel\Category\CategoryProduct $categoryProduct
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryProduct = $categoryProduct;
    }

    public function execute(mixed $products, array $productIds): mixed
    {
        $categories = $this->categoryProduct->getCategoriesByProductIds($productIds);

        $categoriesCollection = $this->categoryCollectionFactory->create();
        $categoriesCollection
            ->addIdFilter(array_column($categories, 'category_id'))
            ->addAttributeToSelect('name');

        if($this->getPreloadedStoreId() !== null) {
            $categoriesCollection->setStoreId($this->getPreloadedStoreId());
        }

        $allCategories = $categoriesCollection->getItems();

        foreach ($products as $product) {
            $productId = $product->getId();

            $productCategories = array_filter($categories, function ($category) use ($productId) {
                return $category['product_id'] == $productId;
            });

            $categoryCollection = $this->categoryCollectionFactory->create();

            array_map(function ($category) use ($allCategories, $categoryCollection) {
                if (!isset($allCategories[$category['category_id']])) {
                    return;
                }

                $categoryCollection->addItem($allCategories[$category['category_id']]);
            }, $productCategories);

            $categoryCollection->_setIsLoaded();
            $product->setCategoryCollection($categoryCollection);
            $reflection = new \ReflectionProperty($product, '_productIdCached');
            $reflection->setValue($product, $product->getId());
        }

        return $products;
    }

    public function getPreloadedStoreId(): ?int
    {
        return null;
    }

}
