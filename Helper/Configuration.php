<?php

namespace MageSuite\PerformanceProduct\Helper;

class Configuration
{
    const XNL_PATH_SWATCHES_ASYNC_OPTION_PRICES = 'product_performance/swatches/async_option_prices';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface)
    {
        $this->scopeConfig = $scopeConfigInterface;
    }

    public function isAsyncOptionPricesEnabled()
    {
        return (bool)$this->scopeConfig->getValue(self::XNL_PATH_SWATCHES_ASYNC_OPTION_PRICES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
