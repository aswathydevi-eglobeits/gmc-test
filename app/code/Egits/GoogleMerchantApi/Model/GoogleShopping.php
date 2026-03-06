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
use Google\Shopping\Merchant\Products\V1\Client\ProductsServiceClient;
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
    public const SCOPE = 'https://www.googleapis.com/auth/merchantapi';

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
     * Get shopping content service
     *
     * @return ProductsServiceClient
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
                $this->shoppingService = new ProductsServiceClient(
                    ['credentials' => $this->serviceAccountJsonFile]
                );
            }
        } catch (\Exception $exception) {
            $this->googleHelper->writeDebugLogFile($exception);
        }


        return $this->shoppingService;
    }

    /**
     * Insert product
     *
     * @param \Google\Shopping\Merchant\Products\V1\Product $product
     * @param integer $storeId
     * @return \Google\Shopping\Merchant\Products\V1\Product product
     * @throws \Exception
     */
    public function insertProduct($product, $storeId = null)
    {
        $this->setStore($storeId);

        $accountId = $this->getConfig()->getGoogleMerchantAccountId($storeId);
        $dataSourceId = $this->getConfig()->getDataSourceId($storeId);

        $client = $this->getShoppingService();

        $parent = sprintf(
            'accounts/%s/dataSources/%s',
            $accountId,
            $dataSourceId
        );

        try {
            $response = $client->insertProduct([
                'parent' => $parent,
                'product' => $product
            ]);
            return $response;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    /**
     * Product batch insert
     *
     * @param \Google\Shopping\Merchant\Products\V1\Product[] $products
     * @param int|null $storeId
     * @return array
     */
    public function productBatchInsert($products, $storeId = null)
    {
        $this->setStore($storeId);

        $merchantId   = 5715135760;
        $dataSourceId = 10616245774;
        $accessToken  = "ya29.c.c0AZ4bNpZBd0okHbTB860IvbjYQMg6WeXOyxu8ZZh1sDXMV4VlfWPoPGB2vIsjH-EwaHV-vVpJh8441aqMGe9jz7ApIRjb_ZssLMEVCBvuJGgk0RLObu_RLPN547x4Vifm-5UFjUkbUf9H2CrcegXnYL-ePi-NAIGED6w8jqzbJEtVYkcXe_kv5D_V8RyKRNl66QBrCN4BdFUXgroTXiMIuvrq06L43ZsT2x3S1Gw3UMgRmkWKPr_dVrne09ifenaDm4-N6UgGtgN-JcrAELrSMinqlgNeknDk4ddN-W7luK2lODwa583lpdgR4-qyz3brQps2thzBF_j5R9j07dKrw0txkJUU8GD9f4p20UhFCVp397-G494YNzXvT385KO3UJMi8FFf6d2z8_apFZweeRVR5-ov45SQe515p4nYMJ9Xjaaz2OMm72yd7saXe8-Y-JutVuqYxcU6kMje8s1Ynicm3tx2nigwR8IS_rqO684u29doylgewZe96fnpgBdUtX5OfUXorm0YSnYlStiskhQxoagwuOoyen2V3g6OS9qZlhp-O6vXQd7UQbxSllgSb4q-iJR26_Z5r47q-5MgJo3jyc-kzgvMrB7Obbo49j7nyY0ycukI5aOhBjafwgMv4R7o71Sn-cOiYky7v5Yv6F_dVBnibY2-tRcJg4lV-3Z-Y50ZgX1m7t2I8w3bg5Rk8n0hMOiizom6J5MdbUUkdFp7RyQnWgqSyxrdt77_QRIji5IyofJBMtll1n5utgx6b2YcUVr9xiz__RiQmQqaxwqQcZyMk8XZluQZpYOxs2B7tw1S62bhF28JJVg2oIMs9-JJMpQ-bUO9fFZm4a-2SB-qFYX822iOxgft5o2U_2tmvw_52VaRnvzUUxna75XJe6uI2diwpRXOSqM7FStbrjSt4JqOlxbrliUIjYziuaUwaUyS6q7qd3j45WxQOojMRqnbYsBFJzJlabheaxf_3j15enp78YYB6kdlmM9yhur9OgXlqJVit7YJ";

        $url = sprintf(
            'https://merchantapi.googleapis.com/products/v1/accounts/%s/productInputs:insert'
            . '?dataSource=accounts/%s/dataSources/%s',
            $merchantId,
            $merchantId,
            $dataSourceId
        );

        // ── Build payloads — mirror exactly what your original loop did ──────────
        // Original added only: setChannel("online") + setExpirationDate()
        // Everything else was already on the $product before being passed in
        $payloads = [];
        foreach ($products as $itemId => $product) {
            $payloads[$itemId] = array_merge($product, [
                'channel'        => 'ONLINE',                                        // was: $product->setChannel("online")
                'expirationDate' => date('Y-m-d', time() + 30 * 24 * 60 * 60),     // was: $product->setExpirationDate($expDate)
            ]);
        }

        // ── Send all concurrently (replaces customBatch) ─────────────────────────
        $multiHandle = curl_multi_init();
        $handles     = [];

        foreach ($payloads as $itemId => $payload) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_TIMEOUT    => 30,
            ]);

            curl_multi_add_handle($multiHandle, $ch);
            $handles[$itemId] = $ch;
        }

        $running = null;
        do {
            curl_multi_exec($multiHandle, $running);
            curl_multi_select($multiHandle);
        } while ($running > 0);

        // ── Collect results — same shape as customBatch response ─────────────────
        $result = ['success' => [], 'failed' => []];

        foreach ($handles as $itemId => $ch) {
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $body     = json_decode(curl_multi_getcontent($ch), true);

            if ($httpCode >= 200 && $httpCode < 300) {
                $result['success'][$itemId] = $body;
            } else {
                $result['failed'][$itemId] = [
                    'batchId'  => $itemId,                               // mirrors original batchId
                    'httpCode' => $httpCode,
                    'error'    => $body['error']['message'] ?? 'Unknown error',
                ];
            }

            curl_multi_remove_handle($multiHandle, $ch);
            curl_close($ch);
        }

        curl_multi_close($multiHandle);
        return $result;
    }
    /**
     * Product batch delete.
     *
     * @param array $googleContentIds
     * @param int|null $storeId
     * @return void
     */
    public function productBatchDelete($googleContentIds, $storeId = null)
    {
        $this->setStore($storeId);
        $accountId = $this->getConfig()->getGoogleMerchantAccountId($storeId);

        foreach ($googleContentIds as $googleContentId) {
            try {
                $name = sprintf(
                    'accounts/%s/productInputs/%s',
                    $accountId,
                    $googleContentId
                );

                $this->googleHelper->writeDebugLogFile('Deleting product: ' . $name);

                $this->getShoppingService()->deleteProductInput(['name' => $name]);

            } catch (\Exception $e) {
                $this->googleHelper->writeDebugLogFile($e);
            }
        }
    }

    /**
     * Update product
     *
     * @param \Google\Shopping\Merchant\Products\V1\Product $product
     * @param int|null $storeId
     * @return \Google\Shopping\Merchant\Products\V1\Product
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
     * @return void
     */
    public function deleteProduct($googleContentId, $storeId)
    {
        $this->setStore($storeId);

        $accountId = $this->getConfig()->getGoogleMerchantAccountId($storeId);

        $name = sprintf(
            'accounts/%s/productInputs/%s',
            $accountId,
            $googleContentId
        );

        $this->getShoppingService()->deleteProductInput(['name' => $name]);
    }

    /**
     * Get product
     *
     * @param int $productId
     * @param int|null $storeId
     * @return \Google\Shopping\Merchant\Products\V1\Product
     */
    public function getProduct($productId, $storeId = null)
    {
        $this->setStore($storeId);

        $accountId = $this->getConfig()->getGoogleMerchantAccountId($storeId);

        $name = sprintf(
            'accounts/%s/products/%s',
            $accountId,
            $productId
        );

        return $this->getShoppingService()->getProduct($name);
    }

    /**
     * List product
     *
     * @param int|null $storeId
     * @return \Google\Shopping\Merchant\Products\V1\ListProductsResponse
     */
    public function listProducts($storeId = null)
    {
        $this->setStore($storeId);

        $accountId = $this->getConfig()->getGoogleMerchantAccountId($storeId);

        $parent = sprintf('accounts/%s', $accountId);

        return $this->getShoppingService()->listProducts($parent);
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
