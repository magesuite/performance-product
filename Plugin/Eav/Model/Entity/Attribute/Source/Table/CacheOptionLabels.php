<?php

declare(strict_types=1);

namespace MageSuite\PerformanceProduct\Plugin\Eav\Model\Entity\Attribute\Source\Table;

class CacheOptionLabels
{
    protected const CACHE_KEY = 'performance_product_attribute_option_labels';

    protected ?array $cachedLabels = null;

    public function __construct(
        protected \MageSuite\PerformanceProduct\Helper\Configuration $configuration,
        protected \Magento\Framework\App\ResourceConnection $resourceConnection,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \Magento\Eav\Model\Cache\Type $cache,
        protected \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {}

    public function aroundGetSpecificOptions(
        \Magento\Eav\Model\Entity\Attribute\Source\Table $subject,
        callable $proceed,
        $ids,
        $withEmpty
    ) {
        if (!$this->configuration->isCacheAttributeTextValuesEnabled()) {
            return $proceed($ids, $withEmpty);
        }

        if ($this->cachedLabels === null) {
            $this->cachedLabels = $this->getAllOptionLabels();
        }

        $options = $this->resolveOptions($subject, $this->normalizeIds($ids));

        if ($withEmpty) {
            $options = $this->addEmptyOption($options);
        }

        return $options;
    }

    private function normalizeIds($ids): array
    {
        if (!is_array($ids)) {
            $ids = is_string($ids) ? explode(',', $ids) : [$ids];
        }

        return array_filter($ids, fn($value) => $value !== null);
    }

    private function resolveOptions(\Magento\Eav\Model\Entity\Attribute\Source\Table $subject, array $ids): array
    {
        $attributeId = $subject->getAttribute()->getId();
        $storeId = $this->resolveStoreId($subject);
        $options = [];

        foreach ($ids as $id) {
            $option = $this->cachedLabels[$attributeId][$id][$storeId]
                ?? $this->cachedLabels[$attributeId][$id][0]
                ?? null;

            if ($option !== null) {
                $options[] = $option;
            }
        }

        return $options;
    }

    private function resolveStoreId(\Magento\Eav\Model\Entity\Attribute\Source\Table $subject)
    {
        $storeId = $subject->getAttribute()->getStoreId();

        return $storeId ?? $this->storeManager->getStore()->getId();
    }

    private function addEmptyOption(array $options): array
    {
        array_unshift($options, ['label' => ' ', 'value' => '']);
        return $options;
    }

    public function invalidateCache(): void
    {
        $this->cachedLabels = null;
        $this->cache->remove(self::CACHE_KEY);
    }

    protected function getAllOptionLabels(): array
    {
        $cachedData = $this->cache->load(self::CACHE_KEY);

        if ($cachedData !== false) {
            return $this->serializer->unserialize($cachedData);
        }

        $result = $this->fetchAllOptionLabels();
        $this->cache->save(
            $this->serializer->serialize($result),
            self::CACHE_KEY,
            [\Magento\Eav\Model\Cache\Type::CACHE_TAG]
        );

        return $result;
    }

    protected function fetchAllOptionLabels(): array
    {
        $connection = $this->resourceConnection->getConnection();
        $eavAttrOptionTableName = $connection->getTableName('eav_attribute_option');
        $eavAttrOptionValueTableName = $connection->getTableName('eav_attribute_option_value');

        $select = $connection->select()
            ->from(['eao' => $eavAttrOptionTableName])
            ->joinLeft(['eaov' => $eavAttrOptionValueTableName], 'eao.option_id = eaov.option_id')
            ->order('eao.option_id', 'ASC');
        $options = $connection->fetchAll($select);
        $result = [];

        foreach ($options as $option) {
            $attributeId = $option['attribute_id'];
            $optionId = $option['option_id'];
            $storeId = $option['store_id'];

            if ($optionId === null) {
                continue;
            }

            $result[$attributeId][$optionId][$storeId] = [
                'value' => $option['option_id'],
                'label' => $option['value']
            ];
        }

        return $result;
    }
}
