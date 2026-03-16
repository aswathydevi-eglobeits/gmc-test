<?php
namespace Egits\GoogleMerchantApi\Model\Attributes;

use Egits\GoogleMerchantApi\Helper\GoogleHelper;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Google\Shopping\Merchant\Products\V1\ProductInput;
use Google\Shopping\Merchant\Products\V1\ProductAttributes;

class Availability extends Base
{
    const OUT_OF_STOCK = 2;
    const IN_STOCK     = 1;
    const PREORDER     = 3;
    const BACKORDER    = 4;

    protected $googleAvailabilityMap = [
        0 => self::OUT_OF_STOCK,
        1 => self::IN_STOCK
    ];

    /**
     * @var StockRegistryProviderInterface
     */
    private StockRegistryProviderInterface $stockRegistryProvider;

    /**
     * @param GoogleHelper $googleHelper
     * @param ProductRepository $productRepository
     * @param StockRegistryProviderInterface $stockRegistryProvider
     */
    public function __construct(
        GoogleHelper $googleHelper,
        ProductRepository $productRepository,
        StockRegistryProviderInterface $stockRegistryProvider
    ) {
        parent::__construct($googleHelper, $productRepository);
        $this->stockRegistryProvider = $stockRegistryProvider;
    }

    /**
     * Converting attribute
     *
     * @param ProductInterface|Product $product
     * @param ProductInput $shoppingProduct
     * @return ProductInput
     */
    public function convertAttribute($product, $shoppingProduct,$googleAttributes = null)
    {
        $stockItem = $this->stockRegistryProvider->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        );
        $isAvailable = (int) $stockItem->getIsInStock();
        $value = $this->googleAvailabilityMap[$isAvailable] ?? self::OUT_OF_STOCK;

        $googleAttributes->setAvailability($value);
        $shoppingProduct->setProductAttributes($googleAttributes);
        return $shoppingProduct;
    }
}
