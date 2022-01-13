<?php

namespace MageSuite\PerformanceProduct\Setup\Patch\Schema;

class AddReviewEntitySummaryIndex implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();

        $tableName = $connection->getTableName('review_entity_summary');
        $indexName = $connection->getIndexName($tableName, ['entity_pk_value', 'store_id', 'entity_type']);
        $indexList = $connection->getIndexList($tableName);

        if (!array_key_exists($indexName, $indexList)) {
            $connection->addIndex($tableName, $indexName, ['entity_pk_value', 'store_id', 'entity_type']);
        }

        $connection->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
