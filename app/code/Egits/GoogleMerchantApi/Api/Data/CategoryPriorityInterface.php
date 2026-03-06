<?php

namespace Egits\GoogleMerchantApi\Api\Data;

/**
 * Interface CategoryPriorityInterface
 */
interface CategoryPriorityInterface
{

    public const ENTITY_ID = 'entity_id';
    public const CATEGORY_ID = 'category_id';
    public const CATEGORY_PRIORITY = 'priority';
    public const TYPE = 'type';

    /**
     * Get Entity id
     *
     * @return int
     */
    public function getId();

    /**
     * Get category Id
     *
     * @return int
     */
    public function getCategoryId();

    /**
     * Get category priority
     *
     * @return null|int
     */
    public function getCategoryPriority();

    /**
     * Get type (varchar)
     *
     * @return string|null
     */
    public function getType();

    /**
     * Set Entity Id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Set category Id
     *
     * @param int $id
     * @return $this
     */
    public function setCategoryId($id);

    /**
     * Set category priority
     *
     * @param null|int $priority
     * @return $this
     */
    public function setCategoryPriority($priority);

    /**
     * Set type (varchar)
     *
     * @param string|null $type
     * @return $this
     */
    public function setType($type);
}
