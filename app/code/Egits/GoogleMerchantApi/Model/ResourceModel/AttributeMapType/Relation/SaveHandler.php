<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model\ResourceModel\AttributeMapType\Relation;

use Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterface;
use Egits\GoogleMerchantApi\Model\ResourceModel\AttributeMapping;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 * Attribute map save handler for saving mappings
 */
class SaveHandler implements ExtensionInterface
{

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var AttributeMapping
     */
    private $attributeMappingResource;

    /**
     * SaveHandler constructor.
     *
     * @param MetadataPool $metadataPool
     * @param AttributeMapping $attributeMappingResource
     */
    public function __construct(MetadataPool $metadataPool, AttributeMapping $attributeMappingResource)
    {
        $this->metadataPool = $metadataPool;
        $this->attributeMappingResource = $attributeMappingResource;
    }

    /**
     * Perform action on relation/extension attribute
     *
     * @param AttributeMapTypeInterface $entity
     * @param array $arguments
     * @return object|bool
     */
    public function execute($entity, $arguments = [])
    {
        $currentMapping = $this->attributeMappingResource->getCurrentAttributeMapping($entity);
        $newMapping = (array)$entity->getAttributeMap();

        list($deleteMappings, $insertMapping) = $this->analyzeMappings($currentMapping, $newMapping);

        if ($deleteMappings) {
            $rowDeleteCount = $this->doMappingDeletes($entity, $deleteMappings);
            if ($rowDeleteCount > 0) {
                $entity->setNumberOfRowDeleted($rowDeleteCount);
                $entity->setDeletedMappings($deleteMappings);
                $entity->setIsMappingUpdated(true);
            }
        }

        if ($insertMapping) {
            $newRowCount = $this->doMappingInserts($entity, $insertMapping);
            if ($newRowCount > 0) {
                $entity->setInsertedMapping($insertMapping);
                $entity->setNumberOfNewRowInsert($newRowCount);
                $entity->setIsMappingUpdated(true);
            }
        }

        return $entity;
    }

    /**
     * Get row of attribute to update
     *
     * @param array $currentMapping
     * @param array $newMapping
     * @return array
     */
    private function analyzeMappings($currentMapping, $newMapping)
    {
        $insertItems = [];
        $deleteItems = [];

        foreach ($newMapping as $mapping) {
            $key = array_search($mapping['attr_id'], array_column($currentMapping, 'attr_id'));
            if ($key === false) {
                $insertItems[] = [
                    'attr_id'          => (int)$mapping['attr_id'],
                    'google_attribute' => $mapping['google_attribute']
                ];
            } elseif ($mapping['google_attribute'] !== $currentMapping[$key]['google_attribute']) {
                $deleteItems[] = $mapping['attr_id'];
                $insertItems[] = [
                    'attr_id'          => (int)$mapping['attr_id'],
                    'google_attribute' => $mapping['google_attribute']
                ];
            }
        }

        foreach ($currentMapping as $value) {
            $key = array_search($value['attr_id'], array_column($newMapping, 'attr_id'));
            if ($key === false) {
                $deleteItems[] = $value['attr_id'];
            }
        }

        return [$deleteItems, $insertItems];
    }

    /**
     * Insert Mappings
     *
     * @param object $entity
     * @param array $insertMappings
     * @return int $newInsertCount
     */
    private function doMappingInserts($entity, array $insertMappings = [])
    {
        $entityMetadata = $this->metadataPool->getMetadata(AttributeMapTypeInterface::class);
        $linkField = $entityMetadata->getLinkField();
        $connection = $this->attributeMappingResource->getConnection();
        $table = $this->attributeMappingResource->getMainTable();
        $newInsertCount = 0;
        if ($insertMappings) {
            $data = [];
            foreach ($insertMappings as $item) {
                $data[] = [
                    $linkField         => $entity->getId(),
                    'attr_id'          => (int)$item['attr_id'],
                    'google_attribute' => (string)$item['google_attribute']
                ];
            }

            $newInsertCount = $connection->insertMultiple($table, $data);
        }

        return $newInsertCount;
    }

    /**
     * Delete Mappings
     *
     * @param object $entity
     * @param array $deleteMappings
     * @return int $deleteRowCount
     */
    private function doMappingDeletes($entity, array $deleteMappings = [])
    {
        $entityMetadata = $this->metadataPool->getMetadata(AttributeMapTypeInterface::class);
        $linkField = $entityMetadata->getLinkField();
        $connection = $this->attributeMappingResource->getConnection();
        $table = $this->attributeMappingResource->getMainTable();
        $deleteRowCount = 0;
        if ($deleteMappings) {
            $where = [
                $linkField . ' = ?' => $entity->getId(),
                'attr_id IN (?)'    => $deleteMappings,
            ];
            $deleteRowCount = $connection->delete($table, $where);
        }

        return $deleteRowCount;
    }
}
