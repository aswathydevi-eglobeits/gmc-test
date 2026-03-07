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
use Google\ApiCore\ApiException;
use Google\Shopping\Merchant\Products\V1\Client\ProductInputsServiceClient;
use Google\Shopping\Merchant\Products\V1\Client\ProductsServiceClient;
use Google\Shopping\Merchant\Products\V1\InsertProductInputRequest;
use Google\Shopping\Merchant\Products\V1\DeleteProductInputRequest;
use Google\Shopping\Merchant\Products\V1\GetProductRequest;
use Google\Shopping\Merchant\Products\V1\ListProductsRequest;
use Google\Shopping\Merchant\Products\V1\ProductInput;
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
    public const APP_NAME  = 'Magento 2 Shopping';
    public const SCOPE     = 'https://www.googleapis.com/auth/merchantapi';

    /**
     * Helper google
     *
     * @var GoogleHelper
     */
    protected $googleHelper;

    /**
     * Google api client
     *
     * @var \Google\Client
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
     * @var ProductsServiceClient
     */
    protected $productInputsClient;

    /**
     * Service account js file path.
     *
     * @var ProductsServiceClient
     */
    protected $productsClient;

    /**
     * @var string
     */
    private $serviceAccountJsonFile;

    /**
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
     * @return \Google\Client
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

        $client = new \Google\Client();
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
     * Get ProductInputsServiceClient (insert / delete operations)
     * @return ProductInputsServiceClient|null
     */
    public function getProductInputsClient()
    {
        if (isset($this->productInputsClient)) {
            return $this->productInputsClient;
        }

        try {
            $isEnabled      = $this->googleHelper->getConfig()->isGoogleMerchantApiEnabled($this->storeId);
            $accountJsonPath = $this->googleHelper->getConfig()->getAccountJsonFullFilePath($this->storeId);

            if ($isEnabled && $this->fileDriver->isExists($accountJsonPath)) {
                $this->productInputsClient = new ProductInputsServiceClient(
                    ['credentials' => $this->serviceAccountJsonFile]
                );
            }
        } catch (\Exception $exception) {
            $this->googleHelper->writeDebugLogFile($exception);
        }

        return $this->productInputsClient;
    }

    /**
     * Get ProductsServiceClient (read operations)
     *
     * @return ProductsServiceClient|null
     */
    public function getShoppingService()
    {
        if (isset($this->productsClient)) {
            return $this->productsClient;
        }

        try {
            $isEnabled       = $this->googleHelper->getConfig()->isGoogleMerchantApiEnabled($this->storeId);
            $accountJsonPath = $this->googleHelper->getConfig()->getAccountJsonFullFilePath($this->storeId);

            if ($isEnabled && $this->fileDriver->isExists($accountJsonPath)) {
                $this->productsClient = new ProductsServiceClient(
                    ['credentials' => $this->serviceAccountJsonFile]
                );
            }
        } catch (\Exception $exception) {
            $this->googleHelper->writeDebugLogFile($exception);
        }

        return $this->productsClient;
    }

    /**
     * Insert product
     * @param ProductInput $productInput
     * @param int|null     $storeId
     * @return \Google\Shopping\Merchant\Products\V1\ProductInput
     * @throws \Exception
     */
    public function insertProduct(ProductInput $productInput, $storeId = null)
    {
        $this->setStore($storeId);

        $accountId    = $this->getConfig()->getGoogleMerchantAccountId($storeId);
        $dataSourceId = $this->getConfig()->getDataSourceId($storeId);

        $this->googleHelper->writeDebugLogFile('accountId: ' . $accountId);
        $this->googleHelper->writeDebugLogFile('dataSourceId: ' . $dataSourceId);

        // parent  = accounts/{account}
        // dataSource = accounts/{account}/dataSources/{datasource}
        $parent     = sprintf('accounts/%s', $accountId);
        $dataSource = sprintf('accounts/%s/dataSources/%s', $accountId, $dataSourceId);

        $request = new InsertProductInputRequest();
        $request->setParent($parent);
        $request->setProductInput($productInput);
        $request->setDataSource($dataSource);

        try {
            $response = $this->getProductInputsClient()->insertProductInput($request);
            return $response;
        } catch (\Exception $e) {
            $this->googleHelper->writeDebugLogFile($e);
            throw $e;
        }
    }

    /**
     * Product batch insert
     *
     * @param \Google\Shopping\Merchant\Products\V1\ProductInput[] $products
     * @param int|null $storeId
     * @return array
     */
    public function productBatchInsert($products, $storeId = null)
    {
        $this->setStore($storeId);

        $accountId    = $this->getConfig()->getGoogleMerchantAccountId($storeId);
        $dataSourceId = $this->getConfig()->getDataSourceId($storeId);

        $result = ['success' => [], 'failed' => []];

        foreach ($products as $itemId => $productInput) {
            try {
                $parent     = sprintf('accounts/%s', $accountId);
                $dataSource = sprintf('accounts/%s/dataSources/%s', $accountId, $dataSourceId);

                $request = new InsertProductInputRequest();
                $request->setParent($parent);
                $request->setProductInput($productInput);
                $request->setDataSource($dataSource);

                $response                  = $this->getProductInputsClient()->insertProductInput($request);
                $result['success'][$itemId] = $response;
            } catch (\Exception $e) {
                $this->googleHelper->writeDebugLogFile($e);
                $result['failed'][$itemId] = [
                    'batchId' => $itemId,
                    'error'   => $e->getMessage(),
                ];
            }
        }

        return $result;
    }
    /**
     * Product batch delete
     *
     * @param array    $googleContentIds
     * @param int|null $storeId
     * @return void
     */
    public function productBatchDelete($googleContentIds, $storeId = null)
    {
        foreach ($googleContentIds as $googleContentId) {
            $this->deleteProduct($googleContentId, $storeId);
        }
    }
    /**
     * Update product (insert/update uses same endpoint in Merchant API)
     *
     * @param ProductInput $productInput
     * @param int|null     $storeId
     * @return \Google\Shopping\Merchant\Products\V1\ProductInput
     */
    public function updateProduct(ProductInput $productInput, $storeId = null)
    {
        return $this->insertProduct($productInput, $storeId);
    }

    /**
     * Delete product
     *
     * @param string $googleContentId
     * @param int    $storeId
     * @return void
     */
    // Line 362
    public function deleteProduct($name, $storeId = null)
    {
        $this->setStore($storeId);

        $accountId    = $this->getConfig()->getGoogleMerchantAccountId($storeId);
        $dataSourceId = $this->getConfig()->getDataSourceId($storeId);
        $dataSource   = sprintf('accounts/%s/dataSources/%s', $accountId, $dataSourceId);

        $this->googleHelper->writeDebugLogFile('deleteProduct name: ' . $name);
        $this->googleHelper->writeDebugLogFile('deleteProduct dataSource: ' . $dataSource);

        $request = new DeleteProductInputRequest([
            'name'        => $name,
            'data_source' => $dataSource,
        ]);

        $this->getProductInputsClient()->deleteProductInput($request);
    }

    /**
     * Get product
     *
     * @param int      $productId
     * @param int|null $storeId
     * @return \Google\Shopping\Merchant\Products\V1\Product
     */
    // Line 340
    public function getProduct($productId, $storeId = null)
    {
        $this->setStore($storeId);
        if (strpos($productId, 'accounts/') === 0) {
            $name = $productId;
        } else {
            $accountId = $this->getConfig()->getGoogleMerchantAccountId($storeId);
            $name      = sprintf('accounts/%s/products/%s', $accountId, $productId);
        }

        $this->googleHelper->writeDebugLogFile('getProduct name: ' . $name);

        $request = new GetProductRequest();
        $request->setName($name);

        return $this->getShoppingService()->getProduct($request);
    }

    /**
     * List products
     *
     * @param int|null $storeId
     * @return \Google\Shopping\Merchant\Products\V1\ListProductsResponse
     */
    public function listProducts($storeId = null)
    {
        $this->setStore($storeId);

        $accountId = $this->getConfig()->getGoogleMerchantAccountId($storeId);

        $parent = sprintf('accounts/%s', $accountId);

        $request = new ListProductsRequest();
        $request->setParent($parent);

        return $this->getShoppingService()->listProducts($request);
    }

    /**
     * Get config
     *
     * @return GoogleConfig
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
     * @return $this
     */
    public function setStore($storeId = null)
    {
        $this->storeId = $storeId;
        return $this;
    }
}
