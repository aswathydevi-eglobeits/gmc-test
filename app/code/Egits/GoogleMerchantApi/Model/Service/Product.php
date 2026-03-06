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
use Egits\GoogleMerchantApi\Model\Product as ProductQueueModel;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Product
 * Google product service class , interact with google api
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
     * @param GoogleShopping $googleShopping
     * @param TimezoneInterface $timezone
     * @param Registry $registry
     */
    public function __construct(GoogleShopping $googleShopping, TimezoneInterface $timezone, Registry $registry)
    {
        $this->googleShopping = $googleShopping;
        $this->timezone = $timezone;
        $this->registry = $registry;
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
        $enabledTargetCountryList = $this->googleShopping->getGoogleHelper()->getConfig()->getEnabledTargetCountry(
            $product->getProductStoreId()
        );
        $googleProductId = $product->getGoogleContentId();
        foreach ($enabledTargetCountryList as $enabledCountry) {
            try {
                preg_match('([a-z]{2}:([A-Z]{2,6}))', $googleProductId, $matches);
                if ($matches) {
                    $languageCountry = explode(':', $matches[0]);
                    $language = $languageCountry[0];
                    $replacement = $language . ':' . $enabledCountry;
                    $googleProductId = preg_replace('/([a-z]{2}):([A-Z]{2,6})/', $replacement, $googleProductId);
                }

                $googleProduct = $this->googleShopping->getProduct($googleProductId, $product->getProductStoreId());
                if ($googleProduct && $googleProduct->getId()) {
                    $this->googleShopping->deleteProduct($googleProductId, $product->getProductStoreId());
                }
            } catch (\Google_Service_Exception $exception) {
                if ($exception->getCode() == 404) {
                    $this->googleShopping->getGoogleHelper()->writeDebugLogFile(
                        'Product not found for: ' . $googleProductId
                    );
                    $this->googleShopping->getGoogleHelper()->writeDebugLogFile($exception);
                }
            }
        }

        $product->setStatus(ProductsInterface::DELETED_STATUS)
            ->setExpiryDate(null);
    }

    /**
     *  Update Item data in Google Content
     *
     * @param ProductsInterface|ProductModel $product
     * @return $this
     * @throws LocalizedException
     * @throws Exception
     */
    public function update($product)
    {

        $enabledTargetCountryList = $this->googleShopping->getGoogleHelper()->getConfig()->getEnabledTargetCountry(
            $product->getProductStoreId()
        );
        if (empty($enabledTargetCountryList)) {
            throw new LocalizedException(
                __(
                    'No target Countries Enabled for store id %1.. failed to sync',
                    $product->getProductStoreId()
                )
            );
        }

        $attributeMapType = $product->getType($enabledTargetCountryList);
        try {
            $item = $attributeMapType->convertAttributes($product);
            $shoppingProduct = $this->googleShopping->updateProduct($item, $product->getProductStoreId());
            $this->updateProductStatus($product, $shoppingProduct);
        } catch (LocalizedException $exception) {
            $product->setStatus(ProductsInterface::FAILED_STATUS);
            throw $exception;
        } catch (Exception $e) {
            $product->setStatus(ProductsInterface::FAILED_STATUS);
            throw $e;
        }

        $this->insertProductToAllTargetCountry($product, $attributeMapType);
        return $this;
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
        $enabledTargetCountryList = $this->googleShopping->getGoogleHelper()->getConfig()->getEnabledTargetCountry(
            $product->getProductStoreId()
        );
        if (empty($enabledTargetCountryList)) {
            throw new LocalizedException(
                __(
                    'No target Countries Enabled for store id %1.. failed to sync',
                    $product->getProductStoreId()
                )
            );
        }

        $attributeMapType = $product->getType($enabledTargetCountryList);
        try {
            $item = $attributeMapType->convertAttributes($product);
            $shoppingProduct = $this->googleShopping->insertProduct($item, $product->getProductStoreId());
            $this->updateProductStatus($product, $shoppingProduct);
        } catch (LocalizedException $exception) {
            $product->setStatus(ProductsInterface::FAILED_STATUS);
            throw $exception;
        } catch (Exception $e) {
            $product->setStatus(ProductsInterface::FAILED_STATUS);
            throw $e;
        }

        $this->insertProductToAllTargetCountry($product, $attributeMapType);
        return $this;
    }

    /**
     * Insert product to all other enabled target country
     *
     * @param ProductsInterface|ProductModel $product
     * @param AttributeMapTypeInterface|AttributeMapType $currentAttributeMapType
     * @return $this
     * @throws LocalizedException
     */
    protected function insertProductToAllTargetCountry($product, $currentAttributeMapType)
    {
        $registry = $this->registry->registry(ProductQueueModel::TYPES_REGISTRY_KEY);
        $targetCountry = $this->googleShopping->getGoogleHelper()
            ->getConfig()->getEnabledTargetCountry($product->getProductStoreId());
        $updatedCountry = [];
        $updatedCountry[] = $currentAttributeMapType->getTargetCountry();
        if (is_array($registry) && isset($registry[$product->getProductStoreId()])) {
            $attributeTypes = $registry[$product->getProductStoreId()];
            array_shift($attributeTypes);
            if (count($attributeTypes) > 0) {
                foreach ($attributeTypes as $targetCountry => $attributeMap) {
                    /** @var AttributeMapType $attributeMap */
                    if ($targetCountry !== $currentAttributeMapType->getTargetCountry()
                        && !in_array($targetCountry, $updatedCountry)
                    ) {
                        $item = $attributeMap->convertAttributes($product);
                        $this->googleShopping->insertProduct($item, $product->getProductStoreId());
                        $updatedCountry[] = $targetCountry;
                    }
                }

                if (count($targetCountry) != count($updatedCountry)) {
                    foreach ($targetCountry as $country) {
                        if ($country !== $currentAttributeMapType->getTargetCountry()
                            && !in_array($country, $updatedCountry)
                        ) {
                            $newAttributeMap = clone $currentAttributeMapType;
                            $newAttributeMap->setId(null)
                                ->setTargetCountry($country)
                                ->setStoreId($product->getProductStoreId());
                            $item = $newAttributeMap->convertAttributes($product);
                            $this->googleShopping->insertProduct($item, $product->getProductStoreId());
                        }
                    }
                }
            } else {
                foreach ($targetCountry as $country) {
                    if ($country !== $currentAttributeMapType->getTargetCountry()
                        && !in_array($country, $updatedCountry)
                    ) {
                        $newAttributeMap = clone $currentAttributeMapType;
                        $newAttributeMap->setId(null)
                            ->setTargetCountry($country)
                            ->setStoreId($product->getProductStoreId());
                        $item = $newAttributeMap->convertAttributes($product);
                        $this->googleShopping->insertProduct($item, $product->getProductStoreId());
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Update product status
     *
     * @param ProductsInterface|ProductModel $product
     * @param null|\Google_Service_ShoppingContent_Product $shoppingProduct
     * @return $this
     */
    protected function updateProductStatus($product, $shoppingProduct = null)
    {
        if ($shoppingProduct) {
            $expires = $shoppingProduct->getExpirationDate();
            if ($expires) {
                $expires = $this->timezone->date()->modify('+ 30 days')->format('Y-m-d H:i:s');
            } else {
                $expires = $this->timezone->date()->modify('+ 30 days')->format('Y-m-d H:i:s');
            }

            $product->setGoogleContentId($shoppingProduct->getId())
                ->setLastUpdatedToGoogle($this->googleShopping->getGoogleHelper()->getCurrentDateAndTime())
                ->setStatus(ProductsInterface::UPDATED_STATUS)->setExpiryDate($expires);
        }

        return $this;
    }
}
