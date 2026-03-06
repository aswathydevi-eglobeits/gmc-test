<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category  Egits
 * @package   Egits_GoogleMerchantApi
 * @copyright Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author    Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Block\Adminhtml\CategoryMap\Edit\Tab;

use Egits\GoogleMerchantApi\Model\CategoryMap;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Block\Adminhtml\Category\AbstractCategory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\Tree;
use Magento\Framework\Registry;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Block Class for Category Mapping
 *
 * Class View
 */
class View extends AbstractCategory implements ArgumentInterface
{
    /**
     * Template for category mapping form
     *
     * @var string
     */
    protected $_template = 'category/mapping.phtml';

    /**
     * Google Category mapping
     *
     * @var CategoryMap
     */
    protected $categoryMap;

    /**
     * Google category's
     *
     * @var array
     */
    protected $googleCategoryList = [];

    /**
     * Current category mapping
     *
     * @var array
     */
    protected $categoryMappingItems = [];

    /**
     * Magento category's
     *
     * @var array
     */
    protected $categoryList = [];

    /***
     * Magento Vesrion
     *
     * @var ProductMetadataInterface
     */
    private ProductMetadataInterface $_productMetadata;

    /**
     * View constructor.
     *
     * @param Context                  $context
     * @param Tree                     $categoryTree
     * @param Registry                 $registry
     * @param CategoryMap              $categoryMap
     * @param CategoryFactory          $categoryFactory
     * @param ProductMetadataInterface $_productMetadata
     * @param array                    $data
     */
    public function __construct(
        Context $context,
        Tree $categoryTree,
        Registry $registry,
        CategoryMap $categoryMap,
        CategoryFactory $categoryFactory,
        ProductMetadataInterface $_productMetadata,
        array $data = []
    ) {
        $this->categoryMap = $categoryMap;
        $this->productMetadata = $_productMetadata;
        parent::__construct(
            $context,
            $categoryTree,
            $registry,
            $categoryFactory,
            $data
        );
        $this->setData('form_name', 'google_merchant_category_map_form');
    }

    /**
     * Get Category list
     *
     * @return array
     */
    public function getCategoriesList()
    {
        $list = [];
        $root = $this->getRoot(null, 10);
        if ($root->hasChildren()) {
            foreach ($root->getChildren() as $node) {
                $this->getChildCategories($list, $node);
            }
        }

        return $list;
    }

    /**
     * Get child categories of a category
     *
     * @param array  $list
     * @param object $node
     * @param int    $level
     */
    protected function getChildCategories(&$list, $node, $level = 0)
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
                $this->getChildCategories($list, $child, $level + 1);
            }
        }
    }

    /**
     * Get Google category list
     *
     * @return array
     */
    public function getGoogleCategoryLists()
    {
        $categoryList = $this->getGoogleCategoryAsArray();
        $googleCategoryList = [];
        foreach ($categoryList as $item => $value) {
            $googleCategoryList[] = ['value' => $value, 'data' => (string)$item];
        }

        return $googleCategoryList;
    }

    /**
     * Get Google category as array
     *
     * @return array
     */
    public function getGoogleCategoryAsArray()
    {
        if (empty($this->googleCategoryList)) {
            $this->googleCategoryList = $this->categoryMap->getGoogleCategoryList();
        }

        return $this->googleCategoryList;
    }

    /**
     * Get current category mapping from database
     *
     * @return array
     */
    public function getCategoryMapping()
    {
        $mapping = $this->categoryMap->getGoogleCategoryMapping();
        if (empty($this->categoryMappingItems)) {
            foreach ($mapping as $categoryMapping) {
                $categoryId = $categoryMapping->getCategoryId();
                $this->categoryMappingItems[$categoryId] = $categoryMapping->getGoogleCategoryId();
            }
        }

        return $this->categoryMappingItems;
    }

    /**
     * Get category mapping value for magento category
     *
     * @param int $categoryId
     *
     * @return mixed|string
     */
    public function getMappingValueForCategory($categoryId)
    {
        $categoryMapping = $this->getCategoryMapping();
        $value = '';
        if ($categoryId && isset($categoryMapping[$categoryId])) {
            $value = $categoryMapping[$categoryId];
        }

        return $value;
    }

    /**
     * Get google category label for magento category mapping value.
     *
     * @param int $categoryId
     *
     * @return mixed|string
     */
    public function getGoogleCategoryLabelForCategory($categoryId)
    {
        $googleCategory = $this->getGoogleCategoryAsArray();
        $categoryMapping = $this->getCategoryMapping();
        $value = '';
        if ($categoryId && isset($categoryMapping[$categoryId])) {
            $value = $googleCategory[$categoryMapping[$categoryId]];
        }

        return $value;
    }

    /**
     * Get category map js version.
     *
     * @return string
     */
    public function getJsversion()
    {
        $version = $this->productMetadata->getVersion();

        return version_compare($version, '2.4.4', '<') ? 'CategoryMapGoogle' : 'CategoryMapGoogle244';
    }
}
