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

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\PageCache\Model\Config
     */
    protected $pageCacheConfig;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \MageSuite\PerformanceProduct\Model\Command\Swatches\GetOptionPrices $getOptionPrices,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\PageCache\Model\Config $pageCacheConfig
    ) {
        parent::__construct($context);

        $this->productRepository = $productRepository;
        $this->getOptionPrices = $getOptionPrices;
        $this->resultPageFactory = $resultPageFactory;
        $this->pageCacheConfig = $pageCacheConfig;
    }

    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('product_id');

        if (!$productId) {
            return $this->setNotFoundHeaders();
        }

        $product = $this->getProduct($productId);

        if (!$product || $product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return $this->setNotFoundHeaders();
        }

        $this->getResponse()->setPublicHeaders($this->pageCacheConfig->getTtl());

        $optionPrices = $this->getOptionPrices->execute($product);
        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $result->setHeader('X-Magento-Tags', $this->getProductIdentities($product));
        $result->setData($optionPrices);

        return $result;
    }

    protected function setNotFoundHeaders()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setStatusHeader(404, '1.1', 'Not Found');
        $resultPage->setHeader('Status', '404 File not found');

        return $resultPage;
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
