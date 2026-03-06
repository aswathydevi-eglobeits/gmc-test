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

use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Class AbstractRepository
 * Base class for repository implementation
 */
abstract class AbstractRepository
{
    /**
     * Add Filter to collection
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param AbstractCollection $collection
     */
    protected function addFilterToCollection($searchCriteria, $collection)
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $fields[] = $filter->getField();
                $conditions[] = [$filter->getConditionType() => $filter->getValue()];
            }

            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * Add sort order to collection
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param AbstractCollection $collection
     */
    protected function addSortOrdersToCollection($searchCriteria, $collection)
    {
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $direction = $sortOrder->getDirection() == SortOrder::SORT_ASC ? 'asc' : 'desc';
            $collection->addOrder($sortOrder->getField(), $direction);
        }
    }

    /**
     * Add paging to collection
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param AbstractCollection $collection
     */
    protected function addPagingToCollection($searchCriteria, $collection)
    {
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
    }

    /**
     * Build Search Result Based on collection and search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param AbstractCollection $collection
     * @return SearchResultsInterface
     */
    abstract protected function buildSearchResult($searchCriteria, $collection);
}
