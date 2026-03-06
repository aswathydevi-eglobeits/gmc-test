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

use Egits\GoogleMerchantApi\Api\Data\AttributeMappingInterface;
use Egits\GoogleMerchantApi\Model\ResourceModel\AttributeMapping as AttributeMappingResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class AttributeMapping
 * Attribute mapping model
 */
class AttributeMapping extends AbstractModel implements AttributeMappingInterface
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(AttributeMappingResource::class);
        $this->setIdFieldName('entity_id');
    }

    /**
     * Get Attribute Id
     *
     * @return int
     */
    public function getAttributeId()
    {
        return $this->_getData(self::ATTRIBUTE_ID);
    }

    /**
     * Get google attribute code
     *
     * @return string
     */
    public function getGoogleAttribute()
    {
        return $this->_getData(self::GOOGLE_ATTRIBUTE);
    }

    /**
     * Get attribute map type id
     *
     * @return int
     */
    public function getTypeId()
    {
        return $this->_getData(self::TYPE_ID);
    }

    /**
     * Set Attribute Map Type id
     *
     * @param int $typeId
     * @return $this
     */
    public function setTypeId($typeId)
    {
        $this->setData(self::TYPE_ID, (int)$typeId);
        return $this;
    }

    /**
     * Set Google Attribute
     *
     * @param string $code
     * @return $this
     */
    public function setGoogleAttribute($code)
    {
        $this->setData(self::GOOGLE_ATTRIBUTE, (string)$code);
        return $this;
    }
}
