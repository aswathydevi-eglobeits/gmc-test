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

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface FilterSearchResultInterface
 * Filter search result interface
 */
interface FilterSearchResultInterface extends SearchResultsInterface
{
    /**
     * Set items
     *
     * @param \Egits\GoogleMerchantApi\Api\Data\FilterInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * Get items
     *
     * @return \Egits\GoogleMerchantApi\Api\Data\FilterInterface[]
     */
    public function getItems();
}
