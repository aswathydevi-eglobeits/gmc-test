<?php

namespace Egits\GoogleMerchantApi\Model\ResourceModel\CategoryPriority;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Egits\GoogleMerchantApi\Model\CategoryPriority;
use Egits\GoogleMerchantApi\Model\ResourceModel\CategoryPriority as CategoryPriorityResource;

/**
 * Class Collection
 * Collection class for retrieving a set of CategoryPriority models from the database.
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            CategoryPriority::class,
            CategoryPriorityResource::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
