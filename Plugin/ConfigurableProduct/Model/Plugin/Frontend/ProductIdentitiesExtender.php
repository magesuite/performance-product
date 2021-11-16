<?php
declare(strict_types=1);

namespace MageSuite\PerformanceProduct\Plugin\ConfigurableProduct\Model\Plugin\Frontend;

class ProductIdentitiesExtender
{
    public function afterGetIdentities(
        \Magento\Catalog\Model\Product $subject,
        array $identities
    ): array {
        $childrenProductIds = (array)$subject->getChildrenProductIds();

        if (empty($childrenProductIds)) {
            return $identities;
        }

        foreach ($childrenProductIds as $childId) {
            $identities[] = \Magento\Catalog\Model\Product::CACHE_TAG . '_' . $childId;
        }

        return array_unique($identities);
    }
}
