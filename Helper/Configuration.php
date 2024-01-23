<?php

namespace MageSuite\PerformanceProduct\Helper;

class Configuration
{
    public const XML_PATH_SWATCHES_ASYNC_OPTION_PRICES = 'product_performance/swatches/async_option_prices';

    public const XML_PATH_CACHE_ATTRIBUTE_TEXT_VALUES = 'product_performance/attributes/cache_attribute_text_values';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface)
    {
        $this->scopeConfig = $scopeConfigInterface;
    }

    public function isAsyncOptionPricesEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SWATCHES_ASYNC_OPTION_PRICES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isCacheAttributeTextValuesEnabled(): bool
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CACHE_ATTRIBUTE_TEXT_VALUES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
