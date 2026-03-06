<?php
/**
 *
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model\ResourceModel;

use Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterface;

use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class AttributeMapType
 * Attribute map type resource model
 */
class AttributeMapType extends AbstractDb
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
     * AttributeMapType constructor.
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
        $this->_init('egits_google_attribute_map_type', AttributeMapTypeInterface::TYPE_ID);
    }

    /**
     * Get all Attribute Mapping for type
     *
     * @param int $typeId
     * @return array
     * @throws LocalizedException
     */
    public function findAttributeMappingByType($typeId)
    {
        $connection = $this->getConnection();
        $entityMetadata = $this->metadataPool->getMetadata(AttributeMapTypeInterface::class);
        $linkField = $entityMetadata->getLinkField();
        $select = $connection->select()
            ->from(['map' => $this->getTable('egits_google_attribute_map')], '*')
            ->join(
                ['type' => $this->getMainTable()],
                'type.' . $linkField . ' = map.' . $linkField,
                []
            )
            ->where('map.' . $entityMetadata->getIdentifierField() . ' = :type_id');
        return $connection->fetchAll($select, ['type_id' => (int)$typeId]);
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
}
