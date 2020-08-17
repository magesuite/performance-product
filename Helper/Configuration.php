<?php

namespace MageSuite\PerformanceProduct\Helper;

class Configuration
{
    const XNL_PATH_SWATCHES_DISABLE_OPTION_PRICES = 'product_performance/swatches/disable_option_prices';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface)
    {
        $this->scopeConfig = $scopeConfigInterface;
    }

    public function areSwatchesOptionPricesDisabled()
    {
        return (bool)$this->scopeConfig->getValue(self::XNL_PATH_SWATCHES_DISABLE_OPTION_PRICES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
