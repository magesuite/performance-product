<?php

namespace MageSuite\PerformanceProduct\Plugin\ConfigurableProduct\Model\Product\Type\Configurable;

class PreloadCategoriesCollections
{
    protected \MageSuite\PerformanceProduct\Model\Category\AddCategoriesCollectionToProductCollection $addCategoriesCollectionToProductCollection;
    protected \Magento\Framework\App\Request\Http $request;
    protected \MageSuite\PerformanceProduct\Service\StacktraceAnalyser $stacktraceAnalyser;

    public function __construct(
        \MageSuite\PerformanceProduct\Model\Category\AddCategoriesCollectionToProductCollection $addCategoriesCollectionToProductCollection,
        \Magento\Framework\App\Request\Http $request,
        \MageSuite\PerformanceProduct\Service\StacktraceAnalyser $stacktraceAnalyser
    ) {
        $this->addCategoriesCollectionToProductCollection = $addCategoriesCollectionToProductCollection;
        $this->request = $request;
        $this->stacktraceAnalyser = $stacktraceAnalyser;
    }

    public function aroundGetUsedProducts(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject,
        callable $proceed,
        $product
    ): array {
        $products = $proceed($product);

        if ($this->request->getFullActionName() !== 'catalog_product_view') {
            return $products;
        }

        if (!$this->stacktraceAnalyser->isInvokedBy('MagePal\GoogleAnalytics4\Block\Data\Product', '_dataLayer', 10)) {
            return $products;
        }

        $productIds = [];

        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        $products = $this->addCategoriesCollectionToProductCollection->execute($products, $productIds);

        return $products;
    }
}
