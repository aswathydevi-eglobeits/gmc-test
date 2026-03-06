<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model;

use Egits\GoogleMerchantApi\Api\Data\FilterInterface;
use Egits\GoogleMerchantApi\Api\FilterRepositoryInterface;
use Egits\GoogleMerchantApi\Api\FilterRepositoryInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class ProductValidator
 * Product validator class for filter product based on product filters
 */
class ProductValidator
{
    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var FilterRepositoryInterfaceFactory
     */
    protected $filterRepositoryFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var int $storeId
     */
    protected $storeId;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var array
     */
    private $validProductIds = [];

    /**
     * ProductValidator constructor.
     *
     * @param Rule $rule
     * @param FilterRepositoryInterfaceFactory $filterRepositoryFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Serializer $serializer
     */
    public function __construct(
        Rule $rule,
        FilterRepositoryInterfaceFactory $filterRepositoryFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Serializer $serializer
    ) {
        $this->rule = $rule;
        $this->filterRepositoryFactory = $filterRepositoryFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->serializer = $serializer;
    }

    /**
     * Get product ids that match filter condition
     *
     * @param array $productIds
     * @param int $storeId
     * @return array
     */
    public function getFilterMatchingProducts($productIds, $storeId)
    {
        $this->validProductIds = $productIds;
        $this->setStoreId($storeId);
        $filters = $this->getAllFilters();
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                $this->rule->setConditions([]);
                $this->rule->setProductsFilter($this->validProductIds);
                $this->rule->setConditionsSerialized($this->getConditionsSerialized($filter->getConditions()));
                $this->rule->setStoreId($this->getStoreId());
                $this->validProductIds = array_keys($this->rule->getFeedMatchingProductIds());
            }
        } else {
            $this->validProductIds = [];
        }

        return $this->validProductIds;
    }

    /**
     * Get all filters
     *
     * @return FilterInterface[]
     */
    protected function getAllFilters()
    {
        /** @var FilterRepositoryInterface $filterRepository */
        $filterRepository = $this->filterRepositoryFactory->create();
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            'main_table.store_id',
            $this->getStoreId()
        )->addFilter(
            'main_table.is_active',
            FilterInterface::ENABLED_FILTER
        )->create();
        $filterResults = $filterRepository->getList($searchCriteria);

        return $filterResults->getTotalCount() > 0 ? $filterResults->getItems() : [];
    }

    /**
     * Get store id
     *
     * @return mixed
     */
    protected function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * Set store Id
     *
     * @param int $storeId
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * Get serialized filter condition
     *
     * @param string $condition
     * @return string
     */
    public function getConditionsSerialized($condition)
    {
        if ($condition) {
            if ($condition[0] == 'a') { // Old serialization format used
                if (interface_exists(SerializerInterface::class)) { // New version of Magento
                    $condition = $this->serializer->serialize(
                        $this->serializer->unserialize($condition)
                    );
                }
            }
        }

        return $condition;
    }
}
