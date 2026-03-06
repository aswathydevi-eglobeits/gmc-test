<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Api;

use Egits\GoogleMerchantApi\Api\Data\LogInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface LogRepositoryInterface
 * Log repository interface
 */
interface LogRepositoryInterface
{
    /**
     * Save Log
     *
     * @param \Egits\GoogleMerchantApi\Api\Data\LogInterface $log
     * @return \Egits\GoogleMerchantApi\Api\Data\LogInterface
     */
    public function save(LogInterface $log);

    /**
     * Get Log by Id
     *
     * @param int $id
     * @param \Egits\GoogleMerchantApi\Api\Data\LogInterface|null $log
     * @return \Egits\GoogleMerchantApi\Api\Data\LogInterface;
     * @throws \Magento\Framework\Exception\NoSuchEntityException;
     */
    public function loadById($id, ?LogInterface $log = null);

    /**
     *  Get list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Egits\GoogleMerchantApi\Api\Data\LogSearchResultInterface
     */

    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Log
     *
     * @param \Egits\GoogleMerchantApi\Api\Data\LogInterface $log
     * @return void
     */
    public function delete(LogInterface $log);

    /**
     * Delete Log by id
     *
     * @param int $id
     * @return void
     */
    public function deleteById($id);

    /**
     * Add Success Entry
     *
     * @param string $title
     * @param string|array $message
     * @return \Egits\GoogleMerchantApi\Api\Data\LogInterface
     */
    public function addSuccess($title, $message);

    /**
     * Add Notice entry
     *
     * @param string $title
     * @param string|array $message
     * @return \Egits\GoogleMerchantApi\Api\Data\LogInterface
     */
    public function addNotice($title, $message);

    /**
     * Add major entry
     *
     * @param string $title
     * @param string|array $message
     * @param int|null $storeId
     * @return mixed
     */
    public function addMajor($title, $message, $storeId = null);

    /**
     * Set Sync type
     *
     * @param int $type
     * @return $this
     */
    public function setSyncType($type);

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get Sync type
     *
     * @return int
     */
    public function getSyncType();
}
