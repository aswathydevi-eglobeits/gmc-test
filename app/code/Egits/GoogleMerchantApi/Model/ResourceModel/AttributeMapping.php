<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model\ResourceModel;

use Egits\GoogleMerchantApi\Api\Data\AttributeMappingInterface;
use Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class AttributeMapping
 * Attribute mapping resource model
 */
class AttributeMapping extends AbstractDb
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * AttributeMapping constructor.
     *
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param EntityManager $entityManager
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        EntityManager $entityManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadataPool = $metadataPool;
        $this->entityManager = $entityManager;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('egits_google_attribute_map', AttributeMappingInterface::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function save(AbstractModel $object)
    {
        $this->entityManager->save($object);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        return $this->entityManager->load($object, $value);
    }

    /**
     * @inheritDoc
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
    }

    /**
     * Get attribute mapping for attribute map type
     *
     * @param AttributeMapTypeInterface $attributeMapType
     * @return array
     */
    public function getCurrentAttributeMapping(AttributeMapTypeInterface $attributeMapType)
    {
        $connection = $this->getConnection();

        $select = $connection->select();
        $select->from($this->getMainTable(), ['attr_id', 'google_attribute']);
        $select->where('type_id = ?', (int)$attributeMapType->getId());
        $result = $connection->fetchAll($select);

        return $result;
    }
}
