<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model\Service;

use Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterface;
use Egits\GoogleMerchantApi\Api\Data\ProductsInterface;
use Egits\GoogleMerchantApi\Model\AttributeMapType;
use Egits\GoogleMerchantApi\Model\GoogleShopping;
use Egits\GoogleMerchantApi\Model\Product as ProductModel;
use Exception;
use Google\ApiCore\ApiException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Product
 * Google product service class, interact with google api
 */
class Product
{
    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var GoogleShopping
     */
    protected $googleShopping;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Product constructor.
     *
     * @param GoogleShopping    $googleShopping
     * @param TimezoneInterface $timezone
     * @param Registry          $registry
     */
    public function __construct(GoogleShopping $googleShopping, TimezoneInterface $timezone, Registry $registry)
    {
        $this->googleShopping = $googleShopping;
        $this->timezone       = $timezone;
        $this->registry       = $registry;
    }

    /**
     * Set Store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * Delete Item from Google Content
     *
     * @param ProductsInterface $product
     */
    public function delete($product)
    {
        if ($product->getGoogleContentId()) {
            $this->deleteProductFromAllTargetCountries($product);
        }
    }

    /**
     * Delete product from all target countries
     *
     * @param ProductsInterface $product
     */
    protected function deleteProductFromAllTargetCountries($product)
    {
        $storeId = $product->getProductStoreId();
        $enabledTargetCountryList = $this->getEnabledTargetCountries($storeId);
        $originalGoogleContentId = $product->getGoogleContentId();
        if (empty($enabledTargetCountryList)) {
            $this->googleShopping->deleteProduct($originalGoogleContentId, $storeId);
        } else {
            foreach ($enabledTargetCountryList as $enabledCountry) {
                try {
                    $googleContentId = $this->buildCountrySpecificGoogleContentId(
                        $originalGoogleContentId,
                        $enabledCountry
                    );
                    $this->googleShopping->deleteProduct($googleContentId, $storeId);
                } catch (ApiException $exception) {
                    if ((int)$exception->getCode() !== 404) {
                        $this->googleShopping->getGoogleHelper()->writeDebugLogFile($exception);
                    }
                } catch (Exception $exception) {
                    $this->googleShopping->getGoogleHelper()->writeDebugLogFile($exception);
                }
            }
        }

        $product->setStatus(ProductsInterface::DELETED_STATUS)
            ->setExpiryDate(null);
    }

    /**
     * Update Item data in Google Content
     *
     * @param ProductsInterface|ProductModel $product
     * @return $this
     * @throws LocalizedException
     * @throws Exception
     */
    public function update($product)
    {
        return $this->syncProduct($product, 'updateProduct');
    }

    /**
     * Insert Item into Google Content
     *
     * @param ProductsInterface|ProductModel $product
     * @return $this
     * @throws LocalizedException|Exception
     */
    public function insert($product)
    {
        return $this->syncProduct($product, 'insertProduct');
    }

    /**
     * Insert product to all other enabled target country
     *
     * @param ProductsInterface|ProductModel $product
     * @param AttributeMapTypeInterface|AttributeMapType $currentAttributeMapType
     * @return $this
     * @throws LocalizedException
     */

    protected function insertProductToAllTargetCountry($product, $currentAttributeMapType, $googleShoppingMethod = 'insertProduct')
    {
        $storeId         = $product->getProductStoreId();
        $attributeTypes  = $this->getRegisteredAttributeTypes($storeId);
        $targetCountries = $this->getEnabledTargetCountries($storeId);
        $currentCountry  = $currentAttributeMapType->getTargetCountry();

        foreach ($targetCountries as $country) {
            if ($country === $currentCountry) {
                continue;
            }

            try {
                if (isset($attributeTypes[$country])) {
                    $attributeMap = $attributeTypes[$country];
                } else {
                    $attributeMap = clone $currentAttributeMapType;
                    $attributeMap->setId(null)
                        ->setTargetCountry($country)
                        ->setStoreId($storeId)
                        ->resetAttributeMappingCollection();
                }

                $item            = $attributeMap->convertAttributes($product);
                $shoppingProduct = $this->googleShopping->{$googleShoppingMethod}($item, $storeId);
                $this->updateProductStatus($product, $shoppingProduct);

            } catch (ApiException $exception) {
                if ((int)$exception->getCode() !== 404) {
                    $this->googleShopping->getGoogleHelper()->writeDebugLogFile($exception);
                }
            } catch (Exception $exception) {
                $this->googleShopping->getGoogleHelper()->writeDebugLogFile($exception);
            }
        }

        return $this;
    }

    /**
     * Update product status
     *
     * @param ProductsInterface|ProductModel $product
     * @param object|null $shoppingProduct
     * @return $this
     */
    protected function updateProductStatus($product, $shoppingProduct = null)
    {
        if ($shoppingProduct) {
            $googleContentId = $shoppingProduct->getName();
            $expires = $this->timezone->date()->modify('+30 days')->format('Y-m-d H:i:s');

            $product->setGoogleContentId($googleContentId)
                ->setLastUpdatedToGoogle(
                    $this->googleShopping->getGoogleHelper()->getCurrentDateAndTime()
                )
                ->setStatus(ProductsInterface::UPDATED_STATUS)
                ->setExpiryDate($expires);
        }

        return $this;
    }

    /**
     * @param int $storeId
     * @return array
     */
    protected function getEnabledTargetCountries($storeId)
    {
        return (array)$this->googleShopping->getGoogleHelper()
            ->getConfig()
            ->getEnabledTargetCountry($storeId);
    }

    /**
     * @param int $storeId
     * @return array
     */
    protected function getRegisteredAttributeTypes($storeId)
    {
        $registry = $this->registry->registry(ProductsInterface::TYPES_REGISTRY_KEY);
        if (is_array($registry) && isset($registry[$storeId])) {
            return $registry[$storeId];
        }

        return [];
    }

    /**
     * @param ProductsInterface|ProductModel $product
     * @param string $googleShoppingMethod
     * @return $this
     * @throws LocalizedException
     * @throws Exception
     */
    protected function syncProduct($product, $googleShoppingMethod)
    {
        $storeId = $product->getProductStoreId();
        $enabledTargetCountryList = $this->getEnabledTargetCountries($storeId);

        if (empty($enabledTargetCountryList)) {
            throw new LocalizedException(
                __('No target Countries Enabled for store id %1.. failed to sync', $storeId)
            );
        }

        $attributeMapType = $product->getType($enabledTargetCountryList);

        try {
            $item = $attributeMapType->convertAttributes($product);
            $shoppingProduct = $this->googleShopping->{$googleShoppingMethod}($item, $storeId);
            $this->updateProductStatus($product, $shoppingProduct);
            $this->insertProductToAllTargetCountry($product, $attributeMapType);
        } catch (LocalizedException $exception) {
            $product->setStatus(ProductsInterface::FAILED_STATUS);
            throw $exception;
        } catch (Exception $exception) {
            $product->setStatus(ProductsInterface::FAILED_STATUS);
            throw $exception;
        }

        return $this;
    }

    /**
     * @param string $googleContentId
     * @param string $targetCountry
     * @return string
     */
    protected function buildCountrySpecificGoogleContentId($googleContentId, $targetCountry)
    {
        if (strpos($googleContentId, '~') !== false) {
            return (string)preg_replace(
                '/~([A-Z]{2,6})~/',
                '~' . $targetCountry . '~',
                $googleContentId
            );
        }

        return (string)preg_replace(
            '/([a-z]{2}):([A-Z]{2,6})/',
            '$1:' . $targetCountry,
            $googleContentId,
            1
        );
    }
}
