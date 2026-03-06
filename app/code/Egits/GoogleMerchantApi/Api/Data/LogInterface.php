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

/**
 * Interface LogInterface
 * Log entity interface
 */
interface LogInterface
{
    /**
     * Entity Fields
     */
    public const ENTITY_ID = 'entity_id';
    public const LOG_LEVEL = 'log_level';
    public const STORE_ID = 'store_id';
    public const SYNC_TYPE = 'sync_type';
    public const MESSAGE = 'message';
    public const CREATED_AT = 'date';

    /**
     * Log Levels
     */
    public const INFO = 0;
    public const SUCCESS = 1;
    public const WARNING = 2;
    public const ERROR = 3;
    public const CRITICAL = 4;

    /**
     * Get Entity id
     *
     * @return int
     */
    public function getId();

    /**
     * Get log level level
     *
     * @return int
     */
    public function getLogLevel();

    /**
     * Get Status
     *
     * @return int
     */
    public function getSyncType();

    /**
     * Get Log message
     *
     * @return string
     */
    public function getMessage();

    /**
     * Get  Created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     *  Get store id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set Entity Id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Set Log Level
     *
     * @param int $level
     * @return $this
     */
    public function setLogLevel($level);

    /**
     * Set Sync Type
     *
     * @param int $type
     * @return $this
     */
    public function setSyncType($type);

    /**
     * Set Message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * Set CreatedAt
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Set Store Id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);
}
