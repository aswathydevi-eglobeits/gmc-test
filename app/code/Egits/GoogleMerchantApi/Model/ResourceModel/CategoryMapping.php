<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package    Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model\ResourceModel;

use Egits\GoogleMerchantApi\Api\Data\CategoryMappingInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

/**
 * Class CategoryMapping
 * Category mapping resource model
 */
class CategoryMapping extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('egits_google_category_mapping', CategoryMappingInterface::ENTITY_ID);
    }
}
