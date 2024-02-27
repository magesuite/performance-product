<?php

namespace MageSuite\PerformanceProduct\Plugin\CatalogRule\Model\Indexer\AbstractIndexer;

class DisableCacheClearingWhenNoCatalogRulesAreDefined
{
    protected \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory;

    public function __construct(\Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    public function afterGetIdentities(\Magento\CatalogRule\Model\Indexer\AbstractIndexer $subject, $result)
    {
        $catalogRuleCollection = $this->collectionFactory->create()
            ->addFieldToFilter('is_active', 1);

        if ($catalogRuleCollection->getSize() === 0) {
            // we cannot return here an empty array as it would cause clearing of all the cache, so we return arbitrary
            // non-existing tag to prevent it
            return ['CATALOG_RULE_CACHE_TAG'];
        }

        return $result;
    }
}
