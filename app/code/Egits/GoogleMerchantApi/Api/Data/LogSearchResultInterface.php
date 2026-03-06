<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface LogSearchResultInterface
 * Log search result interface
 */
interface LogSearchResultInterface extends SearchResultsInterface
{
    /**
     * Set items
     *
     * @param \Egits\GoogleMerchantApi\Api\Data\LogInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * Get Items
     *
     * @return \Egits\GoogleMerchantApi\Api\Data\LogInterface[]
     */
    public function getItems();
}
