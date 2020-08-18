<?php

namespace MageSuite\PerformanceProduct\Controller\Swatches;

class Prices extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * @var \MageSuite\PerformanceProduct\Model\Command\GetProductEntityById
     */
    protected $getProductEntityById;

    /**
     * @var \MageSuite\PerformanceProduct\Model\Command\Swatches\GetOptionPrices
     */
    protected $getOptionPrices;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \MageSuite\PerformanceProduct\Model\Command\GetProductEntityById $getProductEntityById,
        \MageSuite\PerformanceProduct\Model\Command\Swatches\GetOptionPrices $getOptionPrices
    ) {
        parent::__construct($context);

        $this->getProductEntityById = $getProductEntityById;
        $this->getOptionPrices = $getOptionPrices;
    }

    public function execute()
    {
        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $productId = (int)$this->getRequest()->getParam('product_id');

        if (!$productId) {
            return $result;
        }

        $product = $this->getProductEntityById->execute($productId);

        if (!$product) {
            return $result;
        }

        $optionPrices = $this->getOptionPrices->execute($product);

        $result->setHeader('X-Magento-Tags', implode(',', $product->getIdentities()));
        $result->setData($optionPrices);

        return $result;
    }
}
