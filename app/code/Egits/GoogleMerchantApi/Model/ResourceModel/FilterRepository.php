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

use Egits\GoogleMerchantApi\Api\Data\FilterInterface;
use Egits\GoogleMerchantApi\Api\Data\FilterSearchResultInterface;
use Egits\GoogleMerchantApi\Api\Data\FilterSearchResultInterfaceFactory;
use Egits\GoogleMerchantApi\Api\FilterRepositoryInterface;
use Egits\GoogleMerchantApi\Model\FilterFactory;
use Egits\GoogleMerchantApi\Model\ResourceModel\Filter\Collection;
use Egits\GoogleMerchantApi\Model\ResourceModel\FilterFactory as FilterResourceFactory;
use Egits\GoogleMerchantApi\Model\ResourceModel\Filter\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class FilterRepository
 * Repository class for filter
 */
class FilterRepository extends AbstractRepository implements FilterRepositoryInterface
{

    /**
     * @var FilterFactory
     */
    protected $filterFactory;

    /**
     * @var FilterResourceFactory
     */
    protected $filterResourceFactory;

    /**
     * @var CollectionFactory
     */
    protected $filterCollectionFactory;

    /**
     * @var FilterSearchResultInterfaceFactory
     */
    protected $filterSearchResultInterfaceFactory;

    /**
     * FilterRepository constructor.
     *
     * @param FilterFactory $filterFactory
     * @param FilterResourceFactory $filterResourceFactory
     * @param CollectionFactory $filterCollectionFactory
     * @param FilterSearchResultInterfaceFactory $filterSearchResultInterface
     */
    public function __construct(
        FilterFactory $filterFactory,
        FilterResourceFactory $filterResourceFactory,
        CollectionFactory $filterCollectionFactory,
        FilterSearchResultInterfaceFactory $filterSearchResultInterface
    ) {
        $this->filterFactory = $filterFactory;
        $this->filterResourceFactory = $filterResourceFactory;
        $this->filterCollectionFactory = $filterCollectionFactory;
        $this->filterSearchResultInterfaceFactory = $filterSearchResultInterface;
    }

    /**
     * Save Filter
     *
     * @param \Egits\GoogleMerchantApi\Api\Data\FilterInterface $filter
     * @return \Egits\GoogleMerchantApi\Api\Data\FilterInterface
     */
    public function save(FilterInterface $filter)
    {
        $filterResource = $this->filterResourceFactory->create();
        $filterResource->save($filter);
        return $filter;
    }

    /**
     * Get Filter by id
     *
     * @param int $id
     * @param \Egits\GoogleMerchantApi\Api\Data\FilterInterface|null $filter
     * @return \Egits\GoogleMerchantApi\Api\Data\FilterInterface;
     * @throws \Magento\Framework\Exception\NoSuchEntityException;
     */
    public function loadFilterById($id, ?FilterInterface $filter = null)
    {
        if ($filter === null) {
            $filter = $this->filterFactory->create();
        }

        $this->filterResourceFactory->create()->load($filter, $id);
        if (!$filter->getId()) {
            throw new NoSuchEntityException(__('Unable to find filter with ID "%1"', $id));
        }

        return $filter;
    }

    /**
     * Get List
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Egits\GoogleMerchantApi\Api\Data\FilterSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->filterCollectionFactory->create();
        $this->addFilterToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);
        $collection->load();
        return $this->buildSearchResult($searchCriteria, $collection);
    }

    /**
     * Delete Filter
     *
     * @param \Egits\GoogleMerchantApi\Api\Data\FilterInterface $filter
     * @return void
     */
    public function delete(FilterInterface $filter)
    {
        $this->filterResourceFactory->create()->delete($filter);
    }

    /**
     * Delete By Id
     *
     * @param int $id
     * @return void
     */
    public function deleteById($id)
    {
        $this->filterResourceFactory->create()->delete($this->loadFilterById($id));
    }

    /**
     * Build Search Result Based on collection and search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param AbstractCollection $collection
     * @return FilterSearchResultInterface
     */
    protected function buildSearchResult($searchCriteria, $collection)
    {

        $searchResults = $this->filterSearchResultInterfaceFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
