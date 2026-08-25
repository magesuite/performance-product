<?php

declare(strict_types=1);

namespace MageSuite\PerformanceProduct\Test\Integration\Observer;

class InvalidateAttributeOptionLabelsCacheTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    protected ?\Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->attributeRepository = $this->objectManager->get(\Magento\Eav\Api\AttributeRepositoryInterface::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Catalog/_files/products_with_layered_navigation_attribute_store_options.php
     */
    public function testSavingAnOptionInvalidatesThePreviouslyCachedLabel(): void
    {
        $product = $this->productRepository->get('simple2');
        $optionId = $product->getData('test_configurable');
        $attribute = $product->getResource()->getAttribute('test_configurable');
        $attribute->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $source = $attribute->getSource();
        $source->setAttribute($attribute);

        $this->assertEquals('Option Admin Store', $source->getOptionText($optionId));

        $attributeToSave = $this->attributeRepository->get('catalog_product', 'test_configurable');
        $attributeToSave->setData('option', [
            'value' => [$optionId => [\Magento\Store\Model\Store::DEFAULT_STORE_ID => 'Updated Option Label']],
            'order' => [$optionId => 1],
        ]);
        $this->attributeRepository->save($attributeToSave);

        $refreshedAttribute = $product->getResource()->getAttribute('test_configurable');
        $refreshedAttribute->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $refreshedSource = $refreshedAttribute->getSource();
        $refreshedSource->setAttribute($refreshedAttribute);

        $this->assertEquals('Updated Option Label', $refreshedSource->getOptionText($optionId));
    }
}
