<?php

namespace MageSuite\PerformanceProduct\Test\Integration\Plugin\Catalog\Model\ResourceModel\Product\Collection;

class CacheOptionLabelsTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository = null;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Catalog/_files/products_with_layered_navigation_attribute_store_options.php
     */
    public function testItReturnsCachedValueForSpecificStoreId()
    {
        $sku = 'simple2';
        $attributeCode = 'test_configurable';

        $product = $this->productRepository->get($sku);
        $attribute = $product->getResource()->getAttribute($attributeCode);

        $optionId = $product->getData($attributeCode);
        $source = $attribute->getSource();

        $this->assertEquals('Option Default Store', $source->getOptionText($optionId));

        $attribute->setStoreId(0);
        $source->setAttribute($attribute);

        $this->assertEquals('Option Admin Store', $source->getOptionText($optionId));
    }
}
