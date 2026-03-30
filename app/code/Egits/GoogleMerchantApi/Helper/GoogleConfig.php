<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Store\Model\ScopeInterface;

/**
 * Class GoogleConfig
 * Google merchant api store config reader
 */
class GoogleConfig
{
    /**
     * System config path.
     */
    public const XML_SECTION_PATH = 'egits_google_merchant';
    public const XML_GENERAL_GROUP_PATH = 'general';
    public const XML_GOOGLE_API_CREDENTIALS_GROUP_PATH = 'api_credentials';
    public const XML_CRON_SCHEDULES_GROUP_PATH = 'cron_schedules';
    public const XML_PATH_ENABLE = 'enable';
    public const XML_PATH_DEBUG = 'debug';
    public const XML_PATH_REMOVE_DISABLED = 'remove_disabled';
    public const XML_PATH_TARGET_COUNTRY = 'target_country';
    public const XML_PATH_TARGET_USE_STORE_CODE_IN_URL = 'include_store_code_in_url';
    public const XML_PATH_DEFAULT_GOOGLE_CATEGORY = 'default_google_category';
    public const XML_PATH_DEFAULT_CONTENT_LANGUAGE = 'default_content_language';
    public const XML_PATH_ENABLE_BATCH_SYNC = 'enable_batch_import';
    public const XML_PATH_BATCH_SIZE = 'batch_size';
    public const XML_PATH_ACCOUNT_ID = 'account_id';
    public const XML_PATH_DATA_SOURCE_ID = 'data_source_id';
    public const XML_PATH_DESTINATION_EXCLUDE = 'destination_exclude';
    public const XML_PATH_SERVICE_ACCOUNT_FILE = 'egits_google_merchant/api_credentials/merchant_service_account_file';
    public const XML_PATH_ENABLE_CRON = 'enable_cron';
    public const XML_PATH_CRON_FREQUENCY = 'frequency';
    public const XML_PATH_CRON_TIME = 'time';
    public const XML_PATH_INTERNAL_SYNC_BATCH_SIZE = 'sync_batch_size';
    public const CONFIG_PATH_FOR_SET_PRODUCT_SYNC_DONE = 'egits_google_merchant/general/is_products_added_to_queue';
    public const XML_PWA_GROUP_PATH = 'pwa';
    public const XML_PATH_PWA_URL_ENABLED = 'pwa_url_enabled';
    public const XML_PATH_PWA_URL = 'pwa_url';
    public const CONFIG_PATH_FOR_SET_PRODUCT_SYNC_PROGRESS = 'egits_google_merchant/general/is_queue_in_progress';
    public const CONFIG_PATH_FOR_INTERNAL_QUEUE_STARTED = 'egits_google_merchant/general/internal_queue_started';

    /**
     * Service account json file folder name
     */
    public const ACCOUNT_JSON_DIR = 'googlemerchant/';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * GoogleConfig constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param DirectoryList $directoryList
     */
    public function __construct(ScopeConfigInterface $scopeConfig, DirectoryList $directoryList)
    {
        $this->scopeConfig = $scopeConfig;
        $this->directoryList = $directoryList;
    }

    /**
     * Get General Config values
     *
     * @param null|string $cfg
     * @param null|int|string $storeId
     * @return mixed
     */
    public function getGeneralCfg($cfg = null, $storeId = null)
    {
        $config = $this->scopeConfig->getValue(
            self::XML_SECTION_PATH . '/' . self::XML_GENERAL_GROUP_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (isset($config[$cfg])) {
            return $config[$cfg];
        }

        return null;
    }

    /**
     * Get Api Credentials Config values
     *
     * @param null|string $cfg
     * @param null|int|string $storeId
     * @return string
     */
    public function getApiCfg($cfg = null, $storeId = null)
    {
        $config = $this->scopeConfig->getValue(
            self::XML_SECTION_PATH . '/' . self::XML_GOOGLE_API_CREDENTIALS_GROUP_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (isset($config[$cfg])) {
            return $config[$cfg];
        }

        return null;
    }

    /**
     * Get cron config
     *
     * @param null|string $cfg
     * @param null|int|string $storeId
     * @return mixed
     */
    public function getCronCfg($cfg = null, $storeId = null)
    {
        $config = $this->scopeConfig->getValue(
            self::XML_SECTION_PATH . '/' . self::XML_CRON_SCHEDULES_GROUP_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (isset($config[$cfg])) {
            return $config[$cfg];
        }

        return null;
    }

    /**
     * Get PWA Url configurations
     *
     * @param int|null $cfg
     * @param int|null $storeId
     * @return mixed|null
     */
    public function getPwaCfg($cfg = null, $storeId = null)
    {
        $config = $this->scopeConfig->getValue(
            self::XML_SECTION_PATH . '/' . self::XML_PWA_GROUP_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (isset($config[$cfg])) {
            return $config[$cfg];
        }

        return null;
    }

    /**
     * Is google merchant api enabled
     *
     * @param null|int $storeId
     * @return mixed
     */
    public function isGoogleMerchantApiEnabled($storeId = null)
    {
        return $this->getGeneralCfg(self::XML_PATH_ENABLE, $storeId);
    }

    /**
     * Is auto remove disabled product
     *
     * @param null|int $storeId
     * @return mixed
     */
    public function getAutoRemoveDisabled($storeId = null)
    {
        return $this->getGeneralCfg(self::XML_PATH_REMOVE_DISABLED, $storeId);
    }

    /**
     * Is on debug mode
     *
     * @param null|int $storeId
     * @return mixed
     */
    public function isOnDebugMode($storeId = null)
    {
        return $this->getGeneralCfg(self::XML_PATH_DEBUG, $storeId);
    }

    /**
     * Get all enabled target country
     *
     * @param null|int $storeId
     * @return array
     */
    public function getEnabledTargetCountry($storeId = null)
    {
        $targetCountry = $this->getGeneralCfg(self::XML_PATH_TARGET_COUNTRY, $storeId);
        return explode(',', $targetCountry);
    }

    /**
     * Get default google category
     *
     * @param null|int $storeId
     * @return int
     */
    public function getDefaultGoogleCategory($storeId = null)
    {
        return (int)$this->getGeneralCfg(self::XML_PATH_DEFAULT_GOOGLE_CATEGORY, $storeId);
    }

    /**
     * Is batch import enabled
     *
     * @param null|int $storeId
     * @return mixed
     */
    public function isEnabledBatchImport($storeId = null)
    {
        return $this->getGeneralCfg(self::XML_PATH_ENABLE_BATCH_SYNC, $storeId);
    }

    /**
     * Get batch size,default value 50.
     *
     * @param null|int $storeId
     * @return mixed
     */
    public function getBatchSize($storeId = null)
    {
        return $this->getGeneralCfg(self::XML_PATH_BATCH_SIZE, $storeId);
    }

    /**
     * Get batch size,default value 50.
     *
     * @param null|int $storeId
     * @return mixed
     */
    public function getProductSyncBatchSize($storeId = null)
    {
        return $this->getApiCfg(self::XML_PATH_INTERNAL_SYNC_BATCH_SIZE, $storeId);
    }

    /**
     * Get merchant account id
     *
     * @param null|int $storeId
     * @return mixed
     */
    public function getGoogleMerchantAccountId($storeId = null)
    {
        return $this->getApiCfg(self::XML_PATH_ACCOUNT_ID, $storeId);
    }

    /**
     * Get Merchant API Data Source ID
     *
     * Content API used a feed/merchant ID pair.
     * Merchant API v1 requires a Data Source ID to insert products.
     * @param null|int $storeId
     * @return mixed
     */
    public function getDataSourceId($storeId = null)
    {
        return $this->getApiCfg(self::XML_PATH_DATA_SOURCE_ID, $storeId);
    }

    /**
     * Get service account json file name
     *
     * @param int|null $storeId
     * @return string
     */
    public function getGoogleJsonFile($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SERVICE_ACCOUNT_FILE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is cron enabled
     *
     * @param null|int $storeId
     * @return bool
     */
    public function isCronEnabled($storeId = null)
    {
        return $this->getCronCfg(self::XML_PATH_ENABLE_CRON, $storeId);
    }

    /**
     * Get cron frequency
     *
     * @param null|int $storeId
     * @return string
     */
    public function getCronFrequency($storeId = null)
    {
        return $this->getCronCfg(self::XML_PATH_CRON_FREQUENCY, $storeId);
    }

    /**
     * Get cron time.
     *
     * @param null|int $storeId
     * @return string
     */
    public function getCronTime($storeId = null)
    {
        return $this->getCronCfg(self::XML_PATH_CRON_TIME, $storeId);
    }

    /**
     * Get use store in url default.
     *
     * @param null|int $storeId
     * @return bool
     */
    public function getUseStoreUrlDefault($storeId = null)
    {
        return $this->scopeConfig->isSetFlag('web/url/use_store', ScopeInterface::SCOPE_STORES, $storeId);
    }

    /**
     * Get use store url.
     *
     * @param null|int $storeId
     * @return bool
     */
    public function getAddStoreCodeToUrl($storeId = null)
    {
        return $this->getGeneralCfg(self::XML_PATH_TARGET_USE_STORE_CODE_IN_URL, $storeId);
    }

    /**
     * Get PWA url from configurations
     *
     * @param int|null $storeId
     * @return bool|string
     */
    public function getPwaUrl($storeId = null)
    {
        $isPwaUrlEnabled = $this->getPwaCfg(self::XML_PATH_PWA_URL_ENABLED, $storeId);
        if (!$isPwaUrlEnabled) {
            return false;
        }

        $pwaUrl = trim($this->getPwaCfg(self::XML_PATH_PWA_URL, $storeId));
        if (!$pwaUrl) {
            return false;
        }

        return $pwaUrl;
    }

    /**
     * Get add utm source in feed.
     *
     * @return bool
     */
    public function getAddUtmSourceGoogleShopping()
    {
        return true;
    }

    /**
     * Get all destination included.
     *
     * @param null|int $storeId
     * @return array
     */
    public function getDestinationExclude($storeId = null)
    {
        $values = $this->getApiCfg(self::XML_PATH_DESTINATION_EXCLUDE, $storeId);
        if ($values && $values !== '') {
            return explode(',', $values);
        }

        return [];
    }

    /**
     * Get account json file full path.
     *
     * @param int|null $storeId
     * @return string
     * @throws FileSystemException
     */
    public function getAccountJsonFullFilePath($storeId = null)
    {
        $googleConfig = $this->getGoogleJsonFile($storeId);
        return $this->directoryList->getPath('var') . DIRECTORY_SEPARATOR . self::ACCOUNT_JSON_DIR . $googleConfig;
    }

    /**
     * Check if product already synced to queue,if yes disable button
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isProductsSyncedToQueue($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_PATH_FOR_SET_PRODUCT_SYNC_DONE, 'store', $storeId);
    }

    /**
     * TODO remove this flag condition or change it.
     * Is Configurable parent allowed to sync to google
     * For best practise of google ,we disallow sending parent product as separate product
     * instead use item_group_id as parent sku in child
     *
     * @return bool
     */
    public function isAllowedConfigurableParent()
    {
        return true;
    }

    /**
     * Get default content language.
     *
     * @param int|null $storeId
     * @return mixed
     */
    public function getDefaultContentLanguage($storeId = null)
    {
        return $this->getGeneralCfg(self::XML_PATH_DEFAULT_CONTENT_LANGUAGE, $storeId);
    }
}
