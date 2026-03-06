<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model\ResourceModel\Product;

use Egits\GoogleMerchantApi\Logger\Logger;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Egits\GoogleMerchantApi\Model\Product;
use Egits\GoogleMerchantApi\Model\ResourceModel\Product as ProductResource;

/**
 * Class Collection
 * Google product collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * Collection constructor.
     *
     * @param AttributeRepositoryInterface $attributeRepository
     * @param EntityFactoryInterface $entityFactory
     * @param Logger $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param ProductMetadataInterface $productMetadata
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     * @internal param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        EntityFactoryInterface $entityFactory,
        Logger $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        ProductMetadataInterface $productMetadata,
        ?AdapterInterface $connection = null,
        ?AbstractDb $resource = null
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->productMetadata = $productMetadata;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            Product::class,
            ProductResource::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @inheritdoc
     */
    protected function _beforeLoad()
    {
        $attributeId = $this->getAttributeIdOfProductName();
        $productValueExpression = $this->getConnection()->getCheckSql(
            'cpv.value IS NOT NULL',
            'cpv.value',
            'cpv_default.value'
        );

        $productPrimaryKey = 'entity_id';
        $varcharForeignKey = "entity_id";
        $isEnterprise = ("Community" != $this->productMetadata->getEdition());
        if ($isEnterprise) {
            $varcharForeignKey = 'row_id';
        }

        $this->getSelect()->joinLeft(
            ['product_entity' => $this->getTable('catalog_product_entity')],
            'main_table.product_id = product_entity.' . $productPrimaryKey,
            ['sku', 'type_id']
        );

        $this->getSelect()->joinLeft(
            ['cpv_default' => $this->getTable('catalog_product_entity_varchar')],
            "product_entity.{$varcharForeignKey} = cpv_default.{$varcharForeignKey}"
            . " AND cpv_default.store_id = 0 AND cpv_default.attribute_id = "
            . $attributeId,
            []
        );
        $this->getSelect()->joinLeft(
            ['cpv' => $this->getTable('catalog_product_entity_varchar')],
            "main_table.product_id = cpv." . $varcharForeignKey
             . " AND main_table.product_store_id = cpv.store_id AND cpv.attribute_id = "
            . $attributeId,
            []
        );

        $this->getSelect()->columns(
            ['product_name' => $productValueExpression]
        );

        if ($isEnterprise) {
            $this->getSelect()->columns(
                ['product_entity_id' => 'product_entity.entity_id']
            );
        }
        return parent::_beforeLoad();
    }

    /**
     * Get EAV id of the product name attribute
     *
     * @return mixed
     */
    private function getAttributeIdOfProductName()
    {
        $productNameAttributeId = $this->attributeRepository->get('catalog_product', 'name')->getAttributeId();
        return $productNameAttributeId;
    }
}
