<?php

namespace MageSuite\PerformanceProduct\Plugin\GroupedProduct\Model\Product\Type\Grouped;

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

    public function afterGetAssociatedProducts(
        \Magento\GroupedProduct\Model\Product\Type\Grouped $subject,
        $result,
        $product
    ) {
        if ($this->request->getFullActionName() !== 'catalog_product_view') {
            return $result;
        }

        if (!$this->stacktraceAnalyser->isInvokedBy('MagePal\GoogleAnalytics4\Block\Data\Product', '_dataLayer', 10)) {
            return $result;
        }

        $productIds = [];

        foreach ($result as $associatedProduct) {
            $productIds[] = $associatedProduct->getId();
        }

        $this->addCategoriesCollectionToProductCollection->execute($result, $productIds);

        return $result;
    }
}
