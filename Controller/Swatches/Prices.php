<?php

namespace MageSuite\PerformanceProduct\Controller\Swatches;

class Prices extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \MageSuite\PerformanceProduct\Model\Command\Swatches\GetOptionPrices
     */
    protected $getOptionPrices;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \MageSuite\PerformanceProduct\Model\Command\Swatches\GetOptionPrices $getOptionPrices
    ) {
        parent::__construct($context);

        $this->productRepository = $productRepository;
        $this->getOptionPrices = $getOptionPrices;
    }

    public function execute()
    {
        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $productId = (int)$this->getRequest()->getParam('product_id');

        if (!$productId) {
            return $result;
        }

        $product = $this->getProduct($productId);

        if (!$product) {
            return $result;
        }

        $optionPrices = $this->getOptionPrices->execute($product);

        $result->setHeader('X-Magento-Tags', $this->getProductIdentities($product));
        $result->setData($optionPrices);

        return $result;
    }

    protected function getProductIdentities($product)
    {
        $identities = $product->getIdentities();

        if (empty($identities)) {
            return $identities;
        }

        $identities = array_diff($identities, [\Magento\Catalog\Model\Product::CACHE_TAG]);

        return implode(',', $identities);
    }

    protected function getProduct($productId)
    {
        try {
            $product = $this->productRepository->getById($productId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }

        return $product;
    }
}
