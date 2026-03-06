<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Api;

use Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface AttributeMapTypeRepositoryInterface
 * Repository interface for attribute map
 */
interface AttributeMapTypeRepositoryInterface
{
    /**
     * Save attribute Mapping
     *
     * @param \Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterface $attribute
     * @return \Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterface
     */
    public function save(AttributeMapTypeInterface $attribute);

    /**
     * Get attribute mapping by id
     *
     * @param int $id
     * @param \Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterface|null $attributeMapType
     * @return \Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException;
     */
    public function getAttributeMapTypeById($id, ?AttributeMapTypeInterface $attributeMapType = null);

    /**
     * Get Attribute Mapping List
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Attribute mapping
     *
     * @param \Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterface $attribute
     * @return void
     */
    public function delete(AttributeMapTypeInterface $attribute);

    /**
     * Delete By id
     *
     * @param int $id
     * @return void
     */
    public function deleteById($id);
}
