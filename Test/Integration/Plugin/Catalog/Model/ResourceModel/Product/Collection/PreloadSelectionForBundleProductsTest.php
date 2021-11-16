<?php

namespace MageSuite\PerformanceProduct\Test\Integration\Plugin\Catalog\Model\ResourceModel\Product\Collection;

class PreloadSelectionForBundleProductsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->productCollectionFactory = $objectManager->get(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);
        $this->productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Bundle/_files/bundle_product_radio_required_option.php
     */
    public function testPreloadSelection()
    {
        $product1 = $this->productRepository->get('simple-1');
        $expectedResults = [
            'simple-1' => null,
            'bundle-product-radio-required-option' => [
                $product1->getId()
            ]
        ];
        $collection = $this->productCollectionFactory->create();

        foreach ($collection->getItems() as $item) {
            $result = $item->getSelectionProductIds();
            $expectedResult = $expectedResults[$item->getSku()];

            $this->assertEquals($expectedResult, $result);
        }
    }
}
