<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model\Attributes;

use Egits\GoogleMerchantApi\Helper\GoogleHelper;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ProductRepository;
use Egits\GoogleMerchantApi\Model\ResourceModel\CategoryPriority\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class ProductType
 * Google merchant api product type attribute
 */
class ProductType extends Base
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;
    /**
     * @var CollectionFactory
     */
    private $categoryPriorityCollectionFactory;
    /**
     * @var Configurable
     */
    private $configurableType;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepositoryInterface;

    /**
     * ProductType constructor.
     *
     * @param GoogleHelper $googleHelper
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     * @param CollectionFactory $categoryPriorityCollectionFactory
     * @param Configurable $configurableType
     * @param ProductRepositoryInterface $productRepositoryInterface
     */
    public function __construct(
        GoogleHelper $googleHelper,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        CollectionFactory $categoryPriorityCollectionFactory,
        Configurable $configurableType,
        ProductRepositoryInterface $productRepositoryInterface
    ) {
        parent::__construct($googleHelper, $productRepository);
        $this->categoryRepository = $categoryRepository;
        $this->categoryPriorityCollectionFactory = $categoryPriorityCollectionFactory;
        $this->configurableType = $configurableType;
        $this->productRepositoryInterface = $productRepositoryInterface;
    }

    /**
     * @inheritdoc
     */
    public function convertAttribute($product, $shoppingProduct,$googleAttributes = null)
    {
        $value = 'Home';
        $categoryIds = $product->getCategoryIds();

        if (empty($categoryIds) && $product->getTypeId() === 'simple') {
            $parentIds = $this->configurableType->getParentIdsByChild($product->getId());
            $parentId = array_shift($parentIds);
            if (!empty($parentId)) {
                $parentProduct = $this->productRepository->getById($parentId);
                $categoryIds = $parentProduct->getCategoryIds();
            }
        }

        // Still no categories found after checking parent
        if (empty($categoryIds)) {
            $googleAttributes->setProductTypes([$value]);
        $shoppingProduct->setProductAttributes($googleAttributes);
            return $shoppingProduct;
        }

        // Fetch all priority records for the product's categories
        $priorityCollection = $this->categoryPriorityCollectionFactory->create();
        $priorityCollection->addFieldToFilter('category_id', ['in' => $categoryIds]);

        $priorities = [];
        $categoryType = [];
        $priorityCategoryMap = [];

        foreach ($priorityCollection as $item) {
            $priority = $item->getPriority();
            if ($priority === null) {
                continue;
            }
            $priority = (int)$priority;
            $categoryId = (int)$item->getCategoryId();

            $priorities[] = $priority;
            $categoryType[$item->getCategoryId()] = $item->getType();
            $priorityCategoryMap[$priority][] = $categoryId;
        }

        if (!empty($priorities)) {
            $minPriority = min($priorities);
            $highestPriorityCategoryIds = $priorityCategoryMap[$minPriority];

            // Find the first matching category from the product's category list
            $selectedCategoryId = null;

            if (count($highestPriorityCategoryIds) > 1) {
                foreach ($categoryIds as $productCategoryId) {
                    if (in_array((int)$productCategoryId, $highestPriorityCategoryIds, true)) {
                        $selectedCategoryId = (int)$productCategoryId;
                        break;
                    }
                }
            } else {
                // Only one category with highest priority, take it directly
                $selectedCategoryId = reset($highestPriorityCategoryIds);
            }
            $category = $this->categoryRepository->get($selectedCategoryId, $product->getStoreId());
        } else {
            // No priority data, use the first category
            $category = $this->categoryRepository->get(array_shift($categoryIds), $product->getStoreId());
        }

        $typeValue = '';
        if (array_key_exists($category->getEntityId(), $categoryType)) {
            $typeValue = $categoryType[$category->getEntityId()];
        }
        if ($typeValue == null) {
            // Build breadcrumbs path from parent categories
            $breadcrumbs = [];
            foreach ($category->getParentCategories() as $cat) {
                $breadcrumbs[] = $cat->getName();
            }

            $value = count($breadcrumbs) ? implode(' > ', $breadcrumbs) : $category->getName();
        } else {
            $value = $typeValue;
        }
        $googleAttributes->setProductTypes([$value]);
        $shoppingProduct->setProductAttributes($googleAttributes);
        return $shoppingProduct;
    }
}
