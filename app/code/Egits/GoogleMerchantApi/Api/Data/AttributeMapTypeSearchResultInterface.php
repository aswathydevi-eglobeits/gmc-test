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

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface AttributeMapTypeSearchResultInterface
 * Attribute map type search result interface
 */
interface AttributeMapTypeSearchResultInterface extends SearchResultsInterface
{
    /**
     * Set items
     *
     * @param \Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * Get items
     *
     * @return \Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterface[]
     */
    public function getItems();
}
