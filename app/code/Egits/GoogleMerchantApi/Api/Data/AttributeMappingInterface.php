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
 * Interface AttributeMappingInterface
 * Interface for attribute map type
 */
interface AttributeMappingInterface
{
    /**
     * Entity Fields
     */
    public const ENTITY_ID = 'entity_id';
    public const ATTRIBUTE_ID = 'attr_id';
    public const GOOGLE_ATTRIBUTE = 'google_attribute';
    public const TYPE_ID = 'type_id';

    /**
     * Get Entity id
     *
     * @return int
     */
    public function getId();

    /**
     * Get Attribute Id
     *
     * @return int
     */
    public function getAttributeId();

    /**
     * Get google attribute code
     *
     * @return string
     */
    public function getGoogleAttribute();

    /**
     * Get attribute map type id
     *
     * @return int
     */
    public function getTypeId();

    /**
     * Set Entity Id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Set Attribute Map Type id
     *
     * @param int $typeId
     * @return $this
     */
    public function setTypeId($typeId);

    /**
     * Set Google Attribute
     *
     * @param string $code
     * @return $this
     */
    public function setGoogleAttribute($code);
}
