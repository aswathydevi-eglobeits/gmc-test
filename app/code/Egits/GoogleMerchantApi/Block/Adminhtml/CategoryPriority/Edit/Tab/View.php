<?php

namespace Egits\GoogleMerchantApi\Block\Adminhtml\CategoryPriority\Edit\Tab;

use Egits\GoogleMerchantApi\Api\CategoryPriorityRepositoryInterface;
use Egits\GoogleMerchantApi\Model\ResourceModel\CategoryPriority\CollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Block\Adminhtml\Category\AbstractCategory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\Tree;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Block Class for Setting Category Priority
 */
class View extends AbstractCategory implements ArgumentInterface
{
    /**
     * Template for category priority form
     *
     * @var string
     */
    protected $_template = 'categoryPriority/priority.phtml';

    /**
     * @var CategoryPriorityRepositoryInterface
     */
    protected CategoryPriorityRepositoryInterface $categoryPriorityRepository;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $categoryPriorityCollectionFactory;

    /**
     * View constructor.
     *
     * @param Context $context
     * @param Tree $categoryTree
     * @param Registry $registry
     * @param CategoryPriorityRepositoryInterface $categoryPriorityRepository
     * @param CollectionFactory $categoryPriorityCollectionFactory
     * @param CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Tree $categoryTree,
        Registry $registry,
        CategoryPriorityRepositoryInterface $categoryPriorityRepository,
        CollectionFactory $categoryPriorityCollectionFactory,
        CategoryFactory $categoryFactory,
        array $data = []
    ) {
        $this->categoryPriorityRepository = $categoryPriorityRepository;
        $this->categoryPriorityCollectionFactory = $categoryPriorityCollectionFactory;

        parent::__construct(
            $context,
            $categoryTree,
            $registry,
            $categoryFactory,
            $data
        );

        $this->setData('form_name', 'google_category_priority_form');
    }

    /**
     * Get flat list of categories for assigning priority
     *
     * @return array
     */
    public function getCategoriesList(): array
    {
        $list = [];
        $root = $this->getRoot(null, 10);

        if ($root && $root->hasChildren()) {
            foreach ($root->getChildren() as $node) {
                $this->buildCategoryTree($list, $node);
            }
        }

        return $list;
    }

    /**
     * Recursively build category list with nesting level
     *
     * @param array  $list
     * @param object $node
     * @param int    $level
     * @return void
     */
    protected function buildCategoryTree(array &$list, $node, int $level = 0): void
    {
        $list[] = [
            'name'      => $node->getName(),
            'id'        => $node->getId(),
            'level'     => $level,
            'parent_id' => $node->getParentId(),
            'has_child' => $node->hasChildren()
        ];

        if ($node->hasChildren()) {
            foreach ($node->getChildren() as $child) {
                $this->buildCategoryTree($list, $child, $level + 1);
            }
        }
    }

    /**
     * Get category priority and type values
     *
     * @param int $categoryId
     * @return array|null
     */
    public function getCategoryPriorityData(int $categoryId): ?array
    {
        $collection = $this->categoryPriorityCollectionFactory->create();
        $collection->addFieldToFilter('category_id', $categoryId);

        /** @var \Egits\GoogleMerchantApi\Model\CategoryPriority $item */
        $item = $collection->getFirstItem();

        if (!$item || !$item->getId()) {
            return null;
        }

        return [
            'priority' => $item->getData('priority'),
            'type'     => $item->getData('type'),
        ];
    }
}
