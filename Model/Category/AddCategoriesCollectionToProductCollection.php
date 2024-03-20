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

            $this->executePrivateMethod($categoryCollection, '_setIsLoaded', true);
            $this->executePrivateMethod($product, 'setCategoryCollection', $categoryCollection);
            $this->setPrivateProperty($product, '_productIdCached', $product->getId());
        }

        return $products;
    }

    private function executePrivateMethod(mixed $object, string $methodName, ...$args)
    {
        $method = new \ReflectionMethod($object, $methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }

    private function setPrivateProperty(mixed $object, string $propertyName, $value)
    {
        $property = new \ReflectionProperty($object, $propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
