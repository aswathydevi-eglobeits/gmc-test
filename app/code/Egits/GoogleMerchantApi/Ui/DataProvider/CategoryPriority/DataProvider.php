<?php

namespace Egits\GoogleMerchantApi\Ui\DataProvider\CategoryPriority;

use Egits\GoogleMerchantApi\Model\CategoryPriority;
use Egits\GoogleMerchantApi\Model\ResourceModel\CategoryPriority\Collection;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Egits\GoogleMerchantApi\Model\ResourceModel\CategoryPriority\CollectionFactory;

/**
 * Class DataProvider
 * DataProvider for CategoryPriority UI component.
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * DataProvider constructor.
     *
     * @param string            $name
     * @param string            $primaryFieldName
     * @param string            $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array             $meta
     * @param array             $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     *  Retrieve data for UI component form
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

        /** @var CategoryPriority $categoryPriority */
        foreach ($items as $categoryPriority) {
            $this->loadedData[$categoryPriority->getId()]['category_priority'] = $categoryPriority->getData();
        }

        return $this->loadedData;
    }
}
