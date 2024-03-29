<?php

namespace MageSuite\PerformanceProduct\Plugin\ConfigurableProduct\Block\Product\View\Type\Configurable;

class DisableSwatchesOptionPrices
{
    const CATALOG_PRODUCT_VIEW_FULL_ACTION_NAME = 'catalog_product_view';

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \MageSuite\PerformanceProduct\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \Magento\ConfigurableProduct\Helper\Data
     */
    protected $configurableProductHelper;

    /**
     * @var \Magento\ConfigurableProduct\Model\ConfigurableAttributeData
     */
    protected $configurableAttributeData;

    /**
     * @var \Magento\Framework\Locale\Format
     */
    protected $localeFormat;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices
     */
    protected $variationPrices;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Catalog\Model\Product\Image\UrlBuilder
     */
    protected $imageUrlBuilder;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \MageSuite\PerformanceProduct\Helper\Configuration $configuration,
        \Magento\ConfigurableProduct\Helper\Data $configurableProductHelper,
        \Magento\ConfigurableProduct\Model\ConfigurableAttributeData $configurableAttributeData,
        \Magento\Framework\Locale\Format $localeFormat,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices $variationPrices,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\Product\Image\UrlBuilder $imageUrlBuilder,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->request = $request;
        $this->configuration = $configuration;
        $this->configurableProductHelper = $configurableProductHelper;
        $this->configurableAttributeData = $configurableAttributeData;
        $this->localeFormat = $localeFormat;
        $this->variationPrices = $variationPrices;
        $this->jsonEncoder = $jsonEncoder;
        $this->imageUrlBuilder = $imageUrlBuilder;
        $this->productMetadata = $productMetadata;
    }

    public function aroundGetJsonConfig(\Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject, \Closure $proceed)
    {
        if (!$this->configuration->isAsyncOptionPricesEnabled()) {
            return $proceed();
        }

        if ($this->request->getFullActionName() == self::CATALOG_PRODUCT_VIEW_FULL_ACTION_NAME) {
            return $proceed();
        }

        $subject->unsetData('allow_products');

        $store = $subject->getCurrentStore();
        $currentProduct = $subject->getProduct();
        $allowProducts = $subject->getAllowProducts();

        $options = $this->configurableProductHelper->getOptions($currentProduct, $allowProducts);
        $attributesData = $this->configurableAttributeData->getAttributesData($currentProduct, $options);

        $config = [
            'attributes' => $attributesData['attributes'],
            'template' => str_replace('%s', '<%- data.price %>', $store->getCurrentCurrency()->getOutputFormat()),
            'currencyFormat' => $store->getCurrentCurrency()->getOutputFormat(),
            'optionPrices' => [],
            'priceFormat' => $this->localeFormat->getPriceFormat(),
            'prices' => $this->variationPrices->getFormattedPrices($currentProduct->getPriceInfo()),
            'productId' => $currentProduct->getId(),
            'chooseText' => __('Choose an Option...'),
            'images' => $this->getOptionImages($allowProducts),
            'index' => isset($options['index']) ? $options['index'] : [],
        ];

        if (isset($options['salable'])) {
            $config['salable'] = $options['salable'];
        } elseif (version_compare($this->productMetadata->getVersion(), '2.4.4', '>=')) {
            // we need empty salable array in case all variants are not salable for Magento >= 2.4.4
            $config['salable'] = [];
        }

        if (isset($options['canDisplayShowOutOfStockStatus'])) {
            $config['canDisplayShowOutOfStockStatus'] = $options['canDisplayShowOutOfStockStatus'];
        }

        if ($currentProduct->hasPreconfiguredValues() && !empty($attributesData['defaultValues'])) {
            $config['defaultValues'] = $attributesData['defaultValues'];
        }

        $config = array_merge($config, []);

        return $this->jsonEncoder->encode($config);
    }

    protected function getOptionImages($allowProducts)
    {
        $images = [];
        foreach ($allowProducts as $product) {
            $productImages = $this->configurableProductHelper->getGalleryImages($product) ?: [];

            foreach ($productImages as $image) {
                $images[$product->getId()][] =
                    [
                        'thumb' => $image->getData('small_image_url'),
                        'img' => $image->getData('medium_image_url'),
                        'full' => $image->getData('large_image_url'),
                        'caption' => $image->getLabel(),
                        'position' => $image->getPosition(),
                        'isMain' => $image->getFile() == $product->getImage(),
                        'type' => str_replace('external-', '', $image->getMediaType()),
                        'videoUrl' => $image->getVideoUrl(),
                        'tile' => $this->imageUrlBuilder->getUrl($image->getFile(), 'category_page_grid'),
                        'tile2x' => $this->imageUrlBuilder->getUrl($image->getFile(), 'category_page_grid_x2')
                    ];
            }
        }

        return $images;
    }
}
