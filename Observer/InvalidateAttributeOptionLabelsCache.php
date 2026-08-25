<?php

declare(strict_types=1);

namespace MageSuite\PerformanceProduct\Observer;

class InvalidateAttributeOptionLabelsCache implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(
        protected \MageSuite\PerformanceProduct\Plugin\Eav\Model\Entity\Attribute\Source\Table\CacheOptionLabels $cacheOptionLabels
    ) {
    }

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        $object = $observer->getEvent()->getObject();

        if (!$object instanceof \Magento\Eav\Model\Entity\Attribute) {
            return;
        }

        $this->cacheOptionLabels->invalidateCache();
    }
}
