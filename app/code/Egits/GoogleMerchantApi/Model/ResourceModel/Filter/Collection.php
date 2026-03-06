<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model\ResourceModel\Filter;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Egits\GoogleMerchantApi\Model\Filter;
use Egits\GoogleMerchantApi\Model\ResourceModel\Filter as FilterResource;

/**
 * Class Collection
 * Filter collection
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
            Filter::class,
            FilterResource::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->columns(['store.store_id'])
            ->joinLeft(
                ['store' => $this->getTable('store')],
                'main_table . store_id = store . store_id',
                []
            );
    }
}
