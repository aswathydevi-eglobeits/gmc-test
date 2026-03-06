<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model\Rule\Condition;

use Magento\Backend\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as CatalogProduct;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Registry;
use Magento\Rule\Model\Condition\Context;

/**
 * Class Product
 * Product attributes conditions for filter
 */
class Product extends \Magento\CatalogRule\Model\Rule\Condition\Product
{
    /** @var Registry */
    private $registry;

    /** @var  array|null */
    private $categoryProductLink;

    /**
     * @var Type
     */
    private $productType;

    /**
     * Product constructor.
     *
     * @param Context $context
     * @param Data $backendData
     * @param Config $config
     * @param ProductFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param CatalogProduct $productResource
     * @param Collection $attrSetCollection
     * @param FormatInterface $localeFormat
     * @param Registry $registry
     * @param Type $productType
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendData,
        Config $config,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        CatalogProduct $productResource,
        Collection $attrSetCollection,
        FormatInterface $localeFormat,
        Registry $registry,
        Type $productType,
        array $data
    ) {
        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $data
        );

        $this->productType = $productType;
        $this->registry = $registry;
    }

    /**
     * Get Available Product in categories
     *
     * @param AbstractModel $object
     * @return array
     */
    public function getAvailableProductInCategories(AbstractModel $object)
    {
        $connection = $object->getResource()->getConnection();

        if ($registryIds = $this->registry->registry('filter_matching_product_ids')) {
            if ($this->categoryProductLink === null) {
                if (!$registryIds) {
                    $this->categoryProductLink = [];
                } else {
                    $select = $object->getResource()->getConnection()->select()->distinct()->from(
                        $object->getResource()->getTable('catalog_category_product'),
                        ['product_id', 'GROUP_CONCAT(category_id)']
                    )->where('product_id IN (?)', $registryIds)->group('product_id');
                    $this->categoryProductLink = $connection->fetchPairs($select);
                }
            }

            if (isset($this->categoryProductLink[(int)$object->getEntityId()])) {
                return explode(',', $this->categoryProductLink[(int)$object->getEntityId()]);
            }

            return [];
        }

        $select = $object->getResource()->getConnection()->select()->distinct()->from(
            $object->getResource()->getTable('catalog_category_product'),
            ['category_id']
        )->where(
            'product_id = ?',
            (int)$object->getEntityId()
        );

        return $connection->fetchCol($select);
    }

    /**
     * Validate
     *
     * @param AbstractModel $model
     * @return bool
     */
    public function validate(AbstractModel $model)
    {
        $attributeCode = $this->getAttribute();

        if ('category_ids' == $attributeCode) {
            return $this->validateAttribute($this->getAvailableProductInCategories($model));
        }

        $oldAttrValue = $model->hasData($attributeCode) ? $model->getData($attributeCode) : null;
        $this->_setAttributeValue($model);
        $result = $this->validateAttribute($model->getData($this->getAttribute()));
        $this->_restoreOldAttrValue($model, $oldAttrValue);

        return (bool)$result;
    }

    /**
     * Load Attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        parent::loadAttributeOptions();

        $attributeOptions = $this->getAttributeOption();

        $attributeOptions['type_id'] = __('Type');

        // Override weird default attribute names
        $titles = [
            'status'                    => __('Status'),
            'quantity_and_stock_status' => __('Stock Status')
        ];

        foreach ($titles as $code => $title) {
            if (isset($attributeOptions[$code])) {
                $attributeOptions[$code] = $title;
            }
        }

        asort($attributeOptions);

        $this->setAttributeOption($attributeOptions);

        return $this;
    }

    /**
     * Get Value of select options
     *
     * @return array
     */
    public function getValueSelectOptions()
    {
        if ($this->getAttribute() == 'type_id') {
            $this->setData('value_select_options', $this->productType->getOptions());

            $this->getAttributeObject()->setFrontendInput('select');
        }

        return parent::getValueSelectOptions();
    }
}
