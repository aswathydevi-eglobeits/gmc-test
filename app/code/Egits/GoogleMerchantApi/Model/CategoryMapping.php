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

use Egits\GoogleMerchantApi\Api\Data\CategoryMappingInterface;
use Egits\GoogleMerchantApi\Model\ResourceModel\CategoryMapping as CategoryMappingResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class CategoryMapping
 * Category mapping model
 */
class CategoryMapping extends AbstractModel implements CategoryMappingInterface
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(CategoryMappingResource::class);
        $this->setIdFieldName('entity_id');
    }

    /**
     * Get category Id
     *
     * @return int
     */
    public function getCategoryId()
    {
        return (int)$this->_getData(self::CATEGORY_ID);
    }

    /**
     * Get google category code
     *
     * @return int
     */
    public function getGoogleCategory()
    {
        return (int)$this->_getData(self::GOOGLE_CATEGORY_ID);
    }

    /**
     * Get category Id
     *
     * @param int $id
     * @return $this
     */
    public function setCategoryId($id)
    {
        $this->setData(self::CATEGORY_ID, (int)$id);
        return $this;
    }

    /**
     * Set Google Attribute
     *
     * @param int $id
     * @return $this
     */
    public function setGoogleCategoryId($id)
    {
        $this->setData(self::GOOGLE_CATEGORY_ID, (int)$id);
        return $this;
    }
}
