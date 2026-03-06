<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model;

use Egits\GoogleMerchantApi\Api\Data\LogInterface;
use Egits\GoogleMerchantApi\Model\ResourceModel\Logs as LogsResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Logs
 * Logs model class
 */
class Logs extends AbstractModel implements LogInterface
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(LogsResource::class);
        $this->setIdFieldName('entity_id');
    }

    /**
     * Get log level level
     *
     * @return int
     */
    public function getLogLevel()
    {
        return $this->_getData(self::LOG_LEVEL);
    }

    /**
     * Get Status
     *
     * @return int
     */
    public function getSyncType()
    {
        return $this->_getData(self::SYNC_TYPE);
    }

    /**
     * Get Log message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->_getData(self::MESSAGE);
    }

    /**
     * Get  Created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->_getData(self::CREATED_AT);
    }

    /**
     * Set Log Level
     *
     * @param int $level
     * @return $this
     */
    public function setLogLevel($level)
    {
        $this->setData(self::LOG_LEVEL, (int)$level);
        return $this;
    }

    /**
     * Set Sync Type
     *
     * @param int $type
     * @return $this
     */
    public function setSyncType($type)
    {
        $this->setData(self::SYNC_TYPE, (int)$type);
        return $this;
    }

    /**
     * Set Message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->setData(self::MESSAGE, $message);
        return $this;
    }

    /**
     * Set CreatedAt
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
        return $this;
    }

    /**
     *  Get store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->_getData(self::STORE_ID);
    }

    /**
     * Set Store Id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->setData(self::STORE_ID, $storeId);
        return $this;
    }
}
