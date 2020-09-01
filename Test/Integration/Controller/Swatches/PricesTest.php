<?php

namespace MageSuite\PerformanceProduct\Test\Integration\Controller\Swatches;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class PricesTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->_objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testItReturnsSwatchesOptionPrices()
    {
        $productSku = 'configurable';
        $product = $this->productRepository->get($productSku);

        $this->dispatch('performance/swatches/prices/product_id/' . $product->getId());

        $body = $this->getResponse()->getBody();
        $result = json_decode($body, true);

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('oldPrice', $result[10]);
        $this->assertEquals(10, $result[10]['oldPrice']['amount']);
        $this->assertEquals(20, $result[20]['finalPrice']['amount']);
    }
}
