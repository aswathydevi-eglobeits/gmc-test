<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package    Egits_
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface ProductSearchResultInterface
 * Google product search result interface
 */
interface ProductSearchResultInterface extends SearchResultsInterface
{
    /**
     * Set Items
     *
     * @param \Egits\GoogleMerchantApi\Api\Data\ProductsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * Get items
     *
     * @return \Egits\GoogleMerchantApi\Api\Data\ProductsInterface[]
     */
    public function getItems();
}
