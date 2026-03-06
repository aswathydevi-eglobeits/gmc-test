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

use Egits\GoogleMerchantApi\Api\GoogleDataMigrationInterface;
use Egits\GoogleMerchantApi\Model\ResourceModel\CategoryMappingFactory;
use Egits\GoogleMerchantApi\Model\ResourceModel\ProductFactory;
use Egits\GoogleMerchantApi\Model\ResourceModel\FilterFactory;
use Egits\GoogleMerchantApi\Model\ResourceModel\AttributeMapTypeFactory;
use Egits\GoogleMerchantApi\Model\ResourceModel\AttributeMappingFactory;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;

/**
 * Class GoogleDataMigration
 * Google data migration model class
 */
class GoogleDataMigration extends DataObject implements GoogleDataMigrationInterface
{
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var ProductFactory
     */
    private ProductFactory $productResourceFactory;

    /**
     * @var FilterFactory
     */
    private FilterFactory $filterResourceFactory;

    /**
     * @var AttributeMapTypeFactory
     */
    private AttributeMapTypeFactory $attributeMapTypeResourceFactory;

    /**
     * @var AttributeMappingFactory
     */
    private AttributeMappingFactory $attributeMappingResourceFactory;

    /**
     * @var CategoryMappingFactory
     */
    private CategoryMappingFactory $categoryMappingResourceFactory;
    /**
     * @var ProductsHelper
     */

    /**
     * @var ProductsHelper
     */
    private ProductsHelper $productsHelper;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * GoogleDataMigration constructor.
     * @param ResourceConnection $resourceConnection
     * @param ProductFactory $productResourceFactory
     * @param FilterFactory $filterResourceFactory
     * @param AttributeMapTypeFactory $attributeMapTypeResourceFactory
     * @param AttributeMappingFactory $attributeMappingResourceFactory
     * @param CategoryMappingFactory $categoryMappingResourceFactory
     * @param ProductsHelper $productsHelper
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ProductFactory $productResourceFactory,
        FilterFactory $filterResourceFactory,
        AttributeMapTypeFactory$attributeMapTypeResourceFactory,
        AttributeMappingFactory $attributeMappingResourceFactory,
        CategoryMappingFactory $categoryMappingResourceFactory,
        ProductsHelper $productsHelper,
        Config $config,
        array $data = []
    ) {
        parent::__construct($data);
        $this->resourceConnection = $resourceConnection;
        $this->productResourceFactory = $productResourceFactory;
        $this->filterResourceFactory = $filterResourceFactory;
        $this->attributeMapTypeResourceFactory = $attributeMapTypeResourceFactory;
        $this->attributeMappingResourceFactory = $attributeMappingResourceFactory;
        $this->categoryMappingResourceFactory = $categoryMappingResourceFactory;
        $this->productsHelper = $productsHelper;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function checkIfOldTableExists(string $table): bool
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName($table);
        return $connection->isTableExists($tableName);
    }

    /**
     * @inheritDoc
     */
    public function getAllMigrationTables() : array
    {
        return [
            self::NEW_GMC_PROD_TABLE => self::OLD_GMC_PROD_TABLE,
            self::NEW_GMC_ATTR_MAP_TYPE_TABLE => self::OLD_GMC_ATTR_MAP_TYPE_TABLE,
            self::NEW_GMC_ATTR_MAP_TABLE => self::OLD_GMC_ATTR_MAP_TABLE,
            self::NEW_GMC_FILTER_TABLE => self::OLD_GMC_FILTER_TABLE,
            self::NEW_GMC_CAT_MAP_TABLE => self::OLD_GMC_CAT_MAP_TABLE
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSelectForTable(string $table) : string
    {
        $productResource  = $this->productResourceFactory->create();
        /**
         * Migrate Products.
         */
        if ($table == $productResource->getMainTable()) {
            $productResource->getConnection()->select();
        }
        return '';
    }

    /**
     * @inheritDoc
     */
    public function checkValidForMigration() :bool
    {
        /**
         * Check if all old version tables exist in the database.
         */
        foreach ($this->getAllMigrationTables() as $oldTable => $newTable) {
            if (!$this->checkIfOldTableExists($oldTable)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function migrateTableData(string $newTable, string $oldTable)
    {
        $oldTableName = $this->resourceConnection->getTableName($oldTable);
        $newTableName = $this->resourceConnection->getTableName($newTable);
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from($oldTableName);
        $connection->query($select);
    }
}
