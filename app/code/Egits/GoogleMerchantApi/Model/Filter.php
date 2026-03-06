<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model;

use Egits\GoogleMerchantApi\Api\Data\FilterInterface;
use Egits\GoogleMerchantApi\Model\ResourceModel\Filter as FilterResource;
use Egits\GoogleMerchantApi\Model\ResourceModel\Filter as FilterResourceModel;
use Egits\GoogleMerchantApi\Model\ResourceModel\Filter\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Filter
 * Filter model class
 */
class Filter extends AbstractModel implements FilterInterface
{
    /**
     * Entity type and current filter key
     */
    public const ENTITY_TYPE = 'filter';
    public const CURRENT_FILTER_DATA_KEY = 'current_googlemerchant_filter';

    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * Filter constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Rule $rule
     * @param Serializer $serializer
     * @param FilterResourceModel|null $resource
     * @param Collection|null $resourceCollection
     * @param array $data
     * @throws LocalizedException
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Rule $rule,
        Serializer $serializer,
        ?FilterResource $resource = null,
        ?Collection $resourceCollection = null,
        array $data = []
    ) {
        $this->rule = $rule;
        $this->serializer = $serializer;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(FilterResourceModel::class);
        $this->setIdFieldName('filter_id');
    }

    /**
     * Get Conditions file set id
     *
     * @param string
     * @return string
     * @since 100.1.0
     */
    public function getConditionsFieldSetId()
    {
        return 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * Get Conditions serialized
     *
     * @return bool|mixed|string
     */
    public function getConditionsSerialized()
    {
        $conditionsSerialized = $this->getData('conditions');

        if ($conditionsSerialized) {
            if ($conditionsSerialized[0] == 'a') { // Old serialization format used
                if (interface_exists(SerializerInterface::class)) {
                    $conditionsSerialized = $this->serializer->serialize(
                        $this->serializer->unserialize($conditionsSerialized)
                    );
                }
            }
        }

        return $conditionsSerialized;
    }

    /**
     * Get Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->_getData(self::STORE_ID);
    }

    /**
     * Get Filter name
     *
     * @return string
     */
    public function getFilterName()
    {
        return $this->_getData(self::FILTER_NAME);
    }

    /**
     * Get Filter Condition
     *
     * @return string
     */
    public function getConditions()
    {
        return $this->_getData(self::CONDITION);
    }

    /**
     * Get Filter Status
     *
     * @return int
     */
    public function getIsActive()
    {
        return $this->_getData(self::IS_ACTIVE);
    }

    /**
     * Set store id
     *
     * @param int $id
     * @return $this
     */
    public function setStoreId($id)
    {
        $this->setData(self::STORE_ID, $id);
        return $this;
    }

    /**
     * Set Filter name
     *
     * @param string $name
     * @return $this
     */
    public function setFilterName($name)
    {
        $this->setData(self::FILTER_NAME, $name);
        return $this;
    }

    /**
     * Set Filter condition
     *
     * @param string $condition
     * @return void
     */
    public function setConditions($condition)
    {
        $this->setData(self::CONDITION, $condition);
    }

    /**
     * Set Filter status
     *
     * @param int $status
     * @return $this
     */
    public function setIsActive($status)
    {
        $this->setData(self::IS_ACTIVE, $status);
        return $this;
    }

    /**
     * Get Serializer
     *
     * @return Serializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }
}
