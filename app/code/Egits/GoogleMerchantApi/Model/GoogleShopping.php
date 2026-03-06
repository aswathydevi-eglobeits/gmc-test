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

use Egits\GoogleMerchantApi\Api\LogRepositoryInterface;
use Egits\GoogleMerchantApi\Helper\GoogleConfig;
use Egits\GoogleMerchantApi\Helper\GoogleHelper;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Class GoogleShopping
 * Class interact with google api library
 */
class GoogleShopping
{
    /**
     * App name and scope
     */
    public const APP_NAME = 'Magento 2 Shopping';
    public const SCOPE = 'https://www.googleapis.com/auth/content';

    /**
     * Helper google
     *
     * @var GoogleHelper
     */
    protected $googleHelper;

    /**
     * Google api client
     *
     * @var \Google_Client
     */
    protected $client;

    /**
     * Google configuration
     *
     * @var GoogleConfig
     */
    private $googleConfig;

    /**
     * Google shopping service
     *
     * @var \Google_Service_ShoppingContent
     */
    protected $shoppingService;

    /**
     * Service account js file path.
     *
     * @var string
     */
    private $serviceAccountJsonFile;

    /**
     * Stores id
     *
     * @var int
     */
    protected $storeId;

    /**
     * @var File
     */
    protected $fileDriver;

    /**
     * GoogleShopping constructor.
     *
     * @param GoogleHelper $googleHelper
     * @param File $fileDriver
     */
    public function __construct(GoogleHelper $googleHelper, File $fileDriver)
    {
        $this->googleHelper = $googleHelper;
        $this->serviceAccountJsonFile = $this->googleHelper->getConfig()->getAccountJsonFullFilePath();
        $this->fileDriver = $fileDriver;
    }

    /**
     * Get Google api client.
     *
     * @return \Google_Client
     */
    public function getClient()
    {
        if ($this->storeId) {
            $this->serviceAccountJsonFile = $this->googleHelper->getConfig()
                ->getAccountJsonFullFilePath($this->storeId);
        }

        if (isset($this->client)) {
            if ($this->client->isAccessTokenExpired()) {
                $accessToken = $this->client->getAccessToken();
                $this->client->setAccessToken($accessToken);
                $this->client->fetchAccessTokenWithAssertion();
            }

            return $this->client;
        }

        $client = new \Google_Client();
        $client->setApplicationName(self::APP_NAME);
        $client->setAuthConfig($this->serviceAccountJsonFile);
        $client->setScopes([self::SCOPE]);
        if ($client->isAccessTokenExpired()) {
            $accessToken = $client->getAccessToken();
            if ($accessToken) {
                $client->setAccessToken($accessToken);
            }

            $client->fetchAccessTokenWithAssertion();
        }

        $this->client = $client;
        return $this->client;
    }

    /**
     * Get shopping content service
     *
     * @return \Google_Service_ShoppingContent shopping client
     */
    public function getShoppingService()
    {
        if (isset($this->shoppingService)) {
            return $this->shoppingService;
        }

        try {
            $isEnabledForStore = $this->googleHelper->getConfig()->isGoogleMerchantApiEnabled($this->storeId);
            $accountJsonPath = $this->googleHelper->getConfig()->getAccountJsonFullFilePath($this->storeId);

            if ($isEnabledForStore && $this->fileDriver->isExists($accountJsonPath)) {
                $this->shoppingService = new \Google_Service_ShoppingContent($this->getClient());
            }
        } catch (\Exception $exception) {
            $this->googleHelper->writeDebugLogFile($exception);
        }

        return $this->shoppingService;
    }

    /**
     * Insert product
     *
     * @param \Google_Service_ShoppingContent_Product $product
     * @param integer $storeId
     * @return \Google_Service_ShoppingContent_Product product
     * @throws \Exception
     */
    public function insertProduct($product, $storeId = null)
    {
        $this->setStore($storeId);
        $merchantId = $this->getConfig()->getGoogleMerchantAccountId($storeId);
        $product->setChannel("online");
        //product expires in 30 days
        $expDate = date("Y-m-d", (time() + 30 * 24 * 60 * 60));
        $product->setExpirationDate($expDate);
        $result = null;
        try {
            $gShoppingService = $this->getShoppingService();
            if ($gShoppingService) {
                $result = $gShoppingService->products->insert($merchantId, $product);
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $result;
    }

    /**
     * Product batch insert
     *
     * @param \Google_Service_ShoppingContent_Product[] $products
     * @param int|null $storeId
     * @return \Google_Service_ShoppingContent_ProductsCustomBatchResponse
     */
    public function productBatchInsert($products, $storeId = null)
    {
        $this->setStore($storeId);
        $merchantId = $this->getConfig()->getGoogleMerchantAccountId($storeId);
        $entries = [];
        foreach ($products as $itemId => $product) {
            $product->setChannel("online");
            $expDate = date("Y-m-d", (time() + 30 * 24 * 60 * 60));//product expires in 30 days
            $product->setExpirationDate($expDate);
            $entry = new \Google_Service_ShoppingContent_ProductsCustomBatchRequestEntry();
            $entry->setBatchId($itemId);
            $entry->setMerchantId($merchantId);
            $entry->setMethod('insert');
            $entry->setProduct($product);
            $entries[] = $entry;
        }

        $batchReq = new \Google_Service_ShoppingContent_ProductsCustomBatchRequest();
        $batchReq->setEntries($entries);
        $result = $this->getShoppingService()->products->customBatch($batchReq);

        return $result;
    }

    /**
     * Product batch delete.
     *
     * @param array $googleContentIds
     * @param int|null $storeId
     * @return \Google_Service_ShoppingContent_ProductsCustomBatchResponse
     */
    public function productBatchDelete($googleContentIds, $storeId = null)
    {
        $this->setStore($storeId);
        $merchantId = $this->getConfig()->getGoogleMerchantAccountId($storeId);
        $entries = [];
        foreach ($googleContentIds as $itemId => $googleContentId) {
            $entry = new \Google_Service_ShoppingContent_ProductsCustomBatchRequestEntry();
            $entry->setBatchId($itemId);
            $entry->setMerchantId($merchantId);
            $entry->setMethod('delete');
            $entry->setProductId($googleContentId);
            $entries[] = $entry;
        }

        $this->googleHelper->writeDebugLogFile(json_encode($entries));
        $batchReq = new \Google_Service_ShoppingContent_ProductsCustomBatchRequest();
        $batchReq->setEntries($entries);
        $result = $this->getShoppingService()->products->customBatch($batchReq);

        return $result;
    }

    /**
     * Update product
     *
     * @param \Google_Service_ShoppingContent_Product $product
     * @param int|null $storeId
     * @return \Google_Service_ShoppingContent_Product
     */
    public function updateProduct($product, $storeId = null)
    {
        return $this->insertProduct($product, $storeId);
    }

    /**
     * Delete product
     *
     * @param string $googleContentId
     * @param int $storeId
     * @return \Google_Http_Request
     */
    public function deleteProduct($googleContentId, $storeId)
    {
        $this->setStore($storeId);
        $merchantId = $this->getConfig()->getGoogleMerchantAccountId($storeId);
        $result = $this->getShoppingService()->products->delete($merchantId, $googleContentId);
        return $result;
    }

    /**
     * Get product
     *
     * @param int $productId
     * @param int|null $storeId
     * @return \Google_Service_ShoppingContent_Product
     */
    public function getProduct($productId, $storeId = null)
    {
        $this->setStore($storeId);
        $merchantId = $this->getConfig()->getGoogleMerchantAccountId($storeId);
        $product = $this->getShoppingService()->products->get($merchantId, $productId);
        return $product;
    }

    /**
     * List product
     *
     * @param int|null $storeId
     * @return \Google_Service_ShoppingContent_ProductsListResponse
     */
    public function listProducts($storeId = null)
    {
        $this->setStore($storeId);
        $merchantId = $this->getConfig()->getGoogleMerchantAccountId($storeId);
        return $this->getShoppingService()->products->listProducts($merchantId);
    }

    /**
     * Get config
     *
     * @return \Egits\GoogleMerchantApi\Helper\GoogleConfig
     */
    protected function getConfig()
    {
        if (!$this->googleConfig) {
            $this->googleConfig = $this->googleHelper->getConfig();
        }

        return $this->googleConfig;
    }

    /**
     * Retrieve api logger
     *
     * @return LogRepositoryInterface
     */
    protected function getLogger()
    {
        return $this->googleHelper->getApiLogger();
    }

    /**
     * Get google helper
     *
     * @return GoogleHelper
     */
    public function getGoogleHelper()
    {
        return $this->googleHelper;
    }

    /**
     * Set Store id
     *
     * @param int|null $storeId
     */
    public function setStore($storeId = null)
    {
        $this->storeId = $storeId;
        return $this;
    }
}
