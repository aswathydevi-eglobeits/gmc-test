<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model\ResourceModel;

use Egits\GoogleMerchantApi\Api\Data\LogInterface;
use Egits\GoogleMerchantApi\Api\Data\LogInterfaceFactory;
use Egits\GoogleMerchantApi\Api\Data\LogSearchResultInterface;
use Egits\GoogleMerchantApi\Api\Data\LogSearchResultInterfaceFactory;
use Egits\GoogleMerchantApi\Api\LogRepositoryInterface;
use Egits\GoogleMerchantApi\Helper\GoogleHelper;
use Egits\GoogleMerchantApi\Model\Logs;
use Egits\GoogleMerchantApi\Model\ResourceModel\LogsFactory as LogResourceFactory;
use Egits\GoogleMerchantApi\Model\ResourceModel\Logs\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class LogsRepository
 * Repository class for logs
 */
class LogsRepository extends AbstractRepository implements LogRepositoryInterface
{

    /**
     * @var int
     */
    protected $syncType = 0;

    /**
     * @var LogInterfaceFactory
     */
    protected $logFactory;

    /**
     * @var LogResourceFactory
     */
    protected $logResourceFactory;

    /**
     * @var CollectionFactory
     */
    protected $logCollectionFactory;

    /**
     * @var LogSearchResultInterfaceFactory
     */
    protected $logSearchResultInterfaceFactory;

    /**
     * @var DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * @var GoogleHelper
     */
    protected $googleHelper;

    /**
     * ProductsRepository constructor.
     *
     * @param LogInterfaceFactory $logFactory
     * @param LogResourceFactory $logResourceFactory
     * @param CollectionFactory $logCollectionFactory
     * @param LogSearchResultInterfaceFactory $logSearchResultInterface
     * @param DateTimeFactory $dateTimeFactory
     * @param GoogleHelper $googleHelper
     */
    public function __construct(
        LogInterfaceFactory $logFactory,
        LogResourceFactory $logResourceFactory,
        CollectionFactory $logCollectionFactory,
        LogSearchResultInterfaceFactory $logSearchResultInterface,
        DateTimeFactory $dateTimeFactory,
        GoogleHelper $googleHelper
    ) {
        $this->logFactory = $logFactory;
        $this->logResourceFactory = $logResourceFactory;
        $this->logCollectionFactory = $logCollectionFactory;
        $this->logSearchResultInterfaceFactory = $logSearchResultInterface;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->googleHelper = $googleHelper;
    }

    /**
     * Save Log
     *
     * @param  \Egits\GoogleMerchantApi\Api\Data\LogInterface $log
     * @return \Egits\GoogleMerchantApi\Api\Data\LogInterface
     */
    public function save(LogInterface $log)
    {
        $logResource = $this->logResourceFactory->create();
        $logResource->save($log);
        return $log;
    }

    /**
     * Get log by id
     *
     * @param  int $id
     * @param  \Egits\GoogleMerchantApi\Api\Data\LogInterface|null $log
     * @return \Egits\GoogleMerchantApi\Api\Data\LogInterface;
     * @throws \Magento\Framework\Exception\NoSuchEntityException;
     */
    public function loadById($id, ?LogInterface $log = null)
    {
        if ($log === null) {
            $log = $this->logFactory->create();
        }

        $this->logResourceFactory->create()->load($log, $id);
        if (!$log->getId()) {
            throw new NoSuchEntityException(__('Unable to find log with ID "%1"', $id));
        }

        return $log;
    }

    /**
     * Get List
     *
     * @param  \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Egits\GoogleMerchantApi\Api\Data\LogSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->logCollectionFactory->create();
        $this->addFilterToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);
        $collection->load();
        return $this->buildSearchResult($searchCriteria, $collection);
    }

    /**
     * Delete Product
     *
     * @param \Egits\GoogleMerchantApi\Api\Data\LogInterface $log
     * @return void
     */
    public function delete(LogInterface $log)
    {
        $this->logResourceFactory->create()->delete($log);
    }

    /**
     * Delete by id
     *
     * @param  int $id
     * @return void
     */
    public function deleteById($id)
    {
        $this->logResourceFactory->create()->delete($this->loadById($id));
    }

    /**
     * Build Search Result Based on collection and search criteria
     *
     * @param  SearchCriteriaInterface $searchCriteria
     * @param  AbstractCollection $collection
     * @return LogSearchResultInterface
     */
    protected function buildSearchResult($searchCriteria, $collection)
    {
        $searchResults = $this->logSearchResultInterfaceFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Get log level name
     *
     * @param  int $lvl
     * @return string
     */
    public function getLevelName($lvl)
    {
        $levelNames = $this->getLevelNames();
        return (isset($levelNames[$lvl])) ? $levelNames[$lvl] : false;
    }

    /**
     * Get log level names
     *
     * @return array
     */
    public function getLevelNames()
    {
        return [
            LogInterface::INFO     => 'info',
            LogInterface::SUCCESS  => 'success',
            LogInterface::WARNING  => 'warning',
            LogInterface::ERROR    => 'error',
            LogInterface::CRITICAL => 'critical',
        ];
    }

    /**
     * Save log message to db
     *
     * @param  string $message
     * @param  int $lvl
     * @param  int|null $storeId
     * @return LogInterface
     */
    protected function log($message, $lvl = LogInterface::INFO, $storeId = null)
    {
        $model = $this->getLogModel();
        if ($this->googleHelper->getConfig()->isOnDebugMode()) {
            $model->setLogLevel($lvl);
            $model->setMessage($message);
            $model->setStoreId($storeId);
            $model->setSyncType($this->getSyncType());
            $this->beforeSave($model);
            $this->save($model);
        }

        return $model;
    }

    /**
     * Save log message to db
     *
     * @param  string $title
     * @param  array|string $message
     * @param  int|null $storeId
     * @return LogInterface
     */
    public function addSuccess($title, $message, $storeId = null)
    {
        $msg = $this->formatMessage($title, $message);
        return $this->log($msg, Logs::SUCCESS, $storeId);
    }

    /**
     * Save log message to db
     *
     * @param  string $title
     * @param  array|string $message
     * @return LogInterface
     */
    public function addNotice($title, $message)
    {
        $msg = $this->formatMessage($title, $message);
        return $this->log($msg, Logs::INFO);
    }

    /**
     * Save log message to db
     *
     * @param  string $title
     * @param  array|string $message
     * @param  int|null $storeId
     * @return LogInterface|mixed
     */
    public function addMajor($title, $message, $storeId = null)
    {
        $msg = $this->formatMessage($title, $message);
        return $this->log($msg, Logs::ERROR, $storeId);
    }

    /**
     * Format message for output
     *
     * @param  string $title
     * @param  string|array $message
     * @return string
     */
    protected function formatMessage($title, $message)
    {
        if ($message) {
            if (is_array($message)) {
                $message = implode("</br>", $message);
            }

            $message = '<b>' . $title . '</b>' . "</br>" . $message;
        } else {
            $message = $title;
        }

        return $message;
    }

    /**
     * Get log model
     *
     * @return LogInterface
     */
    protected function getLogModel()
    {
        return $this->logFactory->create();
    }

    /**
     * Set Sync type
     *
     * @param int $type
     * @return $this
     */
    public function setSyncType($type)
    {
        $this->syncType = $type;
        return $this;
    }

    /**
     * Set StoreId
     *
     * @param  int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * Get sync Type
     *
     * @return int
     */
    public function getSyncType()
    {
        return $this->syncType;
    }

    /**
     * Get current Time
     *
     * @return string
     */
    protected function getCurrentTime()
    {
        return $this->dateTimeFactory->create()->gmtDate();
    }

    /**
     * Before save
     *
     * @param  LogInterface $logs
     * @return LogInterface
     */
    protected function beforeSave($logs)
    {
        $logs->setCreatedAt($this->getCurrentTime());
        return $logs;
    }
}
