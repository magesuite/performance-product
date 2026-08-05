<?php

declare(strict_types=1);

namespace MageSuite\PerformanceProduct\Observer;

class CleanParentConfigurableCacheTagsAfterProductSave implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(
        protected \MageSuite\PerformanceProduct\Helper\Configuration $configuration,
        protected \MageSuite\PerformanceProduct\Model\Command\CleanParentConfigurableCacheTags $cleanParentConfigurableCacheTags
    ) {
    }

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        if (!$this->configuration->isCleanParentConfigurableCacheTagsEnabled()) {
            return;
        }

        $product = $observer->getEvent()->getProduct();

        if (!$product instanceof \Magento\Catalog\Model\Product) {
            return;
        }

        $this->cleanParentConfigurableCacheTags->execute($product);
    }
}
