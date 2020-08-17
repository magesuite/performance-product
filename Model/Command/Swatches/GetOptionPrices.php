<?php

namespace MageSuite\PerformanceProduct\Model\Command\Swatches;

class GetOptionPrices
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Locale\Format
     */
    protected $localeFormat;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Locale\Format $localeFormat
    ) {
        $this->productRepository = $productRepository;
        $this->localeFormat = $localeFormat;
    }

    public function execute($productId)
    {
        $prices = [];

        $allowProducts = $this->getAllowProducts($productId);

        if (!$productId) {
            return $prices;
        }

        foreach ($allowProducts as $product) {
            $tierPrices = [];
            $priceInfo = $product->getPriceInfo();
            $tierPriceModel =  $priceInfo->getPrice('tier_price');
            $tierPricesList = $tierPriceModel->getTierPriceList();

            foreach ($tierPricesList as $tierPrice) {
                $tierPrices[] = [
                    'qty' => $this->localeFormat->getNumber($tierPrice['price_qty']),
                    'price' => $this->localeFormat->getNumber($tierPrice['price']->getValue()),
                    'percentage' => $this->localeFormat->getNumber(
                        $tierPriceModel->getSavePercent($tierPrice['price'])
                    ),
                ];
            }

            $prices[$product->getId()] =
                [
                    'oldPrice' => [
                        'amount' => $this->localeFormat->getNumber(
                            $priceInfo->getPrice('regular_price')->getAmount()->getValue()
                        ),
                    ],
                    'basePrice' => [
                        'amount' => $this->localeFormat->getNumber(
                            $priceInfo->getPrice('final_price')->getAmount()->getBaseAmount()
                        ),
                    ],
                    'finalPrice' => [
                        'amount' => $this->localeFormat->getNumber(
                            $priceInfo->getPrice('final_price')->getAmount()->getValue()
                        ),
                    ],
                    'tierPrices' => $tierPrices,
                    'msrpPrice' => [
                        'amount' => $this->localeFormat->getNumber(
                            $product->getMsrp()
                        ),
                    ],
                ];
        }

        return $prices;
    }

    protected function getAllowProducts($productId)
    {
        $product = $this->getProduct($productId);

        if (!$product || $product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return null;
        }

        $products = [];
        $allProducts = $product->getTypeInstance()->getUsedProducts($product, null);

        foreach ($allProducts as $product) {
            if ((int) $product->getStatus() === \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
                $products[] = $product;
            }
        }

        return $products;
    }

    protected function getProduct($productId)
    {
        if (!$productId) {
            return null;
        }

        try {
            $product = $this->productRepository->getById($productId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }

        return $product;
    }
}
