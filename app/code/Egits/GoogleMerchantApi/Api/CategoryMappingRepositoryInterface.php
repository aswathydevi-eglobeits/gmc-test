<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package    Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Api;

use Egits\GoogleMerchantApi\Api\Data\CategoryMappingInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface CategoryMappingRepositoryInterface
 * Repository for category mapping
 */
interface CategoryMappingRepositoryInterface
{
    /**
     * Save Category Mapping
     *
     * @param \Egits\GoogleMerchantApi\Api\Data\CategoryMappingInterface $mapping
     * @return \Egits\GoogleMerchantApi\Api\Data\CategoryMappingInterface
     */
    public function save(CategoryMappingInterface $mapping);

    /**
     * Get Category Mapping By id
     *
     * @param int $id
     * @param \Egits\GoogleMerchantApi\Api\Data\CategoryMappingInterface|null $mapping
     * @return \Egits\GoogleMerchantApi\Api\Data\ProductsInterface;
     * @throws \Magento\Framework\Exception\NoSuchEntityException;
     */
    public function loadById($id, ?CategoryMappingInterface $mapping = null);

    /**
     * Get Category Mapping List
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Egits\GoogleMerchantApi\Api\Data\CategoryMappingSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Product
     *
     * @param \Egits\GoogleMerchantApi\Api\Data\CategoryMappingInterface $mapping
     * @return void
     */
    public function delete(CategoryMappingInterface $mapping);

    /**
     * Delete By Id
     *
     * @param int $id
     * @return void
     */
    public function deleteById($id);

    /**
     * Save Multiple Rows of data
     *
     * @param array $data
     * @return int
     */
    public function saveMultiple(array $data);
}
