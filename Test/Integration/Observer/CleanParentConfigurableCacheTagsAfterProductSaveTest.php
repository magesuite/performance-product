<?php

declare(strict_types=1);

namespace MageSuite\PerformanceProduct\Test\Integration\Observer;

use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;

class CleanParentConfigurableCacheTagsAfterProductSaveTest extends \PHPUnit\Framework\TestCase
{
    protected const CACHE_ENTRY_KEY = 'zj777_used_products_cache_entry';

    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository = null;
    protected ?\Magento\Framework\App\CacheInterface $cache = null;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->cache = $this->objectManager->get(\Magento\Framework\App\CacheInterface::class);
    }

    protected function tearDown(): void
    {
        $this->cache->remove(self::CACHE_ENTRY_KEY);
    }

    #[AppIsolation(true)]
    #[DbIsolation(false)]
    #[AppArea('adminhtml')]
    #[DataFixture('Magento/ConfigurableProduct/_files/product_configurable.php')]
    public function testItCleansParentConfigurableCacheTagsWhenChildIsSaved()
    {
        $this->saveCacheEntryTaggedWithParentConfigurableTag();

        $this->assertNotFalse($this->cache->load(self::CACHE_ENTRY_KEY));

        $this->saveChildProductWithNewPrice();

        $this->assertFalse($this->cache->load(self::CACHE_ENTRY_KEY));
    }

    #[AppIsolation(true)]
    #[DbIsolation(false)]
    #[AppArea('adminhtml')]
    #[Config('product_performance/configurable_cache/clean_parent_cache_tags_on_child_save', 0)]
    #[Config('product_performance/configurable_cache/clean_parent_cache_tags_on_child_save', 0, 'store', 'default')]
    #[DataFixture('Magento/ConfigurableProduct/_files/product_configurable.php')]
    public function testItDoesNotCleanParentConfigurableCacheTagsWhenConfigurationIsDisabled()
    {
        $this->saveCacheEntryTaggedWithParentConfigurableTag();

        $this->assertNotFalse($this->cache->load(self::CACHE_ENTRY_KEY));

        $this->saveChildProductWithNewPrice();

        $this->assertNotFalse($this->cache->load(self::CACHE_ENTRY_KEY));
    }

    protected function saveCacheEntryTaggedWithParentConfigurableTag(): void
    {
        $configurableProduct = $this->productRepository->get('configurable');

        $this->cache->save(
            'used_products_data',
            self::CACHE_ENTRY_KEY,
            [
                sprintf(
                    '%s_%s',
                    \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
                    $configurableProduct->getId()
                )
            ]
        );
    }

    protected function saveChildProductWithNewPrice(): void
    {
        $childProduct = $this->productRepository->get('simple_10');
        $childProduct->setPrice(123.45);
        $this->productRepository->save($childProduct);
    }
}
