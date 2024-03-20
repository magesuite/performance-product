<?php

namespace MageSuite\PerformanceProduct\Plugin\Catalog\Block\Product\ListProduct;

class PreloadCategoriesCollections
{
    protected \MageSuite\PerformanceProduct\Model\Category\AddCategoriesCollectionToProductCollection $addCategoriesCollectionToProductCollection;

    public function __construct(
        \MageSuite\PerformanceProduct\Model\Category\AddCategoriesCollectionToProductCollection $addCategoriesCollectionToProductCollection
    ) {
        $this->addCategoriesCollectionToProductCollection = $addCategoriesCollectionToProductCollection;
    }

    public function afterGetLoadedProductCollection(
        \Magento\Catalog\Block\Product\ListProduct $subject,
        $result
    ) {
        if ($result->hasFlag('categories_collections_preloaded')) {
            return $result;
        }

        $result->setFlag('categories_collections_preloaded', true);
        $productIds = [];

        foreach ($result->getItems() as $product) {
            $productIds[] = $product->getId();
        }

        if (empty($productIds)) {
            return $result;
        }

        $this->addCategoriesCollectionToProductCollection->execute($result, $productIds);

        return $result;
    }
}
