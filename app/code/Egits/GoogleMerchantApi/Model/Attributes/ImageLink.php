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

use Egits\GoogleMerchantApi\Helper\Data;
use Egits\GoogleMerchantApi\Helper\GoogleHelper;
use Google\Shopping\Merchant\Products\V1\ProductAttributes;
use Google\Shopping\Merchant\Products\V1\ProductInput;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Area;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ImageLink
 * Google merchant api image link attribute
 */
class ImageLink extends Base
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * ImageLink constructor.
     *
     * @param GoogleHelper $googleHelper
     * @param StoreManagerInterface $storeManager
     * @param BlockFactory $blockFactory
     * @param Emulation $appEmulation
     * @param ProductRepository $productRepository
     */
    public function __construct(
        GoogleHelper $googleHelper,
        StoreManagerInterface $storeManager,
        BlockFactory $blockFactory,
        Emulation $appEmulation,
        ProductRepository $productRepository
    ) {
        parent::__construct($googleHelper, $productRepository);
        $this->storeManager = $storeManager;
        $this->blockFactory = $blockFactory;
        $this->appEmulation = $appEmulation;
    }

    /**
     * @inheritdoc
     * @param \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product $product
     * @param ProductInput  $shoppingProduct
     * @return ProductInput
     */
    public function convertAttribute($product, $shoppingProduct)
    {
        $productImageItems = $product->getMediaGalleryImages()->getItems();
        if (empty($productImageItems)) {
            $parent = $product->getData('item_parent_product');
            if ($parent && $parent->getId()) {
                $productImageItems = $parent->getMediaGalleryImages()->getItems();
            }
        }

        $mainImage = array_shift($productImageItems);
        if ($mainImage && $mainImage->getId()) {
            $url = $mainImage->getUrl();
        } else {
            $url = $this->getImageUrl($product, 'product_page_image_small');
        }

        if ($url && $url != "no_selection") {
            $url = $this->getPwaUrl($product->getStore()->getBaseUrl(), $url);
            $attributes = new ProductAttributes();
            $attributes->setImageLink($url);
            $shoppingProduct->setProductAttributes($attributes);
        }

        $additionalImages = [];
        foreach ($productImageItems as $item) {
            if (count($additionalImages) < 10) {
                $additionalImages[] = $item->getUrl();
            }
        }

        if (count($additionalImages) > 0) {
            foreach ($additionalImages as &$additionalImageUrl) {
                $additionalImageUrl = $this->getPwaUrl($product->getStore()->getBaseUrl(), $additionalImageUrl);
            }
            $attributes = $shoppingProduct->getProductAttributes() ?? new ProductAttributes();
            $attributes->setAdditionalImageLinks($additionalImages);
            $shoppingProduct->setProductAttributes($attributes);
        }

        return $shoppingProduct;
    }

    /**
     * Get Image Url
     *
     * @param  object $product
     * @param  string $imageType
     * @return mixed
     */
    protected function getImageUrl($product, $imageType = '')
    {
        $storeId = $product->getStoreId();

        $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);

        $imageBlock = $this->blockFactory->createBlock(ListProduct::class);
        $productImage = $imageBlock->getImage($product, $imageType);
        $imageUrl = $productImage->getImageUrl();

        $this->appEmulation->stopEnvironmentEmulation();

        return $imageUrl;
    }
}
