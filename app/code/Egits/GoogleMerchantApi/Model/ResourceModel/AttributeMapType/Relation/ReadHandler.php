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

use Egits\GoogleMerchantApi\Model\ResourceModel\AttributeMapType;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 * Attribute map read handler class for reading mapping.
 */
class ReadHandler implements ExtensionInterface
{

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var AttributeMapType
     */
    private $attributeMapTypeResource;

    /**
     * ReadHandler constructor.
     *
     * @param MetadataPool $metadataPool
     * @param AttributeMapType $attributeMapTypeResource
     */
    public function __construct(MetadataPool $metadataPool, AttributeMapType $attributeMapTypeResource)
    {
        $this->metadataPool = $metadataPool;
        $this->attributeMapTypeResource = $attributeMapTypeResource;
    }

    /**
     * Perform action on relation/extension attribute
     *
     * @param object $entity
     * @param array $arguments
     * @return object|bool
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getTypeId()) {
            $attributeMap = $this->attributeMapTypeResource->findAttributeMappingByType((int)$entity->getTypeId());
            $entity->setData('attribute_map', $attributeMap);
        }

        return $entity;
    }
}
