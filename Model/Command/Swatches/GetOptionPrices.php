<?php

namespace MageSuite\PerformanceProduct\Model\Command\Swatches;

class GetOptionPrices
{
    /**
     * @var \Magento\Framework\Locale\Format
     */
    protected $localeFormat;

    public function __construct(\Magento\Framework\Locale\Format $localeFormat)
    {
        $this->localeFormat = $localeFormat;
    }

    public function execute($product)
    {
        $prices = [];

        $allowProducts = $this->getAllowProducts($product);

        foreach ($allowProducts as $allowProduct) {

            $tierPrices = [];
            $priceInfo = $allowProduct->getPriceInfo();
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

            $prices[$allowProduct->getId()] =
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
                            $allowProduct->getMsrp()
                        ),
                    ],
                ];
        }

        return $prices;
    }

    protected function getAllowProducts($product)
    {
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
}
