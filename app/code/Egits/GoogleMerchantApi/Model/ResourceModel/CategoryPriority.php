<?php

namespace Egits\GoogleMerchantApi\Model\ResourceModel;

use Egits\GoogleMerchantApi\Api\Data\CategoryPriorityInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

/**
 * Class CategoryPriority
 * Handles database operations for the CategoryPriority entity.
 */
class CategoryPriority extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('egits_google_category_priority', CategoryPriorityInterface::ENTITY_ID);
    }
}
