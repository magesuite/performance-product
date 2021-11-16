<?php
declare(strict_types=1);

namespace MageSuite\PerformanceProduct\Plugin\BundleProduct\Model\Plugin\Frontend;

class ProductIdentitiesExtender
{
    public function afterGetIdentities(
        \Magento\Catalog\Model\Product $subject,
        array $identities
    ): array {
        $selectionProductIds = (array)$subject->getSelectionProductIds();

        if (empty($selectionProductIds)) {
            return $identities;
        }

        foreach ($selectionProductIds as $selectionProductId) {
            $identities[] = \Magento\Catalog\Model\Product::CACHE_TAG . '_' . $selectionProductId;
        }

        return array_unique($identities);
    }
}
