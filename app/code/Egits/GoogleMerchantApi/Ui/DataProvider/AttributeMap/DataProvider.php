<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Ui\DataProvider\AttributeMap;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Egits\GoogleMerchantApi\Model\ResourceModel\AttributeMapType\CollectionFactory as AttributeMapCollectionFactory;

/**
 * Class DataProvider
 * Attribute map data provider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var array $loadedData
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param AttributeMapCollectionFactory $attributeMapCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        AttributeMapCollectionFactory $attributeMapCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $attributeMapCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get Data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        foreach ($items as $attributeMappingType) {
            $result['attribute'] = $attributeMappingType->getData();
            foreach ($attributeMappingType->getAttributeMapping() as $mapping) {
                $mappingId = $mapping->getId();
                $mapping->load($mappingId);
                $result['attribute']['attribute_map_rows_container']['attribute_map_rows_container'][]
                    = $mapping->getData();
            }

            $this->loadedData[$attributeMappingType->getId()] = $result;
        }

        if (!empty($data)) {
            $AttributeMapTypeId = isset($data['attribute']['type_id']) ? $data['customer']['type_id'] : null;
            $this->loadedData[$AttributeMapTypeId] = $data;
        }

        return $this->loadedData;
    }
}
