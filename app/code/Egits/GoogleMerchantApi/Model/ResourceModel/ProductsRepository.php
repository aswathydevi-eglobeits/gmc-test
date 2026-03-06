<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model\ResourceModel;

use Egits\GoogleMerchantApi\Api\Data\ProductsInterface;
use Egits\GoogleMerchantApi\Api\Data\ProductsInterfaceFactory;
use Egits\GoogleMerchantApi\Api\Data\ProductSearchResultInterface;
use Egits\GoogleMerchantApi\Api\Data\ProductSearchResultInterfaceFactory;
use Egits\GoogleMerchantApi\Api\ProductsRepositoryInterface;
use Egits\GoogleMerchantApi\Model\ResourceModel\ProductFactory as ProductResourceFactory;
use Egits\GoogleMerchantApi\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class ProductsRepository
 * Repository class for google product
 */
class ProductsRepository extends AbstractRepository implements ProductsRepositoryInterface
{

    /**
     * @var ProductsInterfaceFactory
     */
    protected $productFactory;

    /**
     * @var ProductResourceFactory
     */
    protected $productResourceFactory;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ProductSearchResultInterfaceFactory
     */
    protected $productSearchResultInterfaceFactory;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * ProductsRepository constructor.
     *
     * @param ProductsInterfaceFactory $productFactory
     * @param ProductResourceFactory $productResourceFactory
     * @param CollectionFactory $productCollectionFactory
     * @param ProductSearchResultInterfaceFactory $productSearchResultInterface
     * @param DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        ProductsInterfaceFactory $productFactory,
        ProductResourceFactory $productResourceFactory,
        CollectionFactory $productCollectionFactory,
        ProductSearchResultInterfaceFactory $productSearchResultInterface,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->productFactory = $productFactory;
        $this->productResourceFactory = $productResourceFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productSearchResultInterfaceFactory = $productSearchResultInterface;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * Save Product
     *
     * @param \Egits\GoogleMerchantApi\Api\Data\ProductsInterface $product
     * @return \Egits\GoogleMerchantApi\Api\Data\ProductsInterface
     */
    public function save(ProductsInterface $product)
    {
        $productResource = $this->productResourceFactory->create();
        $this->beforeSave($product);
        $productResource->save($product);
        return $product;
    }

    /**
     * Get product by id
     *
     * @param int $id
     * @param \Egits\GoogleMerchantApi\Api\Data\ProductsInterface|null $product
     * @return \Egits\GoogleMerchantApi\Api\Data\ProductsInterface;
     * @throws \Magento\Framework\Exception\NoSuchEntityException;
     */
    public function loadById($id, ?ProductsInterface $product = null)
    {
        if ($product === null) {
            $product = $this->productFactory->create();
        }

        $this->productResourceFactory->create()->load($product, $id);
        if (!$product->getId()) {
            throw new NoSuchEntityException(__('Unable to find product with ID "%1"', $id));
        }

        return $product;
    }

    /**
     * Get List
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Egits\GoogleMerchantApi\Api\Data\ProductSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->productCollectionFactory->create();
        $this->addFilterToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);
        $collection->load();
        return $this->buildSearchResult($searchCriteria, $collection);
    }

    /**
     * Delete Product
     *
     * @param \Egits\GoogleMerchantApi\Api\Data\ProductsInterface $product
     * @return void
     */
    public function delete(ProductsInterface $product)
    {
        $this->productResourceFactory->create()->delete($product);
    }

    /**
     * Delete By id
     *
     * @param int $id
     * @return void
     */
    public function deleteById($id)
    {
        $this->productResourceFactory->create()->delete($this->loadById($id));
    }

    /**
     * Load Product by id
     *
     * @param int $productId
     * @param int $storeId
     * @return ProductsInterface|\Magento\Framework\DataObject
     */
    public function loadByProductId($productId, $storeId)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter(
            ProductsInterface::STORE_ID,
            ['eq', $storeId]
        )->addFieldToFilter(
            ProductsInterface::PRODUCT_ID,
            ['eq', $productId]
        );
        return $collection->load()->getFirstItem();
    }

    /**
     * Build Search Result Based on collection and search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param AbstractCollection $collection
     * @return ProductSearchResultInterface
     */
    protected function buildSearchResult($searchCriteria, $collection)
    {
        $searchResults = $this->productSearchResultInterfaceFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Before save
     *
     * @param ProductsInterface $product
     * @return ProductsInterface
     */
    private function beforeSave($product)
    {
        $dateTimeFactory = $this->dateTimeFactory->create();
        if (!$product->getId()) {
            $product->setAddedDate($dateTimeFactory->gmtDate());
        }

        if (!$product->isSync()) {
            $product->setUpdatedDate($dateTimeFactory->gmtDate());
        } elseif (in_array(
            $product->getStatus(),
            [ProductsInterface::UPDATED_STATUS, ProductsInterface::DELETED_STATUS]
        )) {
            $product->setLastUpdatedToGoogle($dateTimeFactory->gmtDate());
        }

        return $product;
    }
    /**
     * Batch insert data
     *
     * @param array $data
     * @return int No of rows inserted
     * @throws \Exception
     */
    public function batchInsert(array $data)
    {
        $dateTimeFactory = $this->dateTimeFactory->create();
        $resourceModel = $this->productResourceFactory->create();
        try {
            $connection = $resourceModel->getConnection();
            $tableName = $resourceModel->getMainTable();
            $dataToInsert = [];
            foreach ($data as $item) {
                $dataToInsert[] = [
                    'product_id'       => $item[0],
                    'product_store_id' => $item[1],
                    'status'           => ProductsInterface::READY_TO_UPDATE_STATUS,
                    'added_date' =>  $dateTimeFactory->gmtDate(),
                    'updated_date' => $dateTimeFactory->gmtDate()
                ];
            }
            return $connection->insertMultiple($tableName, $dataToInsert);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
