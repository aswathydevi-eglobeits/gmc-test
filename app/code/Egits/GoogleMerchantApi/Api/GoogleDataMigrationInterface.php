<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Api;

interface GoogleDataMigrationInterface
{
    public const OLD_GMC_PROD_TABLE = "egits_google_merchant_products";
    public const OLD_GMC_ATTR_MAP_TABLE = "egits_google_merchant_attribute_map";
    public const OLD_GMC_ATTR_MAP_TYPE_TABLE = "egits_google_merchant_attribute_map_type";
    public const OLD_GMC_FILTER_TABLE = "egits_feed_filter";
    public const OLD_GMC_CAT_MAP_TABLE = "egits_google_merchant_category_mapping";

    public const NEW_GMC_PROD_TABLE = "egits_google_products";
    public const NEW_GMC_ATTR_MAP_TABLE = "egits_google_attribute_map";
    public const NEW_GMC_ATTR_MAP_TYPE_TABLE = "egits_google_attribute_map_type";
    public const NEW_GMC_FILTER_TABLE = "egits_google_product_filter";
    public const NEW_GMC_CAT_MAP_TABLE = "egits_google_category_mapping";

    /**
     * Check if the old table exist or not
     *
     * @param string $table
     * @return bool
     */
    public function checkIfOldTableExists(string $table): bool;

    /**
     * To get select table
     *
     * @param string $table
     * @return string
     */
    public function getSelectForTable(string $table): string;

    /**
     * Validation for table migration
     *
     * @return bool
     */
    public function checkValidForMigration() :bool;

    /**
     * By comparing with old table
     *
     * @return array
     */
    public function getAllMigrationTables() : array;

    /**
     * To migrate table
     *
     * @param string $newTable
     * @param string $oldTable
     * @return mixed
     */
    public function migrateTableData(string $newTable, string $oldTable);
}
