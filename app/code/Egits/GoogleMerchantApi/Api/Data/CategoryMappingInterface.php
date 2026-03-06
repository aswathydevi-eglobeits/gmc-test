<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package    Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Api\Data;

/**
 * Interface CategoryMappingInterface
 * Category mapping interface
 */
interface CategoryMappingInterface
{
    /**
     * Entity Fields
     */
    public const ENTITY_ID = 'entity_id';
    public const CATEGORY_ID = 'category_id';
    public const GOOGLE_CATEGORY_ID = 'google_category_id';

    /**
     * Get Entity id
     *
     * @return int
     */
    public function getId();

    /**
     * Get category Id
     *
     * @return int
     */
    public function getCategoryId();

    /**
     * Get google category code
     *
     * @return int
     */
    public function getGoogleCategory();

    /**
     * Set Entity Id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get category Id
     *
     * @param int $id
     * @return $this
     */
    public function setCategoryId($id);

    /**
     * Set Google Attribute
     *
     * @param int $id
     * @return $this
     */
    public function setGoogleCategoryId($id);
}
