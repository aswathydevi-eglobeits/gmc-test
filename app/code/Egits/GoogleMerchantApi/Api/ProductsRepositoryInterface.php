<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Api;

use Egits\GoogleMerchantApi\Api\Data\ProductsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface ProductsRepositoryInterface
 * Google product repository interface
 */
interface ProductsRepositoryInterface
{
    /**
     * Save Product to queue
     *
     * @param \Egits\GoogleMerchantApi\Api\Data\ProductsInterface $product
     * @return \Egits\GoogleMerchantApi\Api\Data\ProductsInterface
     */
    public function save(ProductsInterface $product);

    /**
     * Get Product by id
     *
     * @param int $id
     * @param \Egits\GoogleMerchantApi\Api\Data\ProductsInterface|null $product
     * @return \Egits\GoogleMerchantApi\Api\Data\ProductsInterface;
     * @throws \Magento\Framework\Exception\NoSuchEntityException;
     */
    public function loadById($id, ?ProductsInterface $product = null);

    /**
     * Get Product List
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Egits\GoogleMerchantApi\Api\Data\ProductSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Product
     *
     * @param \Egits\GoogleMerchantApi\Api\Data\ProductsInterface $product
     * @return void
     */
    public function delete(ProductsInterface $product);

    /**
     * Delete By id
     *
     * @param int $id
     * @return void
     */
    public function deleteById($id);

    /**
     * Load By Product Id
     *
     * @param int $productId
     * @param int $storeId
     * @return ProductsInterface
     */
    public function loadByProductId($productId, $storeId);

    /**
     * Insert Data as batch
     *
     * @param array $data
     * @return int
     */
    public function batchInsert(array $data);
}
