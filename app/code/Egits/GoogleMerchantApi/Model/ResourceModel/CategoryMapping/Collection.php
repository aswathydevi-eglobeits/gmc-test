<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model\ResourceModel\CategoryMapping;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Egits\GoogleMerchantApi\Model\CategoryMapping;
use Egits\GoogleMerchantApi\Model\ResourceModel\CategoryMapping as CategoryMappingResource;

/**
 * Class Collection
 * Category mapping collection
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
            CategoryMapping::class,
            CategoryMappingResource::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
