<?php

namespace Egits\GoogleMerchantApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface CategoryPrioritySearchResultInterface
 */
interface CategoryPrioritySearchResultInterface extends SearchResultsInterface
{
    /**
     * Set items
     *
     * @param CategoryPriorityInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * Get items
     *
     * @return CategoryPriorityInterface[]
     */
    public function getItems();
}
