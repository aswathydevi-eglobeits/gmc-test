<?php

namespace Egits\GoogleMerchantApi\Model;

use Egits\GoogleMerchantApi\Api\Data\CategoryPriorityInterface;
use Egits\GoogleMerchantApi\Model\ResourceModel\CategoryPriority as CategoryPriorityResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class CategoryPriority
 *
 * Represents a category priority entity with properties such as category ID,
 * category priority, and type. Provides getter and setter methods to access and
 * manipulate these properties.
 */
class CategoryPriority extends AbstractModel implements CategoryPriorityInterface
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init(CategoryPriorityResource::class);
        $this->setIdFieldName('entity_id');
    }

    /**
     * Get category ID
     *
     * @return int
     */
    public function getCategoryId()
    {
        return (int) $this->_getData(self::CATEGORY_ID);
    }

    /**
     * Get category priority
     *
     * @return int|null
     */
    public function getCategoryPriority()
    {
        $data = $this->_getData(self::CATEGORY_PRIORITY);
        return $data !== null ? (int) $data : null;
    }

    /**
     * Get type (varchar)
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->_getData(self::TYPE);
    }

    /**
     * Set category ID
     *
     * @param int $id
     * @return $this
     */
    public function setCategoryId($id)
    {
        return $this->setData(self::CATEGORY_ID, (int) $id);
    }

    /**
     * Set category priority
     *
     * @param int|null $priority
     * @return $this
     */
    public function setCategoryPriority($priority)
    {
        return $this->setData(self::CATEGORY_PRIORITY, $priority !== null ? (int) $priority : null);
    }

    /**
     * Set type (varchar)
     *
     * @param string|null $type
     * @return $this
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }
}
