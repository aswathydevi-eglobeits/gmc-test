<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Ui\DataProvider\Filter;

use Egits\GoogleMerchantApi\Model\Filter;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Egits\GoogleMerchantApi\Model\ResourceModel\Filter\CollectionFactory;

/**
 * Class DataProvider
 * Filer data provider
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
     * @param CollectionFactory $filterCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $filterCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $filterCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
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
