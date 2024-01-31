<?php

namespace MageSuite\PerformanceProduct\Plugin\Eav\Model\Entity\Attribute\Source\Table;

class CacheOptionLabels
{
    protected ?array $cachedLabels = null;

    protected \MageSuite\PerformanceProduct\Helper\Configuration $configuration;

    protected \Magento\Framework\App\ResourceConnection $resourceConnection;

    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    public function __construct(
        \MageSuite\PerformanceProduct\Helper\Configuration $configuration,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->configuration = $configuration;
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
    }

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

        if (!is_array($ids)) {
            $ids = is_string($ids) ? explode(',', $ids) : [$ids];
        }

        $options = [];
        foreach ($ids as $id) {
            $attributeId = $subject->getAttribute()->getId();
            $storeId = $this->storeManager->getStore()->getId();

            if (isset($this->cachedLabels[$attributeId][$id][$storeId])) {
                $options[] = $this->cachedLabels[$attributeId][$id][$storeId];
            } elseif (isset($this->cachedLabels[$attributeId][$id][0])) {
                $options[] = $this->cachedLabels[$attributeId][$id][0];
            }
        }

        if ($withEmpty) {
            $options = $this->addEmptyOption($options);
        }

        return $options;
    }

    private function addEmptyOption(array $options): array
    {
        array_unshift($options, ['label' => ' ', 'value' => '']);
        return $options;
    }

    protected function getAllOptionLabels(): array
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

            $result[$attributeId][$optionId][$storeId] = [
                'value' => $option['option_id'],
                'label' => $option['value']
            ];
        }

        return $result;
    }
}
