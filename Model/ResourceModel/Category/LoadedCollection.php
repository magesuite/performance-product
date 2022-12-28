<?php

declare(strict_types=1);

namespace MageSuite\PerformanceProduct\Model\ResourceModel\Category;

class LoadedCollection extends \Magento\Catalog\Model\ResourceModel\Category\Collection
{
    public function addItems(array $items)
    {
        foreach ($items as $item) {
            $this->_addItem($item);
        }
    }

    public function isLoaded(): bool
    {
        return true;
    }
}
