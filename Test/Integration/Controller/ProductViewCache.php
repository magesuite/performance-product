<?php

declare(strict_types=1);

namespace Integration\Controller;

class ProductViewCache extends \Magento\TestFramework\TestCase\AbstractController
{
    protected ?\Magento\Framework\App\Cache\Manager $cacheManager = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheManager = $this->_objectManager->create(\Magento\Framework\App\Cache\Manager::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoCache full_page enabled
     * @magentoConfigFixture system/full_page_cache/caching_application 2
     * @return void
     */
    public function testProductPageCacheableWhenVarnishFpcIsEnabled(): void
    {
        $this->cacheManager->clean(['full_page', 'layout']);
        $this->getRequest()->setParam('id', 1);
        $this->dispatch('catalog/product/view');
        $this->assertHeaderPcre('Pragma', '/^cache/i');
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoCache full_page enabled
     * @magentoConfigFixture system/full_page_cache/caching_application 1
     * @return void
     */
    public function testProductPageCacheableForNonVarnishFpc(): void
    {
        $this->cacheManager->clean(['full_page', 'layout']);
        $this->getRequest()->setParam('id', 1);
        $this->dispatch('catalog/product/view');
        $this->assertHeaderPcre('Pragma', '/^no-cache/i');

        $this->dispatch('catalog/product/view');
        $this->assertHeaderPcre('Pragma', '/^cache/i');
    }
}
