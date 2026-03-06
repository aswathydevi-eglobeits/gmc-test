<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Api\Data;

/**
 * Interface AttributeMapTypeInterface
 * Interface for attribute map type
 */
interface AttributeMapTypeInterface
{
    /**
     * Entity Fields
     */
    public const TYPE_ID = 'type_id';
    public const NAME = 'name';
    public const TARGET_COUNTRY = 'target_country';
    public const STORE_ID = 'store_id';
    public const ATTRIBUTE_MAP = 'attribute_map';

    /**
     * Get Entity id
     *
     * @return int
     */
    public function getId();

    /**
     * Get Name of mapping type
     *
     * @return string
     */
    public function getName();

    /**
     * Get target country code
     *
     * @return string
     */
    public function getTargetCountry();

    /**
     * Get attribute Mapping
     *
     * @return array
     */
    public function getAttributeMap();

    /**
     * Set Entity Id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Set target Country code
     *
     * @param string $code Country code
     * @return $this
     */
    public function setTargetCountry($code);

    /**
     * Set Store Id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * SetName
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Set Attribute Mapping
     *
     * @param array $attributeMap
     * @return $this
     */
    public function setAttributeMap(array $attributeMap = []);
}
