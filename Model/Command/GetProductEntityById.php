<?php

namespace MageSuite\PerformanceProduct\Model\Command;

class GetProductEntityById
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    public function __construct(\Magento\Catalog\Api\ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function execute($productId)
    {
        try {
            $product = $this->productRepository->getById($productId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }

        return $product;
    }
}
