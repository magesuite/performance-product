<?php
declare(strict_types=1);

namespace MageSuite\PerformanceProduct\Plugin\ConfigurableProduct\Model\Plugin\Frontend;

class ProductIdentitiesExtender
{
    public function afterGetIdentities(\Magento\Catalog\Model\Product $subject, array $identities): array
    {
        if ($subject->getTypeId() !== \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
            return $identities;
        }

        $parentProductIds = (array)$subject->getParentProductIds();

        if (empty($parentProductIds)) {
            return $identities;
        }

        foreach ($parentProductIds as $parentProductId) {
            $identities[] = sprintf('%s_%s', \Magento\Catalog\Model\Product::CACHE_TAG, $parentProductId);
        }

        return array_unique($identities);
    }
}
