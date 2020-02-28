<?php

namespace MageSuite\PerformanceProduct\Test\Integration\Observer\Catalog\Product\Collection;

class PreloadChildrenTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    public function setUp()
    {
        $this->productCollectionFactory = \Magento\TestFramework\ObjectManager::getInstance()->get(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Catalog/_files/multiple_mixed_products.php
     */
    public function testPreloadChildren()
    {
        $expectedResults = [
            'configurable' => ['31', '32'],
            'configurable_12345' => ['41', '42'],
            'simple1' => null,
            'simple2' => null,
            'simple_31' => null,
            'simple_32' => null,
            'simple_41' => null,
            'simple_42' => null,
        ];

        $collection = $this->productCollectionFactory->create();
        foreach ($collection as $item) {
            $result = $item->getChildrenProductIds();
            $expectedResult = $expectedResults[$item->getSku()];

            $this->assertEquals($expectedResult, $result);
        }
    }
}