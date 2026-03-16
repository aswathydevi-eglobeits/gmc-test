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

use Egits\GoogleMerchantApi\Api\Data\ProductSearchResultInterface;
use Egits\GoogleMerchantApi\Api\Data\ProductsInterface;
use Egits\GoogleMerchantApi\Api\Data\LogInterface;
use Egits\GoogleMerchantApi\Api\LogRepositoryInterface;
use Egits\GoogleMerchantApi\Helper\GoogleHelper;
use Egits\GoogleMerchantApi\Logger\Logger;
use Egits\GoogleMerchantApi\Model\ResourceModel\ProductsRepository;
use Exception;
use Google\Shopping\Merchant\Products\V1\ProductInput;
use Google\ApiCore\ApiException;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Egits\GoogleMerchantApi\Model\Product as product;

/**
 * Class Synchronizer
 */
class Synchronizer
{
    /** @var ProductsRepository */
    protected $productsRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var LogInterface */
    protected $apiLogger;

    /** @var ProductValidator */
    protected $productValidator;

    /** @var array */
    private $productsIds;

    /** @var DateTimeFactory */
    protected $dateTimeFactory;

    /** @var Logger */
    protected $logger;

    /** @var GoogleHelper */
    protected $googleHelper;

    /** @var GoogleShopping */
    private $googleShopping;

    /** @var FilterBuilder */
    protected $filterBuilder;

    /** @var FilterGroupBuilder */
    protected $filterGroupBuilder;

    /** @var array */
    protected $itemsSkipped = [];

    /** @var int */
    protected $totalSkipped = 0;

    /** @var int */
    protected $totalUpdated = 0;

    /** @var int */
    protected $totalDeleted = 0;

    /** @var array */
    protected $itemsUpdated = [];

    /** @var array */
    protected $itemsDeleted = [];

    /** @var array */
    protected $itemsFailed = [];

    /** @var int */
    protected $totalFailed = 0;

    /** @var array */
    protected $errors = [];

    /** @var int */
    protected $storeId;

    /** @var array [storeId => [itemId => googleContentId]] */
    protected $batchDeleteProducts = [];

    /** @var ProductsInterface[] */
    protected $batchDeleteItems = [];

    /** @var array [storeId => [itemId => ProductInput]] */
    protected $batchInsertProducts = [];

    /** @var ProductsInterface[] */
    protected $batchInsertItems = [];

    /** @var SortOrderBuilder */
    protected $sortOrderBuilder;

    /** @var Registry */
    protected $registry;

    /** @var product */
    private $product;

    /**
     * @param ProductsRepository $productsRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductValidator $productValidator
     * @param DateTimeFactory $dateTimeFactory
     * @param GoogleHelper $googleHelper
     * @param GoogleShopping $googleShopping
     * @param FilterBuilder $filterBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param Registry $registry
     * @param product $product
     */
    public function __construct(
        ProductsRepository $productsRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductValidator $productValidator,
        DateTimeFactory $dateTimeFactory,
        GoogleHelper $googleHelper,
        GoogleShopping $googleShopping,
        FilterBuilder $filterBuilder,
        SortOrderBuilder $sortOrderBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        Registry $registry,
        product $product
    ) {
        $this->productsRepository    = $productsRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productValidator      = $productValidator;
        $this->dateTimeFactory       = $dateTimeFactory;
        $this->googleHelper          = $googleHelper;
        $this->googleShopping        = $googleShopping;
        $this->filterBuilder         = $filterBuilder;
        $this->filterGroupBuilder    = $filterGroupBuilder;
        $this->sortOrderBuilder      = $sortOrderBuilder;
        $this->registry              = $registry;
        $this->product               = $product;
    }

    /**
     * Non-batch synchronize store items
     *
     * @param int $storeId
     * @return $this
     */
    public function synchronizeStoreItems($storeId)
    {
        $this->storeId = $storeId;
        $items = $this->getItemsByStore($storeId);
        if ($items->getTotalCount() > 0) {
            $this->productsIds = $this->productValidator->getFilterMatchingProducts(
                $this->getAllProductIds($items->getItems()),
                $storeId
            );
            list($count, $itemsAfterFilterApplied) = $this->getItemsAfterFilterApply($items->getItems());
            if ($count > 0) {
                $this->synchronizeItems($itemsAfterFilterApplied);
            }
        }
        return $this;
    }

    /**
     * Non-batch synchronize missing store items
     *
     * @param int $storeId
     * @return $this
     */
    public function synchronizeMissingStoreItems($storeId)
    {
        $this->storeId = $storeId;
        $items = $this->getMissingItemsByStore($storeId);
        if ($items->getTotalCount() > 0) {
            $this->productsIds = $this->productValidator->getFilterMatchingProducts(
                $this->getAllProductIds($items->getItems()),
                $storeId
            );
            list($count, $itemsAfterFilterApplied) = $this->getItemsAfterFilterApply($items->getItems());
            if ($count > 0) {
                $this->synchronizeItems($itemsAfterFilterApplied);
            }
        }
        return $this;
    }

    /**
     * Batch synchronize store items
     *
     * @param int $storeId
     * @return $this
     */
    public function batchSynchronizeStoreItems($storeId)
    {
        $this->storeId = $storeId;
        $this->resetBatchState();

        $items = $this->getItemsByStore($storeId, true);
        if ($items->getTotalCount() > 0) {
            $this->productsIds = $this->productValidator->getFilterMatchingProducts(
                $this->getAllProductIds($items->getItems()),
                $storeId
            );
            list($count, $itemsAfterFilterApplied) = $this->getItemsAfterFilterApply($items->getItems());
            if ($count > 0) {
                $this->batchSynchronizeItems($itemsAfterFilterApplied);
            }
        }
        return $this;
    }

    /**
     * Batch synchronize missing store items
     *
     * @param int $storeId
     * @return $this
     */
    public function batchSynchronizeMissingStoreItems($storeId)
    {
        $this->storeId = $storeId;
        $this->resetBatchState();

        $items = $this->getMissingItemsByStore($storeId, true);
        if ($items->getTotalCount() > 0) {
            $this->productsIds = $this->productValidator->getFilterMatchingProducts(
                $this->getAllProductIds($items->getItems()),
                $storeId
            );
            list($count, $itemsAfterFilterApplied) = $this->getItemsAfterFilterApply($items->getItems());
            if ($count > 0) {
                $this->batchSynchronizeItems($itemsAfterFilterApplied);
            }
        }
        return $this;
    }

    /**
     * Non-batch synchronize items
     *
     * @param ProductsInterface[] $items
     * @return $this
     */
    public function synchronizeItems($items)
    {
        $this->getLogger()->setStoreId($this->storeId);
        foreach ($items as $item) {
            /** @var ProductsInterface|Product $item */
            if ($item->getProduct()->getTypeId() == 'configurable') {
                $productsChildItems = $this->getAllChildProductsAsItem($item);
                if (!empty($productsChildItems)) {
                    foreach ($productsChildItems as $itemKey => $childItem) {
                        if (isset($items[$itemKey])) {
                            unset($items[$itemKey]);
                        }
                    }
                    $this->synchronizeItems($productsChildItems);
                    $item->setExpiryDate(
                        $this->googleHelper->getTimeZone()
                            ->date()
                            ->modify('+ 30 days')
                            ->format('Y:m:d H:i:s')
                    );
                    $item->setStatus(ProductsInterface::UPDATED_STATUS);
                    $this->itemUpdated($item);
                }
            }

            try {
                if (!$this->itemDeleteOrUpdate($item)) {
                    if (!$item->getGoogleContentId()) {
                        $this->itemSkipped($item);
                        continue;
                    }
                    $item->deleteProductFromGoogle();
                    $this->itemDeleted($item);
                    $this->productsRepository->save($item);
                } else {
                    $item->updateProductToGoogle();
                    $this->itemUpdated($item);
                    $this->productsRepository->save($item);
                }
            } catch (LocalizedException $exception) {
                $this->itemFailed($item, $exception);
                $this->productsRepository->save($item);
            } catch (Exception $e) {
                $this->itemFailed($item, $e);
                $this->productsRepository->save($item);
            }
        }

        $this->logSynchronization();
        return $this;
    }

    /**
     * Batch synchronize items
     *
     * @param ProductsInterface[] $items
     * @return $this
     */
    public function batchSynchronizeItems($items)
    {
        $copy    = $items;
        $storeId = $this->storeId;
        $this->prepareItemsForBatchSynchronization($copy);

        if (!empty($this->batchInsertProducts[$storeId])) {
            $result = null;
            try {
                $result = $this->googleShopping->productBatchInsert(
                    $this->batchInsertProducts[$storeId],
                    $storeId
                );
            } catch (Exception $e) {
                $this->errors[] = "Failed to batch update for store {$storeId}: " . $e->getMessage();
                $this->googleHelper->writeDebugLogFile($e, $storeId);
            }
            $this->processBatchInsertResponse($result);
        }

        if (!empty($this->batchDeleteProducts)) {
            foreach ($this->batchDeleteProducts as $delStoreId => $productIds) {
                $deleteItemsForStore = [];
                foreach ($this->batchDeleteItems as $delItem) {
                    if ($delItem->getProductStoreId() == $delStoreId) {
                        $deleteItemsForStore[$delItem->getId()] = $delItem;
                    }
                }

                $result = null;
                try {
                    $result = $this->googleShopping->productBatchDelete($productIds, $delStoreId);
                } catch (Exception $exception) {
                    $this->errors[] = "Failed to batch delete for store {$delStoreId}: " . $exception->getMessage();
                    $this->googleHelper->writeDebugLogFile($exception, $delStoreId);
                }
                $this->processBatchDeleteResponse($result, $deleteItemsForStore);
            }
        }

        $this->logSynchronization();
        return $this;
    }

    /**
     * Prepare items for batch synchronization
     *
     * @param ProductsInterface[] $items
     */
    protected function prepareItemsForBatchSynchronization(&$items)
    {
        foreach ($items as &$item) {
            if ($item->getProduct()->getTypeId() == 'configurable') {
                $productsChildItems = $this->getAllChildProductsAsItem($item);
                if (!empty($productsChildItems)) {
                    foreach ($productsChildItems as $itemKey => $childItem) {
                        if (isset($items[$itemKey])) {
                            unset($items[$itemKey]);
                        }
                    }
                    $this->itemUpdated($item);
                    $item->setExpiryDate(
                        $this->googleHelper->getTimeZone()
                            ->date()
                            ->modify('+ 30 days')
                            ->format('Y:m:d H:i:s')
                    );
                    $this->productsRepository->save($item);
                    $this->prepareItemsForBatchSynchronization($productsChildItems);
                    continue;
                }
            }

            try {
                if (!$this->itemDeleteOrUpdate($item)) {
                    if (!$item->getGoogleContentId()) {
                        $this->itemSkipped($item);
                        $this->productsRepository->save($item);
                        continue;
                    }

                    if (!isset($this->batchDeleteProducts[$item->getProductStoreId()])) {
                        $this->batchDeleteProducts[$item->getProductStoreId()] = [];
                    }

                    $this->batchDeleteItems[]                                            = $item;
                    $this->batchDeleteProducts[$item->getProductStoreId()][$item->getId()]
                        = $item->getGoogleContentId();
                    $this->deleteItemFromAllTargetCountries($item);
                } else {
                    if (!isset($this->batchInsertProducts[$item->getProductStoreId()])) {
                        $this->batchInsertProducts[$item->getProductStoreId()] = [];
                    }

                    /** @var AttributeMapType $attributeMap */
                    $attributeMap = $item->getType(
                        $this->googleHelper->getConfig()->getEnabledTargetCountry()
                    );

                    /** @var ProductInput $productInput */
                    $productInput = $attributeMap->convertAttributes($item);

                    $this->batchInsertProducts[$item->getProductStoreId()][$item->getId()] = $productInput;
                    $this->batchInsertItems[] = $item;
                    $this->addMultipleTargetCountryItemsToBatch($item, $attributeMap);
                }
            } catch (LocalizedException $e) {
                $this->itemFailed($item, $e);
            } catch (Exception $e) {
                $this->itemFailed($item, $e);
            }
        }
    }

    /**
     * Process batch insert response
     *
     * @param array|null $response
     */
    protected function processBatchInsertResponse($response)
    {
        if (!is_array($response)) {
            return;
        }

        foreach ($this->batchInsertItems as $item) {
            $itemId = $item->getId();

            if (isset($response['failed'][$itemId])) {
                $error = $response['failed'][$itemId]['error'] ?? 'Unknown error';
                $this->totalFailed++;
                $this->errors[] = $itemId . ' - ' . $error;
                $this->itemFailed($item, new Exception($error));
            } elseif (isset($response['success'][$itemId])) {
                /** @var ProductInput $insertedProduct */
                $insertedProduct = $response['success'][$itemId];
                $googleContentId = $insertedProduct->getName();

                $expires = $this->googleHelper->getTimeZone()
                    ->date()
                    ->modify('+ 30 days')
                    ->format('Y:m:d H:i:s');

                $item->setExpiryDate($expires);
                $item->setGoogleContentId($googleContentId);
                $this->itemUpdated($item);
            } else {
                $this->errors[] = $itemId . ' - missing response';
                $this->itemFailed($item, new Exception($itemId . ' - missing response'));
            }

            $item->setLastUpdatedToGoogle($this->googleHelper->getCurrentDateAndTime());
            $this->productsRepository->save($item);
        }
    }

    /**
     * Process batch delete response
     *
     * @param array|null          $response
     * @param ProductsInterface[] $deleteItemsMap [itemId => item]
     */
    protected function processBatchDeleteResponse($response, array $deleteItemsMap = [])
    {
        if (!is_array($response)) {
            return;
        }

        $items = !empty($deleteItemsMap) ? $deleteItemsMap : $this->batchDeleteItems;

        foreach ($items as $itemId => $item) {
            $id = is_object($item) ? $item->getId() : $itemId;

            if (isset($response['failed'][$id])) {
                $error = $response['failed'][$id]['error'] ?? 'Unknown error';
                $this->itemFailed($item, new Exception($error));
            } else {
                $this->itemDeleted($item);
            }

            $item->setLastUpdatedToGoogle($this->googleHelper->getCurrentDateAndTime());
            $this->productsRepository->save($item);
        }
    }

    /**
     * Get search criteria for normal sync
     *
     * @param int   $storeId
     * @param bool  $isBatch
     * @param array $productIds
     * @return SearchCriteria
     */
    protected function getProductSearchCriteria($storeId, $isBatch = false, $productIds = [])
    {
        $cronFrequency         = $this->googleHelper->getConfig()->getCronFrequency($storeId);
        $noOfDaysTOAdd         = $this->product->getItemRenewNoOfDays()[$cronFrequency];
        $productExpiryUpToDate = $this->googleHelper->getTimeZone()
            ->date()
            ->modify('+' . $noOfDaysTOAdd . ' days')
            ->format('Y-m-d H:i:s');

        $statusFilter = $this->filterBuilder
            ->setField(ProductsInterface::STATUS)
            ->setValue([ProductsInterface::READY_TO_UPDATE_STATUS])
            ->setConditionType('in')
            ->create();

        $expiryDateFilter = $this->filterBuilder
            ->setField(ProductsInterface::EXPIRY_DATE)
            ->setValue($productExpiryUpToDate)
            ->setConditionType('lteq')
            ->create();

        $statusOrExpiryDate = $this->filterGroupBuilder
            ->addFilter($statusFilter)
            ->addFilter($expiryDateFilter)
            ->create();

        if ($isBatch) {
            $batchSizeConfig = $this->googleHelper->getConfig()->getBatchSize();
            $batchSize       = $batchSizeConfig
                ? (int)$batchSizeConfig
                : ProductsInterface::DEFAULT_BATCH_SIZE_FOR_BATCH_IMPORT;
            $this->searchCriteriaBuilder->setPageSize($batchSize);
        }

        $this->searchCriteriaBuilder
            ->setCurrentPage(1)
            ->setFilterGroups([$statusOrExpiryDate])
            ->addFilter(ProductsInterface::STORE_ID, $storeId);

        if ($productIds) {
            $this->searchCriteriaBuilder->addFilter(ProductsInterface::PRODUCT_ID, $productIds, 'in');
        }

        $sortOrder = $this->sortOrderBuilder->setField('type_id')->setAscendingDirection()->create();
        $this->searchCriteriaBuilder->setSortOrders([$sortOrder]);

        return $this->searchCriteriaBuilder->create();
    }

    /**
     * Get search criteria for missing products sync
     *
     * @param int   $storeId
     * @param bool  $isBatch
     * @param array $productIds
     * @return SearchCriteria
     */
    protected function getProductSearchCriteriaForMissingProducts($storeId, $isBatch = false, $productIds = [])
    {
        $cronFrequency         = $this->googleHelper->getConfig()->getCronFrequency($storeId);
        $noOfDaysTOAdd         = $this->product->getItemRenewNoOfDays()[$cronFrequency];
        $productExpiryUpToDate = $this->googleHelper->getTimeZone()
            ->date()
            ->modify('+' . $noOfDaysTOAdd . ' days')
            ->format('Y-m-d H:i:s');

        $statusFilter = $this->filterBuilder
            ->setField(ProductsInterface::STATUS)
            ->setValue([ProductsInterface::READY_TO_UPDATE_STATUS, ProductsInterface::SKIPPED_STATUS])
            ->setConditionType('in')
            ->create();

        $expiryDateFilter = $this->filterBuilder
            ->setField(ProductsInterface::EXPIRY_DATE)
            ->setValue($productExpiryUpToDate)
            ->setConditionType('lteq')
            ->create();

        $statusOrExpiryDate = $this->filterGroupBuilder
            ->addFilter($statusFilter)
            ->addFilter($expiryDateFilter)
            ->create();

        if ($isBatch) {
            $batchSizeConfig = $this->googleHelper->getConfig()->getBatchSize();
            $batchSize       = $batchSizeConfig
                ? (int)$batchSizeConfig
                : ProductsInterface::DEFAULT_BATCH_SIZE_FOR_BATCH_IMPORT;
            $this->searchCriteriaBuilder->setPageSize($batchSize);
        }

        $this->searchCriteriaBuilder
            ->setCurrentPage(1)
            ->setFilterGroups([$statusOrExpiryDate])
            ->addFilter(ProductsInterface::STORE_ID, $storeId);

        if ($productIds) {
            $this->searchCriteriaBuilder->addFilter(ProductsInterface::PRODUCT_ID, $productIds, 'in');
        }

        $sortOrder = $this->sortOrderBuilder->setField('type_id')->setAscendingDirection()->create();
        $this->searchCriteriaBuilder->setSortOrders([$sortOrder]);

        return $this->searchCriteriaBuilder->create();
    }

    /**
     * Add item to multiple target country batch
     *
     * @param ProductsInterface $product
     * @param AttributeMapType  $currentAttributeMapType
     */
    protected function addMultipleTargetCountryItemsToBatch($product, $currentAttributeMapType)
    {
        $registry      = $this->registry->registry(Product::TYPES_REGISTRY_KEY);
        $targetCountry = $this->googleShopping->getGoogleHelper()
            ->getConfig()->getEnabledTargetCountry($product->getProductStoreId());

        $updatedCountry   = [];
        $updatedCountry[] = $currentAttributeMapType->getTargetCountry();

        if (is_array($registry) && isset($registry[$product->getProductStoreId()])) {
            $attributeTypes = $registry[$product->getProductStoreId()];
            array_shift($attributeTypes);

            if (count($attributeTypes) > 0) {
                foreach ($attributeTypes as $country => $attributeMap) {
                    /** @var AttributeMapType $attributeMap */
                    if ($country !== $currentAttributeMapType->getTargetCountry()
                        && !in_array($country, $updatedCountry)
                    ) {
                        /** @var ProductInput $productInput */
                        $productInput  = $attributeMap->convertAttributes($product);
                        $batchIdSuffix = substr((float)time(), -4) . round((float)microtime() * 1000);

                        $this->batchInsertProducts[$product->getProductStoreId()]
                        [$product->getId() . $batchIdSuffix] = $productInput;

                        $updatedCountry[] = $country;
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

                            $productInput  = $newAttributeMap->convertAttributes($product);
                            $batchIdSuffix = substr((float)time(), -4) . round((float)microtime() * 1000);

                            $this->batchInsertProducts[$product->getProductStoreId()]
                            [$product->getId() . $batchIdSuffix] = $productInput;
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

                        $productInput  = $newAttributeMap->convertAttributes($product);
                        $batchIdSuffix = substr((float)time(), -4) . round((float)microtime() * 1000);

                        $this->batchInsertProducts[$product->getProductStoreId()]
                        [$product->getId() . $batchIdSuffix] = $productInput;
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

                    $productInput  = $newAttributeMap->convertAttributes($product);
                    $batchIdSuffix = substr((float)time(), -4) . round((float)microtime() * 1000);

                    $this->batchInsertProducts[$product->getProductStoreId()]
                    [$product->getId() . $batchIdSuffix] = $productInput;
                }
            }
        }
    }

    /**
     * Reset all batch state for a fresh store run
     */
    private function resetBatchState(): void
    {
        $this->batchInsertProducts = [];
        $this->batchInsertItems    = [];
        $this->batchDeleteProducts = [];
        $this->batchDeleteItems    = [];
        $this->totalUpdated        = 0;
        $this->totalDeleted        = 0;
        $this->totalSkipped        = 0;
        $this->totalFailed         = 0;
        $this->itemsUpdated        = [];
        $this->itemsDeleted        = [];
        $this->itemsSkipped        = [];
        $this->itemsFailed         = [];
        $this->errors              = [];

        try {
            $this->registry->unregister(Product::TYPES_REGISTRY_KEY);
        } catch (\Exception $e) {
            // safe to ignore if key was already empty
        }
    }

    /**
     * Delete item from all target countries
     *
     * @param ProductsInterface $item
     */
    protected function deleteItemFromAllTargetCountries($item)
    {
        $enabledTargetCountryList = $this->googleShopping->getGoogleHelper()
            ->getConfig()->getEnabledTargetCountry($item->getProductStoreId());

        $originalGoogleProductId = $item->getGoogleContentId();

        foreach ($enabledTargetCountryList as $enabledCountry) {
            try {
                $googleProductId = $originalGoogleProductId;
                preg_match('([a-z]{2}:([A-Z]{2,6}))', $googleProductId, $matches);
                if ($matches) {
                    $languageCountry = explode(':', $matches[0]);
                    $language        = $languageCountry[0];
                    $replacement     = $language . ':' . $enabledCountry;
                    $googleProductId = preg_replace('/([a-z]{2}):([A-Z]{2,6})/', $replacement, $googleProductId);
                }

                $googleProduct = $this->googleShopping->getProduct($googleProductId, $item->getProductStoreId());

                if ($googleProduct && $googleProduct->getName()) {
                    $batchIdSuffix = substr((float)time(), -4) . round((float)microtime() * 1000);
                    $this->batchDeleteProducts[$item->getProductStoreId()]
                    [$item->getId() . $batchIdSuffix] = $googleProductId;
                }
            } catch (ApiException $exception) {
                if ($exception->getCode() == 404) {
                    $this->googleHelper->writeDebugLogFile('Product not found for: ' . $googleProductId);
                }
            } catch (Exception $exception) {
                $this->googleHelper->writeDebugLogFile($exception);
            }
        }
    }

    /**
     * Determine if item should be deleted or updated
     *
     * @param ProductsInterface $item
     * @return bool
     */
    protected function itemDeleteOrUpdate($item)
    {
        $result         = true;
        $removeInactive = (bool)$this->googleHelper->getConfig()->getAutoRemoveDisabled($item->getProductStoreId());
        $productStatus  = $item->getProduct()->getStatus();

        if ($removeInactive && ($productStatus == Status::STATUS_DISABLED)) {
            $result = false;
        }

        return $result;
    }

    /**
     * Get all child products as queue items
     *
     * @param ProductsInterface $item
     * @return ProductsInterface[]
     */
    protected function getAllChildProductsAsItem($item)
    {
        /** @var ProductInterface[] $childProducts */
        $childProducts     = $item->getProduct()->getTypeInstance()->getUsedProducts($item->getProduct());
        $productChildItems = [];

        if (!empty($childProducts)) {
            $childProductsIds = [];
            foreach ($childProducts as $childProduct) {
                $childProduct->setData('item_parent_product', $item->getProduct());
                $childProductsIds[$childProduct->getId()] = $childProduct;
            }

            $this->productsIds = $this->productValidator->getFilterMatchingProducts(
                array_keys($childProductsIds),
                $this->storeId
            );

            if (count($this->productsIds) != count(array_keys($childProductsIds))) {
                $childItemsFromQueue = $this->getAllChildItemsFromQueue(
                    array_keys($childProductsIds),
                    $item->getProductStoreId()
                );
                list($count, $itemsAfterFilterApplied) = $this->getItemsAfterFilterApply(
                    $childItemsFromQueue->getItems()
                );
                if ($count > 0) {
                    /** @var ProductsInterface[] $itemsAfterFilterApplied */
                    foreach ($itemsAfterFilterApplied as $childItem) {
                        $childItem->setProduct($childProductsIds[$childItem->getProductId()]);
                        $productChildItems[$childItem->getId()] = $childItem;
                    }
                }
            }
        }

        return $productChildItems;
    }

    /**
     * Get all child items from queue
     *
     * @param array $childProductIds
     * @param int   $storeId
     * @return ProductSearchResultInterface
     */
    protected function getAllChildItemsFromQueue($childProductIds, $storeId)
    {
        return $this->productsRepository->getList(
            $this->getProductSearchCriteria($storeId, false, $childProductIds)
        );
    }

    /**
     * Get items by store
     *
     * @param int  $storeId
     * @param bool $isBatch
     * @return ProductSearchResultInterface|null
     */
    protected function getItemsByStore($storeId, $isBatch = false)
    {
        if (!is_numeric($storeId)) {
            return null;
        }
        return $this->productsRepository->getList($this->getProductSearchCriteria($storeId, $isBatch));
    }

    /**
     * Get missing items by store
     *
     * @param int  $storeId
     * @param bool $isBatch
     * @return ProductSearchResultInterface|null
     */
    protected function getMissingItemsByStore($storeId, $isBatch = false)
    {
        if (!is_numeric($storeId)) {
            return null;
        }
        return $this->productsRepository->getList(
            $this->getProductSearchCriteriaForMissingProducts($storeId, $isBatch)
        );
    }

    /**
     * Get all product IDs from items
     *
     * @param ProductsInterface[] $items
     * @return array
     */
    protected function getAllProductIds($items)
    {
        $productIds = [];
        foreach ($items as $item) {
            $productIds[] = $item->getProductId();
        }
        return $productIds;
    }

    /**
     * Get items after filter apply
     *
     * @param ProductsInterface[] $items
     * @return array [$count, $filteredItems]
     */
    protected function getItemsAfterFilterApply($items)
    {
        $productItems = [];
        $itemCount    = 0;
        foreach ($items as $item) {
            if (in_array($item->getProductId(), $this->productsIds)) {
                $this->itemSkipped($item);
                $this->productsRepository->save($item);
                continue;
            }
            $productItems[$item->getId()] = $item;
            $itemCount++;
        }
        return [$itemCount, $productItems];
    }

    /**
     * Get last updated date of product
     *
     * @return int
     */
    public function getLastUpdatedDateOfProduct()
    {
        $sortOrder = ObjectManager::getInstance()->create(SortOrderBuilder::class)
            ->setField('updated_date')
            ->setDirection('DESC')
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder->setSortOrders([$sortOrder])->setPageSize(1)->create();
        $items          = $this->productsRepository->getList($searchCriteria)->getItems();
        $date           = strtotime('now');

        foreach ($items as $item) {
            $date = strtotime($item->getUpdatedDate());
        }

        return $date;
    }

    /**
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
     * @param ProductsInterface $item
     */
    protected function itemSkipped($item)
    {
        $item->setStatus(ProductsInterface::SKIPPED_STATUS);
        $this->productsRepository->save($item);
        $this->totalSkipped++;
        $this->itemsSkipped[$item->getProductStoreId()][] = $item->getProductId();
    }

    /**
     * @param ProductsInterface $item
     */
    protected function itemDeleted($item)
    {
        $item->setGoogleContentId(null)->setExpiryDate(null);
        $this->itemsDeleted[$item->getProductStoreId()][] = $item->getProductId();
        $this->totalDeleted++;
    }

    /**
     * @param ProductsInterface            $item
     * @param Exception|LocalizedException $exception
     */
    protected function itemFailed($item, $exception)
    {
        $this->googleHelper->writeDebugLogFile($exception, $this->storeId);
        $this->errors[] = sprintf(__('The item "%s" hasn\'t been updated.'), $item->getProduct()->getName());
        $this->errors[] = $exception->getMessage();
        $item->setStatus(ProductsInterface::FAILED_STATUS);
        $this->itemsFailed[$item->getProductStoreId()][] = $item->getProductId();
        $this->totalFailed++;
    }

    /**
     * @param ProductsInterface $item
     */
    protected function itemUpdated($item)
    {
        $item->setStatus(ProductsInterface::UPDATED_STATUS);
        $this->itemsUpdated[$item->getProductStoreId()][] = $item->getProductId();
        $this->totalUpdated++;
    }

    protected function logSynchronization()
    {
        $this->logSuccessfulSynchronization();
        $this->logFailedSynchronization();
    }

    protected function logFailedSynchronization()
    {
        if ($this->totalFailed > 0 || count($this->errors) > 0) {
            $failedProducts = isset($this->itemsFailed[$this->storeId])
                ? array_values($this->itemsFailed[$this->storeId])
                : [];

            $message = sprintf(
                __('Cannot update %s products for store %s. failed products Ids: %s'),
                $this->totalFailed,
                $this->storeId,
                implode("\n", $failedProducts)
            );

            array_unshift($this->errors, $message);
            $this->getLogger()->addMajor(
                sprintf(
                    __('Errors happened during synchronization with Google Shopping for Store Id %s'),
                    $this->storeId
                ),
                $this->errors,
                $this->storeId
            );
        }
    }

    protected function logSuccessfulSynchronization()
    {
        $message = [];
        if ($this->totalDeleted > 0 && isset($this->itemsDeleted[$this->storeId])) {
            $message[] = sprintf(__('Total of %s product(s) have been deleted'), $this->totalDeleted);
            $message[] = sprintf(
                    __('Products deleted are: %s'),
                    implode(', ', array_values($this->itemsDeleted[$this->storeId]))
                ) . '</br>';
        }

        if ($this->totalSkipped > 0 && isset($this->itemsSkipped[$this->storeId])) {
            $message[] = sprintf(__('Total of %s product(s) have been skipped'), $this->totalSkipped);
            $message[] = sprintf(
                    __('Products skipped are: %s'),
                    implode(', ', array_values($this->itemsSkipped[$this->storeId]))
                ) . '</br>';
        }

        if ($this->totalUpdated > 0 && isset($this->itemsUpdated[$this->storeId])) {
            $message[] = sprintf(__('Total of %s product(s) have been updated'), $this->totalUpdated);
            $message[] = sprintf(
                    __('Products updated are: %s'),
                    implode(', ', array_values($this->itemsUpdated[$this->storeId]))
                ) . '</br>';
        }

        $this->getLogger()->addSuccess(
            sprintf(
                __('Product synchronization with Google Shopping completed for store Id: %s'),
                $this->storeId
            ) . '</br>',
            $message,
            $this->storeId
        );
    }
}
