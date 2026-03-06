<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model\ResourceModel\AttributeMapping;

use Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Egits\GoogleMerchantApi\Model\AttributeMapping;
use Egits\GoogleMerchantApi\Model\ResourceModel\AttributeMapping as AttributeMappingResourceModel;

/**
 * Class Collection
 * Attribute Mapping collection
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            AttributeMapping::class,
            AttributeMappingResourceModel::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * Set AttributeMapType Filter
     *
     * @param AttributeMapTypeInterface $attributeMapType
     * @return $this
     */
    public function setTypeFilter($attributeMapType)
    {
        if (is_array($attributeMapType)) {
            $this->addFieldToFilter('type_id', ['in' => $attributeMapType]);
        } elseif ($attributeMapType->getId()) {
            $this->addFieldToFilter('type_id', $attributeMapType->getId());
        } else {
            $this->$attributeMapType('type_id', '-1');
        }

        return $this;
    }
}
