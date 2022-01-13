<?php
declare(strict_types=1);

namespace MageSuite\PerformanceProduct\Plugin\Customer\Model\ResourceModel\GroupExcludedWebsite;

class CacheResults
{
    protected array $excludedWebsites = [];

    public function aroundGetCustomerGroupExcludedWebsites(
        \Magento\Customer\Model\ResourceModel\GroupExcludedWebsiteRepository $subject,
        callable $proceed,
        int $customerGroupId
    ): array {
        if (!isset($this->excludedWebsites[$customerGroupId])) {
            $this->excludedWebsites[$customerGroupId] = $proceed($customerGroupId);
        }

        return $this->excludedWebsites[$customerGroupId];
    }
}
