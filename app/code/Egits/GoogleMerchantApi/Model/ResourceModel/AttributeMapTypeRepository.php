<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model\ResourceModel;

use Egits\GoogleMerchantApi\Api\AttributeMapTypeRepositoryInterface;
use Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterface;
use Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeSearchResultInterface;
use Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeSearchResultInterfaceFactory;
use Egits\GoogleMerchantApi\Model\AttributeMapTypeFactory;
use Egits\GoogleMerchantApi\Model\ResourceModel\AttributeMapType\Collection;
use Egits\GoogleMerchantApi\Model\ResourceModel\AttributeMapTypeFactory as AttributeMapTypeResourceFactory;
use Egits\GoogleMerchantApi\Model\ResourceModel\AttributeMapType\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Implements CRUD Operations
 *
 * Class AttributeMapTypeRepository
 */
class AttributeMapTypeRepository extends AbstractRepository implements AttributeMapTypeRepositoryInterface
{

    /**
     * @var AttributeMapTypeFactory
     */
    protected $attributeMapTypeFactory;

    /**
     * @var AttributeMapTypeFactory
     */
    protected $attributeMapTypeResourceFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var AttributeMapTypeSearchResultInterfaceFactory
     */
    protected $attributeMapTypeSearchResultFactory;

    /**
     * AttributeMapTypeRepository constructor.
     *
     * @param AttributeMapTypeFactory $attributeMapFactory
     * @param AttributeMapTypeResourceFactory $attributeResourceFactory
     * @param CollectionFactory $collectionFactory
     * @param AttributeMapTypeSearchResultInterfaceFactory $attributeMapTypeSearchResultInterfaceFactory
     */
    public function __construct(
        AttributeMapTypeFactory $attributeMapFactory,
        AttributeMapTypeResourceFactory $attributeResourceFactory,
        CollectionFactory $collectionFactory,
        AttributeMapTypeSearchResultInterfaceFactory $attributeMapTypeSearchResultInterfaceFactory
    ) {
        $this->attributeMapTypeFactory = $attributeMapFactory;
        $this->attributeMapTypeResourceFactory = $attributeResourceFactory;
        $this->collectionFactory = $collectionFactory;
        $this->attributeMapTypeSearchResultFactory = $attributeMapTypeSearchResultInterfaceFactory;
    }

    /**
     * Save Attribute Type
     *
     * @param AttributeMapTypeInterface $attribute
     * @return AttributeMapTypeInterface
     */
    public function save(AttributeMapTypeInterface $attribute)
    {
        $attributeMapTypeResource = $this->attributeMapTypeResourceFactory->create();
        $attributeMapTypeResource->save($attribute);
        return $attribute;
    }

    /**
     * Get attribute mapping by id
     *
     * @param int $id
     * @param AttributeMapTypeInterface|null $attributeMapType
     * @return AttributeMapTypeInterface
     * @throws NoSuchEntityException
     */
    public function getAttributeMapTypeById($id, ?AttributeMapTypeInterface $attributeMapType = null)
    {
        if ($attributeMapType === null) {
            $attributeMapType = $this->attributeMapTypeFactory->create();
        }

        $this->attributeMapTypeResourceFactory->create()->load($attributeMapType, $id);
        if (!$attributeMapType->getId()) {
            throw new NoSuchEntityException(__('Unable to find attribute mapping with ID "%1"', $id));
        }

        return $attributeMapType;
    }

    /**
     * Delete Attribute mapping
     *
     * @param AttributeMapTypeInterface $attribute
     * @return void
     */
    public function delete(AttributeMapTypeInterface $attribute)
    {
        $this->attributeMapTypeResourceFactory->create()->delete($attribute);
    }

    /**
     * Get List of Attribute mappings
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->addFilterToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);
        $collection->load();
        return $this->buildSearchResult($searchCriteria, $collection);
    }

    /**
     * Delete by Id
     *
     * @param int $id
     * @return void
     */
    public function deleteById($id)
    {
        $this->attributeMapTypeResourceFactory->create()->delete($this->getAttributeMapTypeById($id));
    }

    /**
     * Build Search Result Based on collection and search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     * @return AttributeMapTypeSearchResultInterface
     */
    protected function buildSearchResult($searchCriteria, $collection)
    {
        $searchResults = $this->attributeMapTypeSearchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
