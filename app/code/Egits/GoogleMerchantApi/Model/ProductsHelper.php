<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model;

use Egits\GoogleMerchantApi\Api\Data\ProductsInterface;
use Egits\GoogleMerchantApi\Api\Data\ProductsInterfaceFactory;
use Egits\GoogleMerchantApi\Api\ProductsRepositoryInterface;
use Egits\GoogleMerchantApi\Helper\GoogleConfig;
use Egits\GoogleMerchantApi\Helper\GoogleHelper;
use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Egits\GoogleMerchantApi\Model\Service\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as MagentoProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\ProductFactory;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\ResourceModel\IteratorFactory;
use Egits\GoogleMerchantApi\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class ProductsHelper
 * Product helper class
 */
class ProductsHelper
{
    /**
     * @var ProductsInterfaceFactory
     */
    protected ProductsInterfaceFactory $productsFactory;

    /**
     * @var ProductsRepositoryInterface
     */
    protected ProductsRepositoryInterface $productsRepository;

    /**
     * @var GoogleHelper
     */
    protected GoogleHelper $googleHelper;

    /**
     * Current product store id
     *
     * @var int
     */
    private int $productStoreId;

    /**
     * @var Product
     */
    protected Product $serviceProduct;

    /**
     * @var SearchCriteriaBuilder
     */
    public SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var ProductRepository
     */
    public ProductRepository $productRepository;

    /**
     * @var Config
     */
    protected Config $configResource;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $productResourceFactory;

    /**
     * @var MagentoProductCollectionFactory
     */
    protected MagentoProductCollectionFactory $productCollectionFactory;

    /**
     * @var array
     */
    private array $productToAdd = [];

    /**
     * @var int
     */
    private int $productsAdded = 0;

    /**
     * @var ProductRepository
     */
    private ProductRepository $magentoProductRepository;

    /**
     * @var IteratorFactory
     */
    private IteratorFactory $iteratorFactory;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var GoogleConfig
     */
    private GoogleConfig $googleConfig;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $syncProductCollectionFactory;

    /**
     * ProductsHelper constructor.
     *
     * @param ProductsInterfaceFactory $productsFactory
     * @param ProductsRepositoryInterface $productsRepository
     * @param Product $serviceProduct
     * @param MagentoProductCollectionFactory $productCollectionFactory
     * @param ProductFactory $productResourceFactory
     * @param Config $config
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param IteratorFactory $iteratorFactory
     * @param ProductRepository $magentoProductRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param GoogleConfig $googleConfig
     * @param CollectionFactory $syncProductCollectionFactory
     */
    public function __construct(
        ProductsInterfaceFactory $productsFactory,
        ProductsRepositoryInterface $productsRepository,
        Product $serviceProduct,
        MagentoProductCollectionFactory $productCollectionFactory,
        ProductFactory $productResourceFactory,
        Config $config,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        IteratorFactory $iteratorFactory,
        ProductRepository $magentoProductRepository,
        ScopeConfigInterface $scopeConfig,
        GoogleConfig $googleConfig,
        CollectionFactory $syncProductCollectionFactory
    ) {
        $this->productsFactory = $productsFactory;
        $this->productsRepository = $productsRepository;
        $this->serviceProduct = $serviceProduct;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->configResource = $config;
        $this->productResourceFactory = $productResourceFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->iteratorFactory = $iteratorFactory;
        $this->magentoProductRepository = $magentoProductRepository;
        $this->scopeConfig = $scopeConfig;
        $this->googleConfig = $googleConfig;
        $this->syncProductCollectionFactory = $syncProductCollectionFactory;
    }

    /**
     * Sync Product to queue
     *
     * @param null $storeId
     * @return array
     */
    public function syncProductsToQueue()
    {
        $productAdded = 0;
        try {
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->getSelect()->join(
                ['cpw' => $productCollection->getTable('catalog_product_website')],
                'e.entity_id = cpw.product_id',
                []
            );
            $currentStore = $this->googleHelper->getCurrentStore();
            $productCollection->addStoreFilter($currentStore);
            $productCollection->setOrder('entity_id', 'ASC');
            $batchSize = $this->googleConfig->getProductSyncBatchSize();
            $productCollection->getSelect()->limit($batchSize ?? 1000);
            $collection = $this->syncProductCollectionFactory->create();
            $lastProduct = $collection->getLastItem();
            $productId = $lastProduct->getProductId();
            $lastProcessedProductId = $this->readLastSavedProductFromConfig();
            if ($lastProcessedProductId) {
                $productCollection->getSelect()->where('entity_id > ?', $lastProcessedProductId);
            }

            $iterator = $this->iteratorFactory->create();
            $iterator->walk($productCollection->getSelect(), [[$this, 'walkAndAddProductsToQueue']]);

            /**
             * Peform the last batch insert after the walk.
             */
            if (count($this->productToAdd) > 0) {

                $this->productsAdded += $this->productsRepository->batchInsert($this->productToAdd);
                $lastProduct = max($this->productToAdd);
                if ($lastProduct) {
                    $this->saveLastSavedQueueProduct($lastProduct[0]);
                }

                $this->productToAdd = [];
            }

            if ($this->productsAdded == 0) {

                $this->configResource->saveConfig(
                    GoogleConfig::CONFIG_PATH_FOR_SET_PRODUCT_SYNC_DONE,
                    '1',
                    'stores',
                    $currentStore->getId()
                );
            }
            $productAdded = $this->productsAdded;

            $status = ['error' => false, 'count' => $productAdded];
        } catch (Exception $exception) {
            $this->googleHelper->writeDebugLogFile($exception);
            $status = ['error' => true, 'count' => $productAdded];
        }

        return $status;
    }

    /**
     * Walk over the collection and insert.
     *
     * @param array $args
     * @throws Exception
     */
    public function walkAndAddProductsToQueue($args)
    {
        try {
            $productId = $args['row']['entity_id'];
            $product = $this->magentoProductRepository->getById($productId);

            $websites = $product->getWebsiteIds();
            // Return if no websites are assigned
            if (empty($websites)) {
                return;
            }
            foreach ($websites as $websiteId) {
                $website = $this->googleHelper->getWebsite($websiteId);
                $storeIds = $website->getStoreIds();
                foreach ($storeIds as $storeId) {
                    $config = $this->googleHelper->getConfig();
                    if (!$config->isGoogleMerchantApiEnabled($storeId)) {
                        continue;
                    }
                    $this->setProductStoreId($storeId)->addProductsToQueue($product);
                }
            }

            /**
             * Peform the insert as batches of 100.
             */
            if (count($this->productToAdd) >= 100) {
                $this->productsAdded += $this->productsRepository->batchInsert($this->productToAdd);
                $lastProduct = max($this->productToAdd);
                if ($lastProduct) {
                    $this->saveLastSavedQueueProduct($lastProduct[0]);
                }
                $this->productToAdd = [];
            }
        } catch (Exception $exception) {
            $this->googleHelper->writeDebugLogFile($exception);
            throw $exception;
        }
    }

    /**
     * Save Last saved Product Id
     *
     * @param int $lastId
     * @return void
     */
    private function saveLastSavedQueueProduct($lastId)
    {
        if (!$lastId) {
            return;
        }

        $this->configResource->saveConfig(
            GoogleConfig::CONFIG_PATH_FOR_SET_PRODUCT_SYNC_PROGRESS,
            $lastId
        );
    }

    /**
     * Due to Magento config cache, we need to read the dynamic data using direct query.
     *
     * @return mixed|null
     */
    private function readLastSavedProductFromConfig()
    {
        return $this->getConfigFromDb(GoogleConfig::CONFIG_PATH_FOR_SET_PRODUCT_SYNC_PROGRESS);
    }

    /**
     * Add Product to Queue
     *
     * @param ProductInterface $product
     */
    public function addProductsToQueue($product)
    {
        if ($product->getTypeId() == 'configurable') {
            $this->addAssociatedProductsToQueue($product);
        } else {
            $this->addProductToQueue($product);
        }
    }

    /**
     * Remove Item from queue and google
     *
     * @param ProductInterface $product
     */
    public function removeItem($product)
    {
        if ($product->getTypeId() == 'configurable') {
            $this->removeAssociatedProducts($product);
        } else {
            $this->removeProduct($product);
        }
    }

    /**
     * Remove associated products
     *
     * @param ProductInterface $product
     */
    private function removeAssociatedProducts($product)
    {
        /** @var Product[] $associatedProducts */
        $associatedProducts = $product->getTypeInstance()->getUsedProducts($product);
        foreach ($associatedProducts as $child) {
            $this->removeProduct($child);
        }
    }

    /**
     * Add add associated products to queue
     *
     * @param ProductInterface $product
     */
    private function addAssociatedProductsToQueue(ProductInterface $product)
    {
        $this->googleHelper->writeDebugLogFile($product->getSku());
        /** @var Product[] $associatedProducts */
        $associatedProducts = $product->getTypeInstance()->getUsedProducts($product);
        foreach ($associatedProducts as $child) {
            if (empty($child->getWebsiteIds())) {
                continue;
            }
            if ($product->getData('is_product_save')) {
                $child->setData('is_product_save', true);
            }
            $this->addProductToQueue($child);
        }

        $isAllowedParent = $this->googleHelper->getConfig()->isAllowedConfigurableParent();
        if ($isAllowedParent) {
            $this->addProductToQueue($product);
        }
    }

    /**
     * Remove product from google
     *
     * @param ProductInterface $product
     */
    private function removeProduct(ProductInterface $product)
    {
        $productInQueue = $this->getProductFromQueue($product);
        if ($productInQueue->getId()) {
            $this->serviceProduct->delete($productInQueue);
            $this->productsRepository->delete($productInQueue);
        }
    }

    /**
     * Add product to queue
     *
     * @param ProductInterface $product
     */
    private function addProductToQueue(ProductInterface $product)
    {
        $this->googleHelper->writeDebugLogFile($product->getSku());
        $productInQueue = $this->getProductFromQueue($product);
        if (!$productInQueue->getId()) {
            if (!$product->getData('is_product_save')) {
                $productId = $product->getId();
                $storeId = $this->getProductStoreId();
                foreach ($this->productToAdd as $key => $value) {
                    if (($value[0] == $productId) && ($value[1] == $storeId)) {
                        return;
                    }
                }
                $this->productToAdd[] = [$product->getId(), $this->getProductStoreId()];
                return;
            }
            $productInQueue = $this->addNewItemToQueue($product);
        } else {
            $productInQueue = $this->updateItemInQueue($productInQueue);
        }

        $productInQueue->setIsSync(false);
        $this->productsRepository->save($productInQueue);
    }

    /**
     * Add product as new item
     *
     * @param ProductInterface $product
     * @return ProductsInterface
     */
    private function addNewItemToQueue(ProductInterface $product): ProductsInterface
    {
        $productInQueue = $this->productsFactory->create();
        $productInQueue->setProductId($product->getId())
            ->setProductStoreId($this->getProductStoreId())
            ->setStatus(ProductsInterface::READY_TO_UPDATE_STATUS);
        return $productInQueue;
    }

    /**
     * Update item status
     *
     * @param ProductsInterface $productInQueue
     * @return ProductsInterface
     */
    private function updateItemInQueue(ProductsInterface $productInQueue): ProductsInterface
    {
        if (!in_array(
            $productInQueue->getStatus(),
            [ProductsInterface::READY_TO_UPDATE_STATUS, ProductsInterface::ERROR_STATUS]
        )
        ) {
            $productInQueue->setStatus(ProductsInterface::READY_TO_UPDATE_STATUS);
        }

        return $productInQueue;
    }

    /**
     * Get the product from queue if one already exist
     *
     * @param ProductInterface $product
     * @return ProductsInterface
     */
    public function getProductFromQueue(ProductInterface $product): ProductsInterface
    {
        return $this->productsRepository->loadByProductId($product->getId(), $this->getProductStoreId());
    }

    /**
     * Set google Helper Object
     *
     * @param GoogleHelper $googleHelper
     * @return $this
     */
    public function setGoogleHelper(GoogleHelper $googleHelper): ProductsHelper
    {
        $this->googleHelper = $googleHelper;
        return $this;
    }

    /**
     * Set current product correct store id.
     *
     * @param int $storeId
     * @return $this
     */
    public function setProductStoreId(int $storeId): ProductsHelper
    {
        $this->productStoreId = $storeId;
        return $this;
    }

    /**
     * Get Store Id of current product.
     *
     * @return int
     */
    public function getProductStoreId(): int
    {
        return $this->productStoreId;
    }

    /**
     * Save Internal product queue initiated flag.
     */
    public function setProductQueueSyncFlag(): bool
    {
        try {
            $this->configResource->saveConfig(
                GoogleConfig::CONFIG_PATH_FOR_INTERNAL_QUEUE_STARTED,
                1
            );
        } catch (Exception $exception) {
            $this->googleHelper->writeDebugLogFile($exception);
            return false;
        }

        return true;
    }

    /**
     * Get configuration from db
     *
     * @param string $configPath
     * @param string $scope
     * @param int $scopeId
     * @return mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfigFromDb(
        $configPath,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        int $scopeId = 0
    ) {
        $connection = $this->configResource->getConnection();
        $select = $connection->select()
            ->from($this->configResource->getMainTable())
            ->where(
                'path = ?',
                $configPath
            )->where(
                'scope = ?',
                $scope ?? ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            )->where(
                'scope_id = ?',
                $scopeId ?? 0
            );
        $row = $connection->fetchRow($select);

        return $row['value'] ?? null;
    }
}
