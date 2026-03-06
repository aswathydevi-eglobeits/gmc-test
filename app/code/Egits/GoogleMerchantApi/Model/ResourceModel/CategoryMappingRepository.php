<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model\ResourceModel;

use Egits\GoogleMerchantApi\Api\CategoryMappingRepositoryInterface;
use Egits\GoogleMerchantApi\Api\Data\CategoryMappingInterface;
use Egits\GoogleMerchantApi\Api\Data\CategoryMappingInterfaceFactory;
use Egits\GoogleMerchantApi\Api\Data\CategoryMappingSearchResultInterface;
use Egits\GoogleMerchantApi\Api\Data\CategoryMappingSearchResultInterfaceFactory;
use Egits\GoogleMerchantApi\Model\ResourceModel\CategoryMappingFactory as MappingResourceFactory;
use Egits\GoogleMerchantApi\Model\ResourceModel\CategoryMapping\CollectionFactory;
use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class CategoryMappingRepository
 * Repository class for category mapping
 */
class CategoryMappingRepository extends AbstractRepository implements CategoryMappingRepositoryInterface
{

    /**
     * @var CategoryMappingInterfaceFactory
     */
    protected $mappingFactory;

    /**
     * @var MappingResourceFactory
     */
    protected $mappingResourceFactory;

    /**
     * @var CollectionFactory
     */
    protected $mappingCollectionFactory;

    /**
     * @var CategoryMappingSearchResultInterface
     */
    protected $mappingSearchResultInterfaceFactory;

    /**
     * ProductsRepository constructor.
     *
     * @param CategoryMappingInterfaceFactory $mappingFactory
     * @param MappingResourceFactory $mappingResourceFactory
     * @param CollectionFactory $mappingCollectionFactory
     * @param CategoryMappingSearchResultInterfaceFactory $mappingSearchResultInterface
     */
    public function __construct(
        CategoryMappingInterfaceFactory $mappingFactory,
        MappingResourceFactory $mappingResourceFactory,
        CollectionFactory $mappingCollectionFactory,
        CategoryMappingSearchResultInterfaceFactory $mappingSearchResultInterface
    ) {
        $this->mappingFactory = $mappingFactory;
        $this->mappingResourceFactory = $mappingResourceFactory;
        $this->mappingCollectionFactory = $mappingCollectionFactory;
        $this->mappingSearchResultInterfaceFactory = $mappingSearchResultInterface;
    }

    /**
     * Save Mapping
     *
     * @param CategoryMappingInterface|AbstractModel $mapping
     * @return CategoryMappingInterface
     * @throws AlreadyExistsException
     */
    public function save(CategoryMappingInterface $mapping)
    {
        $productResource = $this->mappingResourceFactory->create();
        $productResource->save($mapping);
        return $mapping;
    }

    /**
     * Get mapping by id
     *
     * @param int $id
     * @param CategoryMappingInterface|AbstractModel|null $mapping
     * @return CategoryMappingInterface|AbstractModel;
     * @throws NoSuchEntityException;
     */
    public function loadById($id, ?CategoryMappingInterface $mapping = null)
    {
        if ($mapping === null) {
            $mapping = $this->mappingFactory->create();
        }

        $this->mappingResourceFactory->create()->load($mapping, $id);
        if (!$mapping->getId()) {
            throw new NoSuchEntityException(__('Unable to find product with ID "%1"', $id));
        }

        return $mapping;
    }

    /**
     * Get category Mapping list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return CategoryMappingSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->mappingCollectionFactory->create();
        $this->addFilterToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);
        $collection->load();
        return $this->buildSearchResult($searchCriteria, $collection);
    }

    /**
     * Delete mapping
     *
     * @param CategoryMappingInterface|AbstractModel $mapping
     * @return void
     * @throws Exception
     */
    public function delete(CategoryMappingInterface $mapping)
    {
        $this->mappingResourceFactory->create()->delete($mapping);
    }

    /**
     * Delete Mapping by id
     *
     * @param int $id
     * @return void
     * @throws Exception
     */
    public function deleteById($id)
    {
        try {
            $categoryMapping = $this->loadById($id);
            if ($categoryMapping->getId()) {
                $this->mappingResourceFactory->create()->delete($categoryMapping);
            }
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Build Search Result Based on collection and search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param AbstractCollection $collection
     * @return CategoryMappingSearchResultInterface
     */
    protected function buildSearchResult($searchCriteria, $collection)
    {
        $searchResults = $this->mappingSearchResultInterfaceFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Save Mapping data after truncating table at once
     *
     * @param array $data
     * @return int No of rows inserted
     * @throws Exception
     */
    public function saveMultiple(array $data)
    {
        $resourceModel = $this->mappingResourceFactory->create();
        try {
            $connection = $resourceModel->getConnection();
            $tableName = $resourceModel->getMainTable();
            $connection->truncateTable($tableName);
            return $connection->insertMultiple($tableName, $data);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
