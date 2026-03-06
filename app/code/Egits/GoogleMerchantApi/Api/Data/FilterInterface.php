<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Api\Data;

/**
 * Interface FilterInterface
 * Filter interface, for product filter condition
 */
interface FilterInterface
{
    /**
     * Entity Fields
     */
    public const FILTER_ID = 'filter_id';
    public const STORE_ID = 'store_id';
    public const FILTER_NAME = 'filter_name';
    public const CONDITION = 'conditions';
    public const IS_ACTIVE = 'is_active';

    /**
     * Filter Status
     */
    public const ENABLED_FILTER = 1;
    public const DISABLED_FILTER = 0;

    /**
     * Get Filter Id
     *
     * @return int
     */
    public function getId();

    /**
     * Get Store Id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Get Filter name
     *
     * @return string
     */
    public function getFilterName();

    /**
     * Get Filter Condition
     *
     * @return string
     */
    public function getConditions();

    /**
     * Get Filter Status
     *
     * @return int
     */
    public function getIsActive();

    /**
     * Set Filter Id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Set store id
     *
     * @param int $id
     * @return $this
     */
    public function setStoreId($id);

    /**
     * Set Filter name
     *
     * @param string $name
     * @return $this
     */
    public function setFilterName($name);

    /**
     * Set Filter condition
     *
     * @param string $condition
     * @return $this
     */
    public function setConditions($condition);

    /**
     * Set Filter status
     *
     * @param int $status
     * @return $this
     */
    public function setIsActive($status);
}
