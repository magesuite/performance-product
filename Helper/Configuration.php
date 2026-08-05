<?php

declare(strict_types=1);

namespace MageSuite\PerformanceProduct\Helper;

class Configuration
{
    public const XML_PATH_SWATCHES_ASYNC_OPTION_PRICES = 'product_performance/swatches/async_option_prices';
    public const XML_PATH_SWATCHES_PRELOAD_CHILD_PRODUCTS_IMAGES = 'product_performance/swatches/preload_child_products_images';
    public const XML_PATH_CACHE_ATTRIBUTE_TEXT_VALUES = 'product_performance/attributes/cache_attribute_text_values';
    public const XML_PATH_CLEAN_PARENT_CONFIGURABLE_CACHE_TAGS = 'product_performance/configurable_cache/clean_parent_cache_tags_on_child_save';

    public function __construct(
        protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isAsyncOptionPricesEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SWATCHES_ASYNC_OPTION_PRICES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isPreloadChildProductsImagesEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SWATCHES_PRELOAD_CHILD_PRODUCTS_IMAGES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isCacheAttributeTextValuesEnabled(): bool
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CACHE_ATTRIBUTE_TEXT_VALUES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isCleanParentConfigurableCacheTagsEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CLEAN_PARENT_CONFIGURABLE_CACHE_TAGS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
