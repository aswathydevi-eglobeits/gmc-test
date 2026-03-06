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

use Magento\Framework\File\Csv;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class CategoryMap
 *
 * Category map source model
 */
class CategoryMap implements OptionSourceInterface
{
    /**
     * @var Csv
     */
    protected $csvProcessor;

    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    protected $fixtureManager;

    /**
     * @var ResourceModel\CategoryMapping\CollectionFactory
     */
    protected $categoryMappingCollectionFactory;

    /**
     * CategoryMap constructor.
     *
     * @param ResourceModel\CategoryMapping\CollectionFactory $collectionFactory
     * @param Csv $csvProcessor
     * @param SampleDataContext $sampleDataContext
     */
    public function __construct(
        ResourceModel\CategoryMapping\CollectionFactory $collectionFactory,
        Csv $csvProcessor,
        SampleDataContext $sampleDataContext
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->categoryMappingCollectionFactory = $collectionFactory;
        $this->csvProcessor = $csvProcessor;
    }

    /**
     * Get google category list
     */
    public function getGoogleCategoryList()
    {
        $fileName = $this->fixtureManager->getFixture('Egits_GoogleMerchantApi::fixtures/taxonomy-en-US.csv');
        $importProductRawData = $this->csvProcessor->setDelimiter('#')->getData($fileName);
        $category = [];
        foreach ($importProductRawData as $dataRow) {
            $category[$dataRow[0]] = $dataRow[1];
        }

        return $category;
    }

    /**
     * Get current mapping collection
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getGoogleCategoryMapping()
    {
        return $this->categoryMappingCollectionFactory->create()->getItems();
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return $this->getGoogleCategoryList();
    }
}
