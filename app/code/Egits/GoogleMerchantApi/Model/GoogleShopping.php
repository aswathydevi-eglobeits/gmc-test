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
use GuzzleHttp\Promise\Utils as PromiseUtils;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Class GoogleShopping
 * Class interact with google api library
 * All operations are async (non-blocking) — callers receive PromiseInterface
 */
class GoogleShopping
{
    public const APP_NAME = 'Magento 2 Shopping';
    public const SCOPE    = 'https://www.googleapis.com/auth/merchantapi';

    protected $googleHelper;
    protected $client;
    private $googleConfig;
    protected $productInputsClient;
    protected $productsClient;
    private $serviceAccountJsonFile;
    protected $storeId;
    protected $fileDriver;

    public function __construct(GoogleHelper $googleHelper, File $fileDriver)
    {
        $this->googleHelper           = $googleHelper;
        $this->serviceAccountJsonFile = $this->googleHelper->getConfig()->getAccountJsonFullFilePath();
        $this->fileDriver             = $fileDriver;
    }

    // -------------------------------------------------------------------------
    // Client Initialization
    // -------------------------------------------------------------------------

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
            $this->googleHelper->writeDebugLogFile($exception);
        }

        return $this->productInputsClient;
    }

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

    // -------------------------------------------------------------------------
    // Insert — Async
    // -------------------------------------------------------------------------

    /**
     * Insert single product — Async (Non-blocking)
     * Returns PromiseInterface — caller can ->wait() or fire-and-forget
     *
     * Used by: Service/Product.php->insert()
     *
     * @param ProductInput $productInput
     * @param int|null $storeId
     * @return PromiseInterface
     */
    public function insertProduct(ProductInput $productInput, $storeId = null): PromiseInterface
    {
        $this->setStore($storeId);

        $accountId    = $this->getConfig()->getGoogleMerchantAccountId($storeId);
        $dataSourceId = $this->getConfig()->getDataSourceId($storeId);

        $this->googleHelper->writeDebugLogFile('insertProduct accountId: ' . $accountId);
        $this->googleHelper->writeDebugLogFile('insertProduct dataSourceId: ' . $dataSourceId);

        $request = new InsertProductInputRequest();
        $request->setParent("accounts/{$accountId}");
        $request->setProductInput($productInput);
        $request->setDataSource("accounts/{$accountId}/dataSources/{$dataSourceId}");

        try {
            $promise = $this->getProductInputsClient()->insertProductInputAsync($request);

            return $promise->then(
                function ($response) use ($productInput) {
                    $this->googleHelper->writeDebugLogFile(
                        'insertProduct success: ' . $productInput->getName()
                    );
                    return $response;
                },
                function ($reason) use ($productInput) {
                    $this->googleHelper->writeDebugLogFile(
                        'insertProduct failed: ' . $productInput->getName() . ' | Error: ' . $reason
                    );
                    return new RejectedPromise($reason);
                }
            );
        } catch (\Exception $e) {
            $this->googleHelper->writeDebugLogFile($e);
            return new RejectedPromise($e);
        }
    }

    // -------------------------------------------------------------------------
    // Batch Insert — Async Parallel
    // -------------------------------------------------------------------------

    /**
     * Product batch insert — Parallel Async
     * Used by: BatchProcessor->batchInsert()
     *
     * @param ProductInput[] $products [itemId => ProductInput]
     * @param int|null $storeId
     * @return PromiseInterface resolves to ['success' => [...], 'failed' => [...]]
     */
    public function productBatchInsert($products, $storeId = null): PromiseInterface
    {
        $this->setStore($storeId);

        $accountId    = $this->getConfig()->getGoogleMerchantAccountId($storeId);
        $dataSourceId = $this->getConfig()->getDataSourceId($storeId);
        $parent       = "accounts/{$accountId}";
        $dataSource   = "accounts/{$accountId}/dataSources/{$dataSourceId}";

        $promises = [];

        foreach ($products as $itemId => $productInput) {
            $request = new InsertProductInputRequest();
            $request->setParent($parent);
            $request->setProductInput($productInput);
            $request->setDataSource($dataSource);

            $promises[$itemId] = $this->getProductInputsClient()
                ->insertProductInputAsync($request);
        }

        // Return a single promise that resolves when ALL complete
        return PromiseUtils::settle($promises)->then(
            function ($responses) {
                $result = ['success' => [], 'failed' => []];

                foreach ($responses as $itemId => $response) {
                    if ($response['state'] === PromiseInterface::FULFILLED) {
                        $result['success'][$itemId] = $response['value'];
                        $this->googleHelper->writeDebugLogFile(
                            'batchInsert success itemId: ' . $itemId
                        );
                    } else {
                        $result['failed'][$itemId] = [
                            'batchId' => $itemId,
                            'error'   => (string)($response['reason'] ?? 'Unknown error'),
                        ];
                        $this->googleHelper->writeDebugLogFile(
                            'batchInsert failed itemId: ' . $itemId . ' | Error: ' . ($response['reason'] ?? '')
                        );
                    }
                }

                return $result;
            }
        );
    }

    // -------------------------------------------------------------------------
    // Update — Async (upsert via insert)
    // -------------------------------------------------------------------------

    /**
     * Update single product — Async (same endpoint as insert — upsert)
     * Returns PromiseInterface
     *
     * Used by: Service/Product.php->update()
     *
     * @param ProductInput $productInput
     * @param int|null $storeId
     * @return PromiseInterface
     */
    public function updateProduct(ProductInput $productInput, $storeId = null): PromiseInterface
    {
        return $this->insertProduct($productInput, $storeId);
    }

    // -------------------------------------------------------------------------
    // Delete — Async
    // -------------------------------------------------------------------------

    /**
     * Delete single product — Async (Non-blocking)
     * Returns PromiseInterface — caller can ->wait() or fire-and-forget
     *
     * Used by: Service/Product.php->delete()
     *
     * @param string $name
     * @param int|null $storeId
     * @return PromiseInterface
     */
    public function deleteProduct($name, $storeId = null): PromiseInterface
    {
        $this->setStore($storeId);

        $accountId    = $this->getConfig()->getGoogleMerchantAccountId($storeId);
        $dataSourceId = $this->getConfig()->getDataSourceId($storeId);
        $dataSource   = "accounts/{$accountId}/dataSources/{$dataSourceId}";

        $this->googleHelper->writeDebugLogFile('deleteProduct name: ' . $name);
        $this->googleHelper->writeDebugLogFile('deleteProduct dataSource: ' . $dataSource);

        $request = new DeleteProductInputRequest([
            'name'        => $name,
            'data_source' => $dataSource,
        ]);

        try {
            $promise = $this->getProductInputsClient()->deleteProductInputAsync($request);

            return $promise->then(
                function ($response) use ($name) {
                    $this->googleHelper->writeDebugLogFile('deleteProduct success: ' . $name);
                    return $response;
                },
                function ($reason) use ($name) {
                    $this->googleHelper->writeDebugLogFile(
                        'deleteProduct failed: ' . $name . ' | Error: ' . $reason
                    );
                    return new RejectedPromise($reason);
                }
            );
        } catch (\Exception $e) {
            $this->googleHelper->writeDebugLogFile($e);
            return new RejectedPromise($e);
        }
    }

    // -------------------------------------------------------------------------
    // Batch Delete — Async Parallel
    // -------------------------------------------------------------------------

    /**
     * Product batch delete — Parallel Async
     * Used by: BatchProcessor->batchDelete()
     *
     * @param array $googleContentIds [itemId => googleContentId]
     * @param int|null $storeId
     * @return PromiseInterface resolves to ['success' => [...], 'failed' => [...]]
     */
    public function productBatchDelete($googleContentIds, $storeId = null): PromiseInterface
    {
        $this->setStore($storeId);

        $accountId    = $this->getConfig()->getGoogleMerchantAccountId($storeId);
        $dataSourceId = $this->getConfig()->getDataSourceId($storeId);
        $dataSource   = "accounts/{$accountId}/dataSources/{$dataSourceId}";

        $promises = [];

        foreach ($googleContentIds as $itemId => $googleContentId) {
            $request = new DeleteProductInputRequest([
                'name'        => $googleContentId,
                'data_source' => $dataSource,
            ]);

            $promises[$itemId] = $this->getProductInputsClient()
                ->deleteProductInputAsync($request);
        }

        // Return a single promise that resolves when ALL complete
        return PromiseUtils::settle($promises)->then(
            function ($responses) {
                $result = ['success' => [], 'failed' => []];

                foreach ($responses as $itemId => $response) {
                    if ($response['state'] === PromiseInterface::FULFILLED) {
                        $result['success'][$itemId] = $response['value'];
                        $this->googleHelper->writeDebugLogFile(
                            'batchDelete success itemId: ' . $itemId
                        );
                    } else {
                        $code = method_exists($response['reason'], 'getCode')
                            ? $response['reason']->getCode()
                            : 0;

                        // 404 — already deleted, skip silently
                        if ($code !== 404) {
                            $result['failed'][$itemId] = [
                                'batchId' => $itemId,
                                'error'   => (string)($response['reason'] ?? 'Unknown error'),
                            ];
                            $this->googleHelper->writeDebugLogFile(
                                'batchDelete failed itemId: ' . $itemId . ' | Error: ' . ($response['reason'] ?? '')
                            );
                        }
                    }
                }

                return $result;
            }
        );
    }

    // -------------------------------------------------------------------------
    // Get Product — Async
    // -------------------------------------------------------------------------

    /**
     * Get single product from Google — Async
     * Returns PromiseInterface
     *
     * Used by: Synchronizer->deleteItemFromAllTargetCountries()
     *
     * @param string $productId
     * @param int|null $storeId
     * @return PromiseInterface
     */
    public function getProduct($productId, $storeId = null): PromiseInterface
    {
        $this->setStore($storeId);

        if (strpos($productId, 'accounts/') === 0) {
            $name = $productId;
        } else {
            $accountId = $this->getConfig()->getGoogleMerchantAccountId($storeId);
            $name      = "accounts/{$accountId}/products/{$productId}";
        }

        $this->googleHelper->writeDebugLogFile('getProduct name: ' . $name);

        $request = new GetProductRequest();
        $request->setName($name);

        try {
            $promise = $this->getShoppingService()->getProductAsync($request);

            return $promise->then(
                function ($response) use ($name) {
                    $this->googleHelper->writeDebugLogFile('getProduct success: ' . $name);
                    return $response;
                },
                function ($reason) use ($name) {
                    $this->googleHelper->writeDebugLogFile(
                        'getProduct failed: ' . $name . ' | Error: ' . $reason
                    );
                    return new RejectedPromise($reason);
                }
            );
        } catch (\Exception $e) {
            $this->googleHelper->writeDebugLogFile($e);
            return new RejectedPromise($e);
        }
    }

    // -------------------------------------------------------------------------
    // List Products — Async
    // -------------------------------------------------------------------------

    /**
     * List all products from Google — Async
     * Returns PromiseInterface
     *
     * Used by: Admin product listing
     *
     * @param int|null $storeId
     * @return PromiseInterface
     */
    public function listProducts($storeId = null): PromiseInterface
    {
        $this->setStore($storeId);

        $accountId = $this->getConfig()->getGoogleMerchantAccountId($storeId);

        $request = new ListProductsRequest();
        $request->setParent("accounts/{$accountId}");

        try {
            $promise = $this->getShoppingService()->listProductsAsync($request);

            return $promise->then(
                function ($response) use ($accountId) {
                    $this->googleHelper->writeDebugLogFile(
                        'listProducts success accountId: ' . $accountId
                    );
                    return $response;
                },
                function ($reason) use ($accountId) {
                    $this->googleHelper->writeDebugLogFile(
                        'listProducts failed accountId: ' . $accountId . ' | Error: ' . $reason
                    );
                    return new RejectedPromise($reason);
                }
            );
        } catch (\Exception $e) {
            $this->googleHelper->writeDebugLogFile($e);
            return new RejectedPromise($e);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function getConfig()
    {
        if (!$this->googleConfig) {
            $this->googleConfig = $this->googleHelper->getConfig();
        }
        return $this->googleConfig;
    }

    protected function getLogger()
    {
        return $this->googleHelper->getApiLogger();
    }

    public function getGoogleHelper()
    {
        return $this->googleHelper;
    }

    public function setStore($storeId = null)
    {
        $this->storeId = $storeId;
        return $this;
    }
}
