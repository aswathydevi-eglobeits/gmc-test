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
 * Sync product to google merchant center
 */
class Synchronizer
{

    /**
     * @var ProductsRepository
     */
    protected $productsRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var LogInterface
     */
    protected $apiLogger;

    /**
     * @var ProductValidator
     */
    protected $productValidator;

    /**
     * Product Ids to sync
     *
     * @var array
     */
    private $productsIds;

    /**
     * @var DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var GoogleHelper
     */
    protected $googleHelper;

    /**
     * @var GoogleShopping
     */
    private $googleShopping;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    protected $filterGroupBuilder;

    /**
     * Get items skipped
     *
     * @var array
     */
    protected $itemsSkipped = [];

    /**
     * Total items skipped
     *
     * @var int
     */
    protected $totalSkipped = 0;

    /**
     * Total items updated
     *
     * @var int
     */
    protected $totalUpdated = 0;

    /**
     * Total items deleted
     *
     * @var int
     */
    protected $totalDeleted = 0;

    /**
     * Get updated items
     *
     * @var array
     */
    protected $itemsUpdated = [];

    /**
     * Get items deleted
     *
     * @var array
     */
    protected $itemsDeleted = [];

    /**
     * Get items failed
     *
     * @var array
     */
    protected $itemsFailed = [];

    /**
     * Get total failed
     *
     * @var int
     */
    protected $totalFailed = 0;

    /**
     * Synchronization errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Current Synchronizing store id
     *
     * @var int
     */
    protected $storeId;

    /**
     * items that are to be deleted.
     *
     * @var array
     */
    protected $batchDeleteProducts;

    /**
     * items that are deleted
     *
     * @var ProductsInterface[]
     */
    protected $batchDeleteItems;

    /**
     * Items that are to be inserted or updated
     *
     * @var array
     */
    protected $batchInsertProducts;

    /**
     * Items that are updated to inserted
     *
     * @var ProductsInterface[]
     */
    protected $batchInsertItems;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var product
     */
    private $product;

    /**
     * Synchronizer constructor.
     *
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
     * @param \Egits\GoogleMerchantApi\Model\Product $product
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
        $this->productsRepository = $productsRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productValidator = $productValidator;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->googleHelper = $googleHelper;
        $this->googleShopping = $googleShopping;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->registry = $registry;
        $this->product = $product;
    }

    /**
     * Synchronize product for the store
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
     * Synchronize product for the store
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
     * Synchronize Items
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
                    //remove child item from items
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
     * Batch synchronize store items
     *
     * @param int $storeId
     * @return $this
     */
    public function batchSynchronizeStoreItems($storeId)
    {
        $this->storeId = $storeId;
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
     * Batch synchronize store items
     *
     * @param int $storeId
     * @return $this
     */
    public function batchSynchronizeMissingStoreItems($storeId)
    {
        $this->storeId = $storeId;
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
     * Batch synchronize
     *
     * @param ProductsInterface[] $items
     * @return $this
     */
    public function batchSynchronizeItems($items)
    {
        $copy = $items;
        $storeId = $this->storeId;
        $this->prepareItemsForBatchSynchronization($copy);
        if (isset($this->batchInsertProducts[$this->storeId])
            && count($this->batchInsertProducts[$this->storeId]) > 0) {
            $storeProducts = $this->batchInsertProducts[$storeId];
            $result = null;
            try {
                $result = $this->googleShopping->productBatchInsert($storeProducts, $storeId);
            } catch (Exception $e) {
                $this->errors[] = "Failed to batch update for store " . $storeId . ":" . $e->getMessage();
                $this->googleHelper->writeDebugLogFile($e, $this->storeId);
            }

            $this->processBatchInsertResponse($result);
        }

        if (isset($this->batchDeleteProducts[$this->storeId])
            && count($this->batchDeleteProducts[$this->storeId]) > 0) {
            $result = null;
            foreach ($this->batchDeleteProducts as $storeId => $productIds) {
                try {
                    $result = $this->googleShopping->productBatchDelete($productIds, $storeId);
                } catch (Exception $exception) {
                    $this->errors[] = "Failed to batch Delete for store " . $storeId . ":" . $exception->getMessage();
                    $this->googleHelper->writeDebugLogFile($exception, $this->storeId);
                }

                $this->processBatchDeleteResponse($result);
            }
        }

        $this->logSynchronization();
        return $this;
    }

    /**
     * Prepare item for batch update
     *
     * @param ProductsInterface[] $items
     */
    protected function prepareItemsForBatchSynchronization(&$items)
    {
        if (is_array($this->batchInsertItems) && count($this->batchInsertItems) > 0) {
            $this->batchInsertItems = [];
        }
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

                    $this->batchDeleteItems[] = $item;
                    $this->batchDeleteProducts[$item->getProductStoreId()][$item->getId()] = $item->getGoogleContentId(
                    );
                    $this->deleteItemFromAllTargetCountries($item);
                } else {
                    if (!isset($this->batchInsertProducts[$item->getProductStoreId()])) {
                        $this->batchInsertProducts[$item->getProductStoreId()] = [];
                    }

                    /** @var AttributeMapType $attributeMap */
                    $attributeMap = $item->getType($this->googleHelper->getConfig()->getEnabledTargetCountry());
                    $this->batchInsertProducts[$item->getProductStoreId()][$item->getId()]
                        = $attributeMap->convertAttributes($item);
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
     * Process batch delete response.
     *
     * Update expiration dates or collect errors.
     *
     * @param ProductInput $response
     */
    protected function processBatchDeleteResponse($response)
    {
        if ($response) { // update expiration dates or collect errors
            foreach ($response->getEntries() as $batchEntry) {
                $resEntries[$batchEntry->getBatchId()] = $batchEntry;
            }

            foreach ($this->batchDeleteItems as $item) {
                if (!isset($resEntries[$item->getId()])
                    || !is_a(
                        $resEntries[$item->getId()],
                        ProductInput::class
                    )
                ) {
                    $this->errors[] = $item->getId() . " - missing response";
                    $this->itemFailed($item, new Exception($item->getId() . " - missing response"));
                    $this->productsRepository->save($item);
                    continue;
                }

                if ($resErrors = $resEntries[$item->getId()]->getErrors()) {
                    foreach ($resErrors->getErrors() as $resError) {
                        $this->totalFailed++;
                        $this->errors[] = $item->getId() . " - " . $resError->getMessage();
                    }

                    $this->itemFailed($item, new Exception(implode(',', $this->errors)));
                } else {
                    $this->itemDeleted($item);
                }

                $item->setLastUpdatedToGoogle($this->googleHelper->getCurrentDateAndTime());
                $this->productsRepository->save($item);
            }
        }
    }

    /**
     * Process batch insert response.
     *
     * Update expiration dates or collect errors.
     *
     * @param ProductInput $response
     */
    protected function processBatchInsertResponse($response)
    {
        $resEntries = [];
        if ($response) { // update expiration dates or collect errors
            foreach ($response->getEntries() as $batchEntry) {
                $resEntries[$batchEntry->getBatchId()] = $batchEntry;
            }

            foreach ($this->batchInsertItems as $item) {
                if (!isset($resEntries[$item->getId()])
                    || !is_a(
                        $resEntries[$item->getId()],
                        ProductInput::class
                    )
                ) {
                    $this->errors[] = $item->getId() . " - missing response" . "\n";
                    $this->itemFailed($item, new Exception($item->getId() . " - missing response"));
                    $this->productsRepository->save($item);
                    continue;
                }

                if ($resErrors = $resEntries[$item->getId()]->getErrors()) {
                    foreach ($resErrors->getErrors() as $resError) {
                        $this->totalFailed++;
                        $this->errors[] = $item->getId() . " - " . $resError->getMessage();
                    }

                    $this->itemFailed($item, new Exception(implode(',', $this->errors)));
                } else {
                    $expires = null;
                    if ($resEntries[$item->getId()]->getProduct()->getExpirationDate()) {
                        $expires = $this->googleHelper->convertContentDateToTimestamp(
                            $resEntries[$item->getId()]->getProduct()->getExpirationDate()
                        );
                    }
                    if (!$expires) {
                        $expires = $this->googleHelper->getTimeZone()
                            ->date()
                            ->modify('+ 30 days')
                            ->format('Y:m:d H:i:s');
                    }

                    $item->setExpiryDate($expires);
                    $item->setGoogleContentId($resEntries[$item->getId()]->getProduct()->getId());
                    $this->itemUpdated($item);
                }

                $item->setLastUpdatedToGoogle($this->googleHelper->getCurrentDateAndTime());
                $this->productsRepository->save($item);
            }
        }
    }

    /**
     * Determine item delete or update,
     *
     * If delete return false else true.
     *
     * @param ProductsInterface $item
     * @return bool
     */
    protected function itemDeleteOrUpdate($item)
    {
        $result = true;
        $removeInactive = (bool)$this->googleHelper->getConfig()->getAutoRemoveDisabled($item->getProductStoreId());
        $productStockItem = $item->getProduct()->getExtensionAttributes()->getStockItem();
        $productStockStatus = $productStockItem ? (bool)$productStockItem->getIsInStock() : true;
        $productStatus = $item->getProduct()->getStatus();

        if ($removeInactive
            && ($productStatus == Status::STATUS_DISABLED)
        ) {
            $result = false;
        }

        return $result;
    }

    /**
     * Get all child product from configurable and get child items from queue,
     *
     * Set parent product in it and apply filter conditions
     *
     * @param ProductsInterface $item
     * @return ProductsInterface[]|[]
     */
    protected function getAllChildProductsAsItem($item)
    {
        /** @var ProductInterface[] $childProducts */
        $childProducts = $item->getProduct()->getTypeInstance()->getUsedProducts($item->getProduct());
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
     * Get all child products of a configurable product from queue
     *
     * @param array $childProductIds
     * @param int $storeId
     * @return ProductSearchResultInterface
     */
    protected function getAllChildItemsFromQueue($childProductIds, $storeId)
    {
        return $this->productsRepository->getList($this->getProductSearchCriteria($storeId, false, $childProductIds));
    }

    /**
     * Return products search result  for store
     *
     * @param int $storeId
     * @param bool $isBatch
     * @return ProductSearchResultInterface|null
     */
    protected function getItemsByStore($storeId, $isBatch = false)
    {
        if (!is_numeric($storeId)) {
            return null;
        }

        return $this->productsRepository
            ->getList($this->getProductSearchCriteria($storeId, $isBatch));
    }

    /**
     * Return skipped / error products search result  for store
     *
     * @param int $storeId
     * @param bool $isBatch
     * @return ProductSearchResultInterface|null
     */
    protected function getMissingItemsByStore($storeId, $isBatch = false)
    {
        if (!is_numeric($storeId)) {
            return null;
        }

        return $this->productsRepository
            ->getList($this->getProductSearchCriteriaForMissingProducts($storeId, $isBatch));
    }

    /**
     * Get search criteria
     *
     * @param int $storeId
     * @param bool $isBatch
     * @param array $productIds
     * @return SearchCriteria
     */
    protected function getProductSearchCriteria($storeId, $isBatch = false, $productIds = [])
    {
        $cronFrequency = $this->googleHelper->getConfig()->getCronFrequency($storeId);
        $noOfDaysTOAdd =  $this->product->getItemRenewNoOfDays()[$cronFrequency];
        $productExpiryUpToDateToUpdate = $this->googleHelper->getTimeZone()
            ->date()
            ->modify('+' . $noOfDaysTOAdd . ' days')
            ->format('Y-m-d H:i:s');

        $statusFilter = $this->filterBuilder->setField(ProductsInterface::STATUS)
            ->setValue(
                [
                    ProductsInterface::READY_TO_UPDATE_STATUS
                ]
            )
            ->setConditionType('in')
            ->create();
        $expiryDateFilter = $this->filterBuilder->setField(ProductsInterface::EXPIRY_DATE)
            ->setValue($productExpiryUpToDateToUpdate)
            ->setConditionType('lteq')
            ->create();
        $statusOrExpiryDate = $this->filterGroupBuilder
            ->addFilter($statusFilter)
            ->addFilter($expiryDateFilter)
            ->create();
        if ($isBatch) {
            $batchSizeConfig = $this->googleHelper->getConfig()->getBatchSize();
            $batchSize = $batchSizeConfig ? (int)$batchSizeConfig
                : ProductsInterface::DEFAULT_BATCH_SIZE_FOR_BATCH_IMPORT;
            $this->searchCriteriaBuilder->setPageSize($batchSize);
        }

        $this->searchCriteriaBuilder->setFilterGroups([$statusOrExpiryDate])
            ->addFilter(
                ProductsInterface::STORE_ID,
                $storeId
            );
        if ($productIds) {
            $this->searchCriteriaBuilder->addFilter(
                ProductsInterface::PRODUCT_ID,
                $productIds,
                'in'
            );
        }

        $sortOrder = $this->sortOrderBuilder->setField('type_id')->setAscendingDirection()->create();
        $this->searchCriteriaBuilder->setSortOrders([$sortOrder]);
        return $this->searchCriteriaBuilder->create();
    }

    /**
     * Get search criteria For Missing Items
     *
     * @param int $storeId
     * @param bool $isBatch
     * @param array $productIds
     * @return SearchCriteria
     */
    protected function getProductSearchCriteriaForMissingProducts($storeId, $isBatch = false, $productIds = [])
    {
        $cronFrequency = $this->googleHelper->getConfig()->getCronFrequency($storeId);
        $noOfDaysTOAdd =  $this->product->getItemRenewNoOfDays()[$cronFrequency];
        $productExpiryUpToDateToUpdate = $this->googleHelper->getTimeZone()
            ->date()
            ->modify('+' . $noOfDaysTOAdd . ' days')
            ->format('Y-m-d H:i:s');

        $statusFilter = $this->filterBuilder->setField(ProductsInterface::STATUS)
            ->setValue(
                [
                    ProductsInterface::FAILED_STATUS,
                    ProductsInterface::SKIPPED_STATUS
                ]
            )
            ->setConditionType('in')
            ->create();
        $expiryDateFilter = $this->filterBuilder->setField(ProductsInterface::EXPIRY_DATE)
            ->setValue($productExpiryUpToDateToUpdate)
            ->setConditionType('lteq')
            ->create();
        $statusOrExpiryDate = $this->filterGroupBuilder
            ->addFilter($statusFilter)
            ->addFilter($expiryDateFilter)
            ->create();
        if ($isBatch) {
            $batchSizeConfig = $this->googleHelper->getConfig()->getBatchSize();
            $batchSize = $batchSizeConfig ? (int)$batchSizeConfig
                : ProductsInterface::DEFAULT_BATCH_SIZE_FOR_BATCH_IMPORT;
            $this->searchCriteriaBuilder->setPageSize($batchSize);
        }

        $this->searchCriteriaBuilder->setFilterGroups([$statusOrExpiryDate])
            ->addFilter(
                ProductsInterface::STORE_ID,
                $storeId
            );
        if ($productIds) {
            $this->searchCriteriaBuilder->addFilter(
                ProductsInterface::PRODUCT_ID,
                $productIds,
                'in'
            );
        }

        $sortOrder = $this->sortOrderBuilder->setField('type_id')->setAscendingDirection()->create();
        $this->searchCriteriaBuilder->setSortOrders([$sortOrder]);
        return $this->searchCriteriaBuilder->create();
    }

    /**
     * Get Product ids from Items
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
     * Get items after filter apply skip items match filter
     *
     * @param ProductsInterface[] $items
     * @return array|ProductsInterface[]
     */
    protected function getItemsAfterFilterApply($items)
    {
        $productItems = [];
        $itemCount = 0;
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
     * Get last updated date
     *
     * @return false|int
     */
    public function getLastUpdatedDateOfProduct()
    {
        $sortOrder = ObjectManager::getInstance()->create(SortOrderBuilder::class)->setField('updated_date')
            ->setDirection('DESC')
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder->setSortOrders([$sortOrder])->setPageSize(1)->create();
        $items = $this->productsRepository->getList($searchCriteria)->getItems();
        $date = strtotime('now');
        foreach ($items as $item) {
            $date = strtotime($item->getUpdatedDate());
        }

        return $date;
    }

    /**
     * Get logger Repository
     *
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
     * Set item skipped
     *
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
     * Set item deleted
     *
     * @param ProductsInterface $item
     */
    protected function itemDeleted($item)
    {
        $item->setGoogleContentId(null)->setExpiryDate(null);
        $this->itemsDeleted[$item->getProductStoreId()][] = $item->getProductId();
        $this->totalDeleted++;
    }

    /**
     * Set item Failed
     *
     * @param ProductsInterface $item
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
     * Set item updated
     *
     * @param ProductsInterface $item
     */
    protected function itemUpdated($item)
    {
        $item->setStatus(ProductsInterface::UPDATED_STATUS);
        $this->itemsUpdated[$item->getProductStoreId()][] = $item->getProductId();
        $this->totalUpdated++;
    }

    /**
     * Log synchronization
     */
    protected function logSynchronization()
    {
        $this->logSuccessfulSynchronization();
        $this->logFailedSynchronization();
    }

    /**
     * Log failed sync
     */
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

    /**
     * Log successful sync
     */
    protected function logSuccessfulSynchronization()
    {
        $message = [];
        if ($this->totalDeleted > 0 && isset($this->itemsSkipped[$this->storeId])) {
            $message[] = sprintf(__('Total of %s product(s) have been deleted'), $this->totalDeleted);
            $message[] = sprintf(
                    __('Products deleted are: %s'),
                    implode(", ", array_values($this->itemsDeleted[$this->storeId]))
                ) . "</br>";
        }

        if ($this->totalSkipped > 0 && isset($this->itemsSkipped[$this->storeId])) {
            $message[] = sprintf(__('Total of %s product(s) have been skipped'), $this->totalSkipped);
            $message[] = sprintf(
                    __('Products skipped are: %s'),
                    implode(", ", array_values($this->itemsSkipped[$this->storeId]))
                ) . "</br>";
        }

        if ($this->totalUpdated > 0 && isset($this->itemsSkipped[$this->storeId])) {
            $message[] = sprintf(__('Total of %s product(s) have been updated'), $this->totalUpdated);
            $message[] = sprintf(
                    __('Products updated are: %s'),
                    implode(", ", array_values($this->itemsUpdated[$this->storeId]))
                ) . "</br>";
        }

        $this->getLogger()->addSuccess(
            sprintf(
                __('Product synchronization with Google Shopping completed for store Id: %s'),
                $this->storeId
            ) . "</br>",
            $message,
            $this->storeId
        );
        //file logger
    }

    /**
     * Add item to multiple target country
     *
     * @param ProductsInterface $product
     * @param AttributeMapType $currentAttributeMapType
     */
    protected function addMultipleTargetCountryItemsToBatch($product, $currentAttributeMapType)
    {
        $registry = $this->registry->registry(Product::TYPES_REGISTRY_KEY);
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
                        $batchIdSuffix = substr((float)time(), -4) . round((float)microtime() * 1000);
                        $this->batchInsertProducts[$product->getProductStoreId()][$item->getId() . $batchIdSuffix]
                            = $item;
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
                            $batchIdSuffix = substr((float)time(), -4) . round((float)microtime() * 1000);
                            $this->batchInsertProducts[$product->getProductStoreId()][$item->getId() . $batchIdSuffix]
                                = $item;
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
                        $batchIdSuffix = substr((float)time(), -4) . round((float)microtime() * 1000);

                        $this->batchInsertProducts[$product->getProductStoreId()][$item->getId() . $batchIdSuffix]
                            = $item;
                    }
                }
            }
        }
    }

    /**
     * Delete item from all target country.
     *
     * @param ProductsInterface $item
     */
    protected function deleteItemFromAllTargetCountries($item)
    {
        $enabledTargetCountryList = $this->googleShopping->getGoogleHelper()->getConfig()->getEnabledTargetCountry(
            $item->getProductStoreId()
        );
        $googleProductId = $item->getGoogleContentId();
        foreach ($enabledTargetCountryList as $enabledCountry) {
            try {
                preg_match('([a-z]{2}:([A-Z]{2,6}))', $googleProductId, $matches);
                if ($matches) {
                    $languageCountry = explode(':', $matches[0]);
                    $language = $languageCountry[0];
                    $replacement = $language . ':' . $enabledCountry;
                    $googleProductId = preg_replace('/([a-z]{2}):([A-Z]{2,6})/', $replacement, $googleProductId);
                }

                $googleProduct = $this->googleShopping->getProduct($googleProductId, $item->getProductStoreId());
                if ($googleProduct && $googleProduct->getId()) {
                    $batchIdSuffix = substr((float)time(), -4) . round((float)microtime() * 1000);
                    $this->batchDeleteProducts[$item->getProductStoreId()][$item->getId() . $batchIdSuffix]
                        = $googleProductId;
                }
            } catch (ApiException $exception) {
                if ($exception->getCode() == 404) {
                    $this->googleHelper->writeDebugLogFile(
                        'Product not found for: ' . $googleProductId
                    );
                }
            } catch (Exception $exception) {
                $this->googleHelper->writeDebugLogFile($exception);
            }
        }
    }
}
