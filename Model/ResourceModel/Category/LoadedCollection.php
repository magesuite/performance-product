<?php

declare(strict_types=1);

namespace MageSuite\PerformanceProduct\Model\ResourceModel\Category;

class LoadedCollection extends \Magento\Catalog\Model\ResourceModel\Category\Collection
{
    public function setItems(array $items): void
    {
        $this->_items = $items;
    }

    public function isLoaded(): bool
    {
        return true;
    }
}
