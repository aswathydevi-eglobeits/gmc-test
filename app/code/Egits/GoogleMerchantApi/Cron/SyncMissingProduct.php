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
class SyncMissingProduct extends SyncProduct
{
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
                        $this->synchronizer->batchSynchronizeMissingStoreItems($_storeId);
                    } else {
                        $this->synchronizer->synchronizeMissingStoreItems($_storeId);
                    }
                } catch (Exception $e) {
                    $this->getLogger()->addMajor(
                        __(
                            'An error has occurred while syncing missing/skipped products with google shopping account.'
                        ),
                        __(
                            'One or more products were not synced to google shopping account.
                             Refer to the log file for details.'
                        ),
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
}
