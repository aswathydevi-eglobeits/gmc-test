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

use Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterface;
use Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterfaceFactory;
use Egits\GoogleMerchantApi\Api\AttributeMapTypeRepositoryInterfaceFactory;
use Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeSearchResultInterface;
use Egits\GoogleMerchantApi\Api\Data\ProductsInterface;
use Egits\GoogleMerchantApi\Model\ResourceModel\AttributeMapTypeRepository;
use Egits\GoogleMerchantApi\Model\ResourceModel\Product as ProductResource;
use Egits\GoogleMerchantApi\Model\Service\Product as ServiceProduct;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class Product
 * Google product model class
 */
class Product extends AbstractModel implements ProductsInterface
{
    /**
     * Item auto renew before threshold days
     * if cron scheduled daily then 2 days
     * if cron scheduled weekly then 9 days
     * if cron scheduled monthly then 31 days
     *
     * @var int[]
     */
    public array $itemRenewDaysBefore = ['D' => 2, 'W' => 9, 'M' => 31];

    /**
     * @var ProductRepositoryInterfaceFactory
     */
    protected $productRepositoryFactory;

    /**
     * @var ServiceProduct
     */
    protected $serviceProduct;

    /**
     * @var AttributeMapTypeRepositoryInterfaceFactory
     */
    protected $attributeMapTypeRepositoryFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var AttributeMapTypeInterfaceFactory
     */
    private $attributeMapTypeFactory;

    /**
     * Current operation update or sync
     *
     * @var bool
     */
    private $isSync = true;

    /**
     * Product constructor.
     *
     * @param ProductRepositoryInterfaceFactory $productRepositoryFactory
     * @param ServiceProduct $serviceProduct
     * @param AttributeMapTypeRepositoryInterfaceFactory $attributeMapTypeRepositoryFactory
     * @param AttributeMapTypeInterfaceFactory $attributeMapTypeFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        ProductRepositoryInterfaceFactory $productRepositoryFactory,
        ServiceProduct $serviceProduct,
        AttributeMapTypeRepositoryInterfaceFactory $attributeMapTypeRepositoryFactory,
        AttributeMapTypeInterfaceFactory $attributeMapTypeFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Context $context,
        Registry $registry,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->productRepositoryFactory = $productRepositoryFactory;
        $this->serviceProduct = $serviceProduct;
        $this->attributeMapTypeRepositoryFactory = $attributeMapTypeRepositoryFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeMapTypeFactory = $attributeMapTypeFactory;
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(ProductResource::class);
        $this->setIdFieldName('entity_id');
    }

    /**
     * Get Product Id
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->_getData(self::PRODUCT_ID);
    }

    /**
     * Get Status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->_getData(self::STATUS);
    }

    /**
     * Get  Store id
     *
     * @return int
     */
    public function getProductStoreId()
    {
        return $this->_getData(self::STORE_ID);
    }

    /**
     * Get  Added date
     *
     * @return string
     */
    public function getAddedDate()
    {
        return $this->_getData(self::ADDED_DATE);
    }

    /**
     * Get  Store id
     *
     * @return string
     */
    public function getUpdatedDate()
    {
        return $this->_getData(self::UPDATED_DATE);
    }

    /**
     * Set Product id
     *
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->setData(self::PRODUCT_ID, $productId);
        return $this;
    }

    /**
     * Set Status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
        return $this;
    }

    /**
     *  Get Google Content Id
     */
    public function getGoogleContentId()
    {
        return $this->_getData(self::GOOGLE_CONTENT_ITEM_ID);
    }

    /**
     * Set Google content id
     *
     * @param string $id
     * @return $this
     */
    public function setGoogleContentId($id)
    {
        $this->setData(self::GOOGLE_CONTENT_ITEM_ID, $id);
        return $this;
    }

    /**
     * Set Store Id
     *
     * @param int $storeId
     * @return $this
     */
    public function setProductStoreId($storeId)
    {
        $this->setData(self::STORE_ID, $storeId);
        return $this;
    }

    /**
     * Set AddedDate
     *
     * @param string $addedDate
     * @return void
     */
    public function setAddedDate($addedDate)
    {
        $this->setData(self::ADDED_DATE, $addedDate);
    }

    /**
     * Set Updated date
     *
     * @param string $updatedDate
     * @return $this
     */
    public function setUpdatedDate($updatedDate)
    {
        $this->setData(self::UPDATED_DATE, $updatedDate);
        return $this;
    }

    /**
     * Get product Data
     *
     * @return ProductInterface| CatalogProduct
     */
    public function getProduct()
    {
        if (!$this->getData('product') && $this->getProductId()) {
            $product = $this->productRepositoryFactory->create()->getById(
                $this->getProductId(),
                false,
                $this->getProductStoreId(),
                true
            );
            $this->setData('product', $product);
        }

        return $this->getData('product');
    }

    /**
     * Get last synced date
     *
     * @return string
     */
    public function getLastUpdatedToGoogle()
    {
        return $this->_getData(self::LAST_SYNCED);
    }

    /**
     * Set Last synced date
     *
     * @param string $date
     * @return $this
     */
    public function setLastUpdatedToGoogle($date)
    {
        $this->setData(self::LAST_SYNCED, $date);
        return $this;
    }

    /**
     * Delete product from google
     *
     * @return $this
     */
    public function deleteProductFromGoogle()
    {
        $this->serviceProduct->delete($this);
        return $this;
    }

    /**
     * Update product to google
     *
     * @return $this
     * @throws LocalizedException
     */
    public function updateProductToGoogle()
    {
        if ($this->getGoogleContentId()) {
            $this->serviceProduct->update($this);
        } else {
            $this->serviceProduct->insert($this);
        }

        return $this;
    }

    /**
     * Set Product
     *
     * @param ProductInterface $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->setData(ProductsInterface::PRODUCT, $product);
        return $this;
    }

    /**
     * Load attribute map type by store and enabled target country's
     *
     * Get one attribute map type and keep all in registry to get it before sending
     * data for multiple country's
     *
     * @param array $targetCountry
     * @return AttributeMapTypeInterface|AttributeMapType
     */
    public function getType($targetCountry = [])
    {
        $registry = $this->_registry->registry(self::TYPES_REGISTRY_KEY);
        if (is_array($registry) && isset($registry[$this->getProductStoreId()])) {
            return reset($registry[$this->getProductStoreId()]);
        }

        /** @var  AttributeMapTypeRepository $attributeMapTypeRepository */
        $attributeMapTypeRepository = $this->attributeMapTypeRepositoryFactory->create();
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            'main_table' . '.' . AttributeMapTypeInterface::STORE_ID,
            $this->getProductStoreId()
        )->addFilter(
            AttributeMapTypeInterface::TARGET_COUNTRY,
            $targetCountry,
            'in'
        )->create();
        /** @var AttributeMapTypeSearchResultInterface $attributeMapTypeResult */
        $attributeMapTypeResult = $attributeMapTypeRepository->getList($searchCriteria);
        $types = [];
        if ($attributeMapTypeResult->getTotalCount() > 0) {
            $types = $attributeMapTypeResult->getItems();
            foreach ($types as $mapType) {
                $registry[$this->getProductStoreId()][$mapType->getTargetCountry()] = $mapType;
            }
        } else {
            $types[] = $this->attributeMapTypeFactory->create()->setTargetCountry(array_shift($targetCountry));
        }
        if ($registry) {
            $this->_registry->unregister(self::TYPES_REGISTRY_KEY);
        }
        $this->_registry->register(self::TYPES_REGISTRY_KEY, $registry);

        return reset($types);
    }

    /**
     * Set Expiry date
     *
     * @param string $expiryDate
     * @return $this
     */
    public function setExpiryDate($expiryDate)
    {
        $this->setData(self::EXPIRY_DATE, $expiryDate);
        return $this;
    }

    /**
     * Get Expiry date
     *
     * @return string
     */
    public function getExpiryDate()
    {
        return $this->_getData(self::EXPIRY_DATE);
    }

    /**
     * Set current operation is sync
     *
     * @param bool $isSync
     * @return $this
     */
    public function setIsSync($isSync = true)
    {
        $this->isSync = $isSync;
        return $this;
    }

    /**
     * Get is Sync Operation or product update
     *
     * @return bool
     */
    public function isSync()
    {
        return $this->isSync;
    }

    /**
     * Get item Renew days
     *
     * @return array
     */
    public function getItemRenewNoOfDays()
    {
        return $this->itemRenewDaysBefore;
    }

    /**
     * Get Product Status Array
     *
     * @return array
     */
    public function getProductStatusArray()
    {
        /**
         * Product Status Options
         */
        return [
            self::READY_TO_UPDATE_STATUS => self::READY_TO_UPDATE_LABEL,
            self::UPDATED_STATUS         => self::UPDATED_STATUS_LABEL,
            self::FAILED_STATUS          => self::FAILED_STATUS_LABEL,
            self::SKIPPED_STATUS         => self::SKIPPED_STATUS_LABEL,
            self::DELETED_STATUS         => self::DELETED_STATUS_LABEL,
            self::ERROR_STATUS           => self::ERROR_STATUS_LABEL
        ];
    }
}
