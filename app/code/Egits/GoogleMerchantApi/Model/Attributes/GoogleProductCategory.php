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
use Egits\GoogleMerchantApi\Model\CategoryMapping;
use Egits\GoogleMerchantApi\Model\ResourceModel\CategoryMapping\CollectionFactory;
use Egits\GoogleMerchantApi\Model\ResourceModel\CategoryMapping\Collection;
use Google\Shopping\Merchant\Products\V1\ProductInput;
use Magento\Catalog\Model\ProductRepository;

/**
 * Class GoogleProductCategory
 * Google merchant api google category attribute
 */
class GoogleProductCategory extends Base
{

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * GoogleProductCategory constructor.
     *
     * @param GoogleHelper $googleHelper
     * @param ProductRepository $productRepository
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        GoogleHelper $googleHelper,
        ProductRepository $productRepository,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($googleHelper, $productRepository);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     * @param \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product $product
     * @param ProductInput $shoppingProduct
     * @return ProductInput
     */
    public function convertAttribute($product, $shoppingProduct,$googleAttributes)
    {
        $productCategories = $product->getCategoryIds();
        $productMappedCategories = $this->getMappedCategories($productCategories);
        if ($productMappedCategories) {
            $value = $productMappedCategories->getGoogleCategory();
        } else {
            $value = $this->googleHelper->getConfig()->getDefaultGoogleCategory($product->getStoreId());
        }

        $googleAttributes->setGoogleProductCategory($value);
        $shoppingProduct->setProductAttributes($googleAttributes);

        return $shoppingProduct;
    }

    /**
     * Get mapped category by product category ids.
     *
     * @param array $productCategories
     * @return \Magento\Framework\DataObject|CategoryMapping
     */
    protected function getMappedCategories($productCategories)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('category_id', ['in' => $productCategories]);
        $collection->load();
        $mapCategory = null;
        if ($collection->getSize() > 0) {
            $mapCategory = $collection->getFirstItem();
        }

        return $mapCategory;
    }
}
