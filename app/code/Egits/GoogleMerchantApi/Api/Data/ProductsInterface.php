<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Api\Data;

/**
 * Interface ProductsInterface
 * Google product interface
 */
interface ProductsInterface
{
    /**
     * Entity Fields
     */
    public const TYPES_REGISTRY_KEY = 'google_content_attribute_type';
    public const  TYPES_CURRENT_TARGET_COUNTRY = 'current_target_country';
    public const ENTITY_ID = 'entity_id';
    public const PRODUCT_ID = 'product_id';
    public const GOOGLE_CONTENT_ITEM_ID = 'gcontent_product_id';
    public const STATUS = 'status';
    public const STORE_ID = 'product_store_id';
    public const ADDED_DATE = 'added_date';
    public const UPDATED_DATE = 'updated_date';
    public const LAST_SYNCED = 'last_synced';
    public const EXPIRY_DATE = 'expire_at';
    public const PRODUCT = 'product';

    /**
     * Status values
     */
    public const READY_TO_UPDATE_STATUS = 0;
    public const UPDATED_STATUS = 1;
    public const FAILED_STATUS = 2;
    public const SKIPPED_STATUS = 3;
    public const DELETED_STATUS = 4;
    public const ERROR_STATUS = 5;

    /**
     *  default batch size
     */
    public const DEFAULT_BATCH_SIZE_FOR_BATCH_IMPORT = 50;

    /**
     * Queue Status Label
     */
    public const UPDATED_STATUS_LABEL = 'Updated';
    public const FAILED_STATUS_LABEL = 'Failed';
    public const SKIPPED_STATUS_LABEL = 'Skipped';
    public const DELETED_STATUS_LABEL = 'Deleted';
    public const READY_TO_UPDATE_LABEL = 'Ready To Update';
    public const ERROR_STATUS_LABEL = 'Error';

    /**
     * Get Entity id
     *
     * @return int
     */
    public function getId();

    /**
     * Get Product Id
     *
     * @return int
     */
    public function getProductId();

    /**
     * Get Status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Get  Store id
     *
     * @return int
     */
    public function getProductStoreId();

    /**
     * Get  Added date
     *
     * @return string
     */
    public function getAddedDate();

    /**
     * Get  Store id
     *
     * @return string
     */
    public function getUpdatedDate();

    /**
     * Get Google Content Item id
     *
     * @return string
     */
    public function getGoogleContentId();

    /**
     * Get Product
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProduct();

    /**
     * Set Product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return $this;
     */
    public function setProduct($product);

    /**
     * Get Expiry date
     *
     * @return string
     */
    public function getExpiryDate();

    /**
     * Get Last updated to google
     *
     * @return string
     */
    public function getLastUpdatedToGoogle();

    /**
     * Set Entity Id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Set Product id
     *
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * Set Status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Set Store Id
     *
     * @param int $storeId
     * @return $this
     */
    public function setProductStoreId($storeId);

    /**
     * Set AddedDate
     *
     * @param string $addedDate
     * @return $this
     */
    public function setAddedDate($addedDate);

    /**
     * Set Updated date
     *
     * @param string $expiryDate
     * @return $this
     */
    public function setUpdatedDate($expiryDate);

    /**
     * Set Expiry date
     *
     * @param string $expiryDate
     * @return $this
     */
    public function setExpiryDate($expiryDate);

    /**
     * Set Google Content item id
     *
     * @param string $id
     * @return $this
     */
    public function setGoogleContentId($id);

    /**
     * Set Last updated to google
     *
     * @param string $date
     * @return $this
     */
    public function setLastUpdatedToGoogle($date);

    /**
     * Get current operation is sync or update
     *
     * @return bool
     */
    public function isSync();

    /**
     * Set current operation to sync.
     *
     * @param bool $isSync
     * @return $this;
     */
    public function setIsSync($isSync = true);
}
