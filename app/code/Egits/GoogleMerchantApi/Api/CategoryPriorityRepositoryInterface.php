<?php

namespace Egits\GoogleMerchantApi\Api;

use Egits\GoogleMerchantApi\Api\Data\CategoryPriorityInterface;
use Egits\GoogleMerchantApi\Api\Data\CategoryPrioritySearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface CategoryPriorityRepositoryInterface
 * Defines CRUD and bulk operations for Category Priority entities
 */
interface CategoryPriorityRepositoryInterface
{

    /**
     * Save Category Priority
     *
     * @param CategoryPriorityInterface $categoryPriority
     * @return mixed
     */
    public function save(CategoryPriorityInterface $categoryPriority);

    /**
     * Load Category Priority by ID
     *
     * @param int $id
     * @return mixed
     */
    public function getById($id);

    /**
     * Get Category Priority List
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Category Priority
     *
     * @param CategoryPriorityInterface $categoryPriority
     * @return mixed
     */
    public function delete(CategoryPriorityInterface $categoryPriority);

    /**
     * Delete Category Priority by ID
     *
     * @param int $id
     * @return void
     */
    public function deleteById($id);

    /**
     * Save multiple category priority records.
     *
     * @param array $data
     * @return mixed
     */
    public function saveMultiple(array $data);
}
