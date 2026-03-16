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

use Egits\GoogleMerchantApi\Api\Data\ProductsInterface;
use Egits\GoogleMerchantApi\Api\ProductsRepositoryInterface;
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
use GuzzleHttp\Promise\Utils as PromiseUtils;
use GuzzleHttp\Promise\PromiseInterface;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Class GoogleShopping
 * Handles all Google Merchant API interactions including batch operations.
 */
class GoogleShopping
{
    public const APP_NAME = 'Magento 2 Shopping';
    public const SCOPE    = 'https://www.googleapis.com/auth/merchantapi';

    /**
     * @var GoogleHelper
     */
    protected $googleHelper;

    /**
     * @var \Google\Client
     */
    protected $client;

    /**
     * @var GoogleConfig
     */
    private $googleConfig;

    /**
     * @var ProductInputsServiceClient
     */
    protected $productInputsClient;

    /**
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
     * @var ProductsRepositoryInterface
     */
    private $productsRepository;

    /**
     * @param GoogleHelper                $googleHelper
     * @param File                        $fileDriver
     * @param ProductsRepositoryInterface $productsRepository
     */
    public function __construct(
        GoogleHelper $googleHelper,
        File $fileDriver,
        ProductsRepositoryInterface $productsRepository
    ) {
        $this->googleHelper           = $googleHelper;
        $this->fileDriver             = $fileDriver;
        $this->productsRepository     = $productsRepository;
        $this->serviceAccountJsonFile = $this->googleHelper->getConfig()->getAccountJsonFullFilePath();
    }


    /**
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
                $this->client->setAccessToken($this->client->getAccessToken());
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
     * @return ProductInputsServiceClient|null
     */
    public function getProductInputsClient()
    {
        if (isset($this->productInputsClient)) {
            return $this->productInputsClient;
        }

        try {
            $isEnabled       = $this->googleHelper->getConfig()->isGoogleMerchantApiEnabled($this->storeId);
            $accountJsonPath = $this->googleHelper->getConfig()->getAccountJsonFullFilePath($this->storeId);

            if ($isEnabled && $this->fileDriver->isExists($accountJsonPath)) {
                $this->productInputsClient = new ProductInputsServiceClient(
                    ['credentials' => $this->serviceAccountJsonFile]
                );
            }
        } catch (\Exception $exception) {
        }

        return $this->productInputsClient;
    }

    /**
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
        }

        return $this->productsClient;
    }



    /**
     * Insert single product.
     *
     * @param ProductInput $productInput
     * @param int|null     $storeId
     * @return mixed
     */
    public function insertProduct(ProductInput $productInput, $storeId = null)
    {
        $this->setStore($storeId);

        $accountId    = $this->getConfig()->getGoogleMerchantAccountId($storeId);
        $dataSourceId = $this->getConfig()->getDataSourceId($storeId);

        $request = new InsertProductInputRequest();
        $request->setParent("accounts/{$accountId}");
        $request->setProductInput($productInput);
        $request->setDataSource("accounts/{$accountId}/dataSources/{$dataSourceId}");

        try {
            return $this->getProductInputsClient()->insertProductInput($request);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update single product — upsert via insert.
     *
     * @param ProductInput $productInput
     * @param int|null     $storeId
     * @return mixed
     */
    public function updateProduct(ProductInput $productInput, $storeId = null)
    {
        return $this->insertProduct($productInput, $storeId);
    }

    /**
     * Delete single product.
     *
     * @param string   $name
     * @param int|null $storeId
     * @return void
     */
    public function deleteProduct($name, $storeId = null)
    {
        $this->setStore($storeId);

        $accountId    = $this->getConfig()->getGoogleMerchantAccountId($storeId);
        $dataSourceId = $this->getConfig()->getDataSourceId($storeId);
        $dataSource   = "accounts/{$accountId}/dataSources/{$dataSourceId}";

        $request = new DeleteProductInputRequest([
            'name'        => $name,
            'data_source' => $dataSource,
        ]);

        $this->getProductInputsClient()->deleteProductInput($request);
    }

    /**
     * Get single product from Google.
     *
     * @param string   $productId
     * @param int|null $storeId
     * @return mixed
     */
    public function getProduct($productId, $storeId = null)
    {
        $this->setStore($storeId);

        if (strpos($productId, 'accounts/') === 0) {
            $name = $productId;
        } else {
            $accountId = $this->getConfig()->getGoogleMerchantAccountId($storeId);
            $name      = "accounts/{$accountId}/products/{$productId}";
        }

        $request = new GetProductRequest();
        $request->setName($name);

        return $this->getShoppingService()->getProduct($request);
    }

    /**
     * List all products from Google.
     *
     * @param int|null $storeId
     * @return mixed
     */
    public function listProducts($storeId = null)
    {
        $this->setStore($storeId);

        $accountId = $this->getConfig()->getGoogleMerchantAccountId($storeId);

        $request = new ListProductsRequest();
        $request->setParent("accounts/{$accountId}");

        return $this->getShoppingService()->listProducts($request);
    }


    /**
     * Batch insert — parallel async, updates DB status.
     *
     * @param ProductInput[]      $products [itemId => ProductInput]
     * @param ProductsInterface[] $items    [itemId => ProductsInterface]
     * @param int|null            $storeId
     * @return void
     */
    public function batchInsert(array $products, array $items, $storeId = null): void
    {
        if (empty($products)) {
            return;
        }

        $response = $this->productBatchInsert($products, $storeId);
        $this->processBatchInsertResponse($response, $items);
    }


    /**
     * Batch delete — parallel async, updates DB status.
     *
     * @param array               $productIds [itemId => googleContentId]
     * @param ProductsInterface[] $items      [itemId => ProductsInterface]
     * @param int|null            $storeId
     * @return void
     */
    public function batchDelete(array $productIds, array $items, $storeId = null): void
    {
        if (empty($productIds)) {
            return;
        }

        $response = $this->productBatchDelete($productIds, $storeId);
        $this->processBatchDeleteResponse($response, $items);
    }


    /**
     * Low-level parallel async insert.
     * Also used directly by Synchronizer (raw batch, no DB update).
     *
     * @param ProductInput[] $products [itemId => ProductInput]
     * @param int|null       $storeId
     * @return array ['success' => [...], 'failed' => [...]]
     */
    public function productBatchInsert($products, $storeId = null): array
    {
        $this->setStore($storeId);

        $accountId    = $this->getConfig()->getGoogleMerchantAccountId($storeId);
        $dataSourceId = $this->getConfig()->getDataSourceId($storeId);
        $parent       = "accounts/{$accountId}";
        $dataSource   = "accounts/{$accountId}/dataSources/{$dataSourceId}";

        $result   = ['success' => [], 'failed' => []];
        $promises = [];

        foreach ($products as $itemId => $productInput) {
            $request = new InsertProductInputRequest();
            $request->setParent($parent);
            $request->setProductInput($productInput);
            $request->setDataSource($dataSource);

            $promises[$itemId] = $this->getProductInputsClient()
                ->insertProductInputAsync($request);
        }

        foreach (PromiseUtils::settle($promises)->wait() as $itemId => $response) {
            if ($response['state'] === PromiseInterface::FULFILLED) {
                $result['success'][$itemId] = $response['value'];
            } else {
                $result['failed'][$itemId] = [
                    'batchId' => $itemId,
                    'error'   => (string)($response['reason'] ?? 'Unknown error'),
                ];
            }
        }

        return $result;
    }

    /**
     * Low-level parallel async delete.
     * Also used directly by Synchronizer (raw batch, no DB update).
     *
     * @param array    $googleContentIds [itemId => googleContentId]
     * @param int|null $storeId
     * @return array ['success' => [...], 'failed' => [...]]
     */
    public function productBatchDelete($googleContentIds, $storeId = null): array
    {
        $this->setStore($storeId);

        $accountId    = $this->getConfig()->getGoogleMerchantAccountId($storeId);
        $dataSourceId = $this->getConfig()->getDataSourceId($storeId);
        $dataSource   = "accounts/{$accountId}/dataSources/{$dataSourceId}";

        $result   = ['success' => [], 'failed' => []];
        $promises = [];

        foreach ($googleContentIds as $itemId => $googleContentId) {
            $request = new DeleteProductInputRequest([
                'name'        => $googleContentId,
                'data_source' => $dataSource,
            ]);

            $promises[$itemId] = $this->getProductInputsClient()
                ->deleteProductInputAsync($request);
        }

        foreach (PromiseUtils::settle($promises)->wait() as $itemId => $response) {
            if ($response['state'] === PromiseInterface::FULFILLED) {
                $result['success'][$itemId] = $response['value'];
            } else {
                $code = method_exists($response['reason'], 'getCode')
                    ? $response['reason']->getCode()
                    : 0;

                if ($code !== 404) {
                    $result['failed'][$itemId] = [
                        'batchId' => $itemId,
                        'error'   => (string)($response['reason'] ?? 'Unknown error'),
                    ];
                }
            }
        }

        return $result;
    }


    /**
     * @param array               $response ['success' => [...], 'failed' => [...]]
     * @param ProductsInterface[] $items    [itemId => ProductsInterface]
     * @return void
     */
    private function processBatchInsertResponse(array $response, array $items): void
    {
        foreach ($items as $itemId => $item) {
            if (isset($response['failed'][$itemId])) {
                $item->setStatus(ProductsInterface::FAILED_STATUS);
            } elseif (isset($response['success'][$itemId])) {
                $item->setGoogleContentId($response['success'][$itemId]->getName());
                $item->setExpiryDate(
                    $this->googleHelper->getTimeZone()
                        ->date()
                        ->modify('+30 days')
                        ->format('Y:m:d H:i:s')
                );
                $item->setStatus(ProductsInterface::UPDATED_STATUS);
            } else {
                $item->setStatus(ProductsInterface::FAILED_STATUS);
            }

            $item->setLastUpdatedToGoogle($this->googleHelper->getCurrentDateAndTime());
            $this->productsRepository->save($item);
        }
    }

    /**
     * @param array               $response ['failed' => [...]]
     * @param ProductsInterface[] $items    [itemId => ProductsInterface]
     * @return void
     */
    private function processBatchDeleteResponse(array $response, array $items): void
    {
        foreach ($items as $itemId => $item) {
            $item->setStatus(
                isset($response['failed'][$itemId])
                    ? ProductsInterface::FAILED_STATUS
                    : ProductsInterface::DELETED_STATUS
            );
            $item->setLastUpdatedToGoogle($this->googleHelper->getCurrentDateAndTime());
            $this->productsRepository->save($item);
        }
    }


    /**
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
     * @return mixed
     */
    protected function getLogger()
    {
        return $this->googleHelper->getApiLogger();
    }

    /**
     * @return GoogleHelper
     */
    public function getGoogleHelper()
    {
        return $this->googleHelper;
    }

    /**
     * @param int|null $storeId
     * @return $this
     */
    public function setStore($storeId = null)
    {
        $this->storeId = $storeId;
        return $this;
    }
}
