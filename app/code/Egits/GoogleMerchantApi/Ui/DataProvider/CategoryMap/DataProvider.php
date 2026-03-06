<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Ui\DataProvider\CategoryMap;

use Egits\GoogleMerchantApi\Model\Filter;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Egits\GoogleMerchantApi\Model\ResourceModel\CategoryMapping\CollectionFactory;

/**
 * Class DataProvider
 * Category map data provider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $categoryMapCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $categoryMapCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $categoryMapCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get date
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        $this->loadedData = [];
        /** @var Filter $filter */
        foreach ($items as $filter) {
            $this->loadedData[$filter->getId()]['filter'] = $filter->getData();
        }

        return $this->loadedData;
    }
}
