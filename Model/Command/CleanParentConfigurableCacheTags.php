<?php

declare(strict_types=1);

namespace MageSuite\PerformanceProduct\Model\Command;

class CleanParentConfigurableCacheTags
{
    protected const CHILD_PRODUCT_TYPES = [
        \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
        \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL
    ];

    public function __construct(
        protected \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableResource,
        protected \Magento\Framework\App\CacheInterface $cache,
        protected \Magento\Framework\Event\ManagerInterface $eventManager,
        protected \MageSuite\PerformanceProduct\Model\Cache\TagsFactory $tagsFactory
    ) {
    }

    public function execute(\Magento\Catalog\Model\Product $product): void
    {
        if (!in_array($product->getTypeId(), self::CHILD_PRODUCT_TYPES, true)) {
            return;
        }

        $parentIds = $this->configurableResource->getParentIdsByChild($product->getId());

        if (empty($parentIds)) {
            return;
        }

        $cacheTags = $this->getCacheTags($parentIds);

        $this->cache->clean($cacheTags);
        $this->eventManager->dispatch(
            'clean_cache_by_tags',
            ['object' => $this->tagsFactory->create(['tags' => $cacheTags])]
        );
    }

    protected function getCacheTags(array $parentIds): array
    {
        $cacheTags = [];

        foreach ($parentIds as $parentId) {
            $cacheTags[] = sprintf('%s_%s', \Magento\Catalog\Model\Product::CACHE_TAG, $parentId);
            $cacheTags[] = sprintf(
                '%s_%s',
                \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
                $parentId
            );
        }

        return $cacheTags;
    }
}
