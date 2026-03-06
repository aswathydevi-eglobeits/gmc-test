<?php

namespace Egits\GoogleMerchantApi\Model\ResourceModel;

use Egits\GoogleMerchantApi\Api\CategoryPriorityRepositoryInterface;
use Egits\GoogleMerchantApi\Api\Data\CategoryPriorityInterface;
use Egits\GoogleMerchantApi\Api\Data\CategoryPriorityInterfaceFactory;
use Egits\GoogleMerchantApi\Api\Data\CategoryPrioritySearchResultInterface;
use Egits\GoogleMerchantApi\Api\Data\CategoryPrioritySearchResultInterfaceFactory;
use Egits\GoogleMerchantApi\Model\ResourceModel\CategoryPriorityFactory as CategoryPriorityResourceFactory;
use Egits\GoogleMerchantApi\Model\CategoryPriorityFactory;
use Egits\GoogleMerchantApi\Model\ResourceModel\CategoryPriority;
use Egits\GoogleMerchantApi\Model\ResourceModel\CategoryPriority\CollectionFactory ;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Exception;

/**
 * Class CategoryPriorityRepository
 * Repository class for managing Category Priority entities.
 */
class CategoryPriorityRepository implements CategoryPriorityRepositoryInterface
{
    /**
     * @var CategoryPriorityInterfaceFactory
     */
    protected CategoryPriorityInterfaceFactory $categoryPriorityFactory;
    /**
     * @var CategoryPriorityResourceFactory
     */
    protected CategoryPriorityResourceFactory $categoryPriorityResourceFactory;
    /**
     * @var CategoryPriorityFactory
     */
    protected CategoryPriorityFactory $categoryPriorityModelFactory;
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $categoryPriorityCollectionFactory;
    /**
     * @var CategoryPrioritySearchResultInterfaceFactory
     */
    protected CategoryPrioritySearchResultInterfaceFactory $categoryPrioritySearchResultFactory;
    /**
     * @var CollectionProcessorInterface
     */
    protected CollectionProcessorInterface $collectionProcessor;
    /**
     * @var CategoryPriority
     */
    private CategoryPriority $categoryPriority;

    /**
     * CategoryPriorityRepository constructor.
     *
     * @param CategoryPriorityInterfaceFactory $categoryPriorityFactory
     * @param CategoryPriorityResourceFactory $categoryPriorityResourceFactory
     * @param CategoryPriorityFactory $categoryPriorityModelFactory
     * @param CollectionFactory $categoryPriorityCollectionFactory
     * @param CategoryPrioritySearchResultInterfaceFactory $categoryPrioritySearchResultFactory
     * @param CategoryPriority $categoryPriority
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        CategoryPriorityInterfaceFactory $categoryPriorityFactory,
        CategoryPriorityResourceFactory $categoryPriorityResourceFactory,
        CategoryPriorityFactory $categoryPriorityModelFactory,
        CollectionFactory $categoryPriorityCollectionFactory,
        CategoryPrioritySearchResultInterfaceFactory $categoryPrioritySearchResultFactory,
        CategoryPriority $categoryPriority,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->categoryPriorityFactory = $categoryPriorityFactory;
        $this->categoryPriorityResourceFactory = $categoryPriorityResourceFactory;
        $this->categoryPriorityModelFactory = $categoryPriorityModelFactory;
        $this->categoryPriorityCollectionFactory = $categoryPriorityCollectionFactory;
        $this->categoryPrioritySearchResultFactory = $categoryPrioritySearchResultFactory;
        $this->categoryPriority = $categoryPriority;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save a CategoryPriority entity.
     *
     * @param CategoryPriorityInterface $categoryPriority
     * @return CategoryPriorityInterface
     */
    public function save(CategoryPriorityInterface $categoryPriority): CategoryPriorityInterface
    {
        $this->categoryPriorityResourceFactory->save($categoryPriority);
        return $categoryPriority;
    }

    /**
     * Retrieve a CategoryPriority entity by its ID.
     *
     * @param int $id
     * @return CategoryPriorityInterface|\Egits\GoogleMerchantApi\Model\CategoryPriority
     */
    public function getById($id)
    {
        $categoryPriorityObject = $this->categoryPriorityModelFactory->create();
        $this->categoryPriority->load($categoryPriorityObject, $id);
        return $categoryPriorityObject;
    }

    /**
     * Retrieve a list of CategoryPriority entities based on search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return CategoryPrioritySearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->categoryPriorityCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->categoryPrioritySearchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete a CategoryPriority entity.
     *
     * @param CategoryPriorityInterface $categoryPriority
     * @return bool|void
     * @throws CouldNotDeleteException
     */
    public function delete(CategoryPriorityInterface $categoryPriority)
    {
        try {
            $this->categoryPriorityResourceFactory->delete($categoryPriority);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        }
        return true;
    }

    /**
     * Delete a CategoryPriority entity by its ID.
     *
     * @param int $id
     * @return bool|void
     * @throws CouldNotDeleteException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    /**
     * Save multiple CategoryPriority records at once.
     *
     * @param array $data
     * @return int|mixed
     * @throws Exception
     */
    public function saveMultiple(array $data)
    {
        $resourceModel = $this->categoryPriorityResourceFactory->create();
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
