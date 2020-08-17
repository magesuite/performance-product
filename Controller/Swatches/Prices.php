<?php

namespace MageSuite\PerformanceProduct\Controller\Swatches;

class Prices extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * @var \MageSuite\PerformanceProduct\Model\Command\Swatches\GetOptionPrices
     */
    protected $getOptionPrices;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \MageSuite\PerformanceProduct\Model\Command\Swatches\GetOptionPrices $getOptionPrices
    ) {
        parent::__construct($context);
        $this->getOptionPrices = $getOptionPrices;
    }

    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $optionPrices = $this->getOptionPrices->execute($productId);

        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $result->setData($optionPrices);

        return $result;
    }

}
