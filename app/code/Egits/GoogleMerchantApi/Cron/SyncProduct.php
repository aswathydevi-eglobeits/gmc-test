<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Cron;

use Egits\GoogleMerchantApi\Api\LogRepositoryInterface;
use Egits\GoogleMerchantApi\Logger\Logger;
use Egits\GoogleMerchantApi\Model\Synchronizer;
use Egits\GoogleMerchantApi\Helper\GoogleConfig;
use Egits\GoogleMerchantApi\Helper\GoogleHelper;
use Exception;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Sync Products in queue to google content.
 *
 * Class SyncProduct
 */
class SyncProduct
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var GoogleHelper
     */
    protected $googleHelper;

    /**
     * @var GoogleConfig
     */
    protected $config;

    /**
     * @var Synchronizer
     */
    protected $synchronizer;

    /**
     * @var LogRepositoryInterface
     */
    protected $apiLogger;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * Enable force sync when sync from configuration page
     *
     * @var bool
     */
    public $syncStatus = false;

    /**
     * @var bool
     */
    protected $forceSync = false;

    /**
     * SyncProduct constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param GoogleHelper $googleHelper
     * @param Synchronizer $synchronizer
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        GoogleHelper $googleHelper,
        Synchronizer $synchronizer
    ) {
        $this->storeManager = $storeManager;
        $this->googleHelper = $googleHelper;
        $this->synchronizer = $synchronizer;
    }

    /**
     * Execute cron
     *
     * @return $this
     */
    public function execute()
    {
        $stores = $this->storeManager->getStores();
        $storeIds = array_keys($stores);
        foreach ($storeIds as $_storeId) {
            if ($this->onSchedule($_storeId) || $this->forceSync) {
                if (!$this->getConfig()->isGoogleMerchantApiEnabled($_storeId)) {
                    continue;
                }

                try {
                    if ($this->getConfig()->isEnabledBatchImport($_storeId)) {
                        $this->synchronizer->batchSynchronizeStoreItems($_storeId);
                    } else {
                        $this->synchronizer->synchronizeStoreItems($_storeId);
                    }
                } catch (Exception $e) {
                    $this->getLogger()->addMajor(
                        __('An error has occured while syncing products with google shopping account.'),
                        __('One or more products were not synced to google shopping account.
                             Refer to the log file for details.'),
                        $_storeId
                    );
                    $this->googleHelper->writeDebugLogFile($e, $_storeId);
                    return $this;
                }
            }
        }

        $this->syncStatus = true;
        return $this;
    }

    /**
     * Get Configurations
     *
     * @return GoogleConfig
     */
    protected function getConfig()
    {
        if (!$this->config) {
            $this->config = $this->googleHelper->getConfig();
        }

        return $this->config;
    }

    /**
     * Ger Api logger
     *
     * @return LogRepositoryInterface
     */
    protected function getLogger()
    {
        if (!$this->apiLogger) {
            $this->apiLogger = $this->googleHelper->getApiLogger();
        }

        return $this->apiLogger;
    }

    /**
     * Check is on schedule
     *
     * @param int $storeId
     * @return bool
     */
    protected function onSchedule($storeId)
    {
        $threshold = 24; // Daily
        $config = $this->googleHelper->getConfig();
        if ($config->isCronEnabled($storeId)) {
            switch ($config->getCronFrequency($storeId)) {
                case 'D':
                    $threshold = 24;
                    break;
                case 'M':
                    $threshold = 5040;
                    break;
                case 'W':
                    $threshold = 168;
                    break;
            }

            if ($threshold <= (strtotime('now') - strtotime($this->synchronizer->getLastUpdatedDateOfProduct())) / 3600
                && $this->validateTime($storeId)
            ) {
                return true;
            }
        }

        return true;
    }

    /**
     * Validate time
     *
     * @param int $storeId
     * @return bool
     */
    protected function validateTime($storeId)
    {
        $validate = true;
        $cronTime = $this->googleHelper->getConfig()->getCronTime($storeId);
        $this->localeDate = $this->googleHelper->getTimeZone();
        if (!empty($cronTime)) {
            $mageTime = $this->localeDate->scopeTimeStamp();
            $validate = false;
            $times = explode(",", $cronTime);
            $now = (date("H", $mageTime) * 60) + date("i", $mageTime);

            foreach ($times as $time) {
                if ($now >= $time && $now < $time + 30) {
                    $validate = true;
                    break;
                }
            }
        }

        return $validate;
    }

    /**
     * Set Force sync option,for sync manually
     *
     * @param bool $true
     * @return $this
     */
    public function setForceSync($true = true)
    {
        $this->forceSync = $true;
        return $this;
    }
}
