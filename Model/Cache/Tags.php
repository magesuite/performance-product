<?php

declare(strict_types=1);

namespace MageSuite\PerformanceProduct\Model\Cache;

class Tags implements \Magento\Framework\DataObject\IdentityInterface
{
    public function __construct(protected array $tags = [])
    {
    }

    public function getIdentities(): array
    {
        return $this->tags;
    }
}
