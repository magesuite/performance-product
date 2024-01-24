<?php

namespace MageSuite\PerformanceProduct\Plugin\Catalog\Helper\Data;

class CacheTaxPriceWithinSingleRequest
{
    protected ?array $cachedTaxPrice = null;

    public function aroundGetTaxPrice(
        \Magento\Catalog\Helper\Data $subject,
        callable $proceed,
        $product,
        $price,
        $includingTax = null,
        $shippingAddress = null,
        $billingAddress = null,
        $ctc = null,
        $store = null,
        $priceIncludesTax = null,
        $roundPrice = true
    ) {
        if (!isset($this->cachedTaxPrice[$product->getId()][(int)$includingTax][(string)$price])) {
            $taxPrice = $proceed($product, $price, $includingTax, $shippingAddress, $billingAddress, $ctc, $store, $priceIncludesTax, $roundPrice);
            $this->cachedTaxPrice[$product->getId()][(int)$includingTax][(string)$price] = $taxPrice;
        }

        return $this->cachedTaxPrice[$product->getId()][(int)$includingTax][(string)$price];
    }
}
