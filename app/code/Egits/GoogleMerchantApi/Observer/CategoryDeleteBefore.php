<?php
/**
 *
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_
 * @copyright   Copyright (c) 2018 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Observer;

use Egits\GoogleMerchantApi\Model\ResourceModel\CategoryMappingRepository;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class CategoryDeleteBefore
 * Product delete observer
 */
class CategoryDeleteBefore implements ObserverInterface
{
    /**
     * @var CategoryMappingRepository
     */
    private CategoryMappingRepository $categoryMappingRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * AddProductQueue constructor.
     *
     * @param CategoryMappingRepository $categoryMappingRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CategoryMappingRepository $categoryMappingRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->categoryMappingRepository = $categoryMappingRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Product save after
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $category = $observer->getEvent()->getCategory();
            if ($category->getId()) {
                $searchCriteria = $this->searchCriteriaBuilder->addFilter('category_id', $category->getId())
                ->create();

                $categoryMappings = $this->categoryMappingRepository->getList($searchCriteria);
                foreach ($categoryMappings as $categoryMapping) {
                    $this->categoryMappingRepository->delete($categoryMapping);
                }
            }
        } catch (\Exception $exception) {
            $this->googleHelper->writeDebugLogFile($exception);
            return;
        }
    }
}
