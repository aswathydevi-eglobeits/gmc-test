<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Api;

use Egits\GoogleMerchantApi\Api\Data\FilterInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface FilterRepositoryInterface
 * Repository for filter
 */
interface FilterRepositoryInterface
{
    /**
     * Save Filter
     *
     * @param \Egits\GoogleMerchantApi\Api\Data\FilterInterface $filter
     * @return \Egits\GoogleMerchantApi\Api\Data\FilterInterface
     */
    public function save(FilterInterface $filter);

    /**
     * Get Filter by id
     *
     * @param int $id
     * @param \Egits\GoogleMerchantApi\Api\Data\FilterInterface|null $filter
     * @return \Egits\GoogleMerchantApi\Api\Data\FilterInterface;
     * @throws \Magento\Framework\Exception\NoSuchEntityException;
     */
    public function loadFilterById($id, ?FilterInterface $filter = null);

    /**
     * Get Filter List
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Egits\GoogleMerchantApi\Api\Data\FilterSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Filter
     *
     * @param \Egits\GoogleMerchantApi\Api\Data\FilterInterface $filter
     * @return void
     */
    public function delete(FilterInterface $filter);

    /**
     * Delete By Id
     *
     * @param int $id
     * @return void
     */
    public function deleteById($id);
}
