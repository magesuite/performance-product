<?php

namespace MageSuite\PerformanceProduct\Helper;

class Configuration
{
    public const XNL_PATH_SWATCHES_ASYNC_OPTION_PRICES = 'product_performance/swatches/async_option_prices';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface)
    {
        $this->scopeConfig = $scopeConfigInterface;
    }

    public function isAsyncOptionPricesEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XNL_PATH_SWATCHES_ASYNC_OPTION_PRICES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
