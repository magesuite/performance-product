<?php

namespace MageSuite\PerformanceProduct\Test\Integration\Plugin\Catalog\Model\ResourceModel\Product\Collection;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @magentoAppArea frontend
 * @magentoDataFixture Magento/Catalog/_files/multiple_mixed_products.php
 */
class PreloadChildrenForConfigurableProductsTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory;

    protected function setUp(): void
    {
        $this->productCollectionFactory = \Magento\TestFramework\ObjectManager::getInstance()->get(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);
    }

    public function testPreloadChildrenIds()
    {
        $expectedResults = [
            'configurable' => [31, 32],
            'configurable_12345' => [41, 42],
            'simple1' => null,
            'simple2' => null,
            'simple_31' => null,
            'simple_32' => null,
            'simple_41' => null,
            'simple_42' => null,
        ];

        $collection = $this->productCollectionFactory->create();

        foreach ($collection->getItems() as $item) {
            $result = $item->getChildrenProductIds();
            $expectedResult = $expectedResults[$item->getSku()];

            $this->assertEquals($expectedResult, $result);
        }
    }

    public function testPreloadParentsIds()
    {
        $expectedResults = [
            'configurable' => null,
            'configurable_12345' => null,
            'simple1' => null,
            'simple2' => null,
            'simple_31' => [1],
            'simple_32' => [1],
            'simple_41' => [2],
            'simple_42' => [2],
        ];

        $collection = $this->productCollectionFactory->create();

        foreach ($collection->getItems() as $item) {
            $result = $item->getParentProductIds();
            $expectedResult = $expectedResults[$item->getSku()];

            $this->assertEquals($expectedResult, $result);
        }
    }
}
