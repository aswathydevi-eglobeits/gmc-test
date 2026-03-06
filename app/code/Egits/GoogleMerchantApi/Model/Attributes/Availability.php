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
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Google\Shopping\Merchant\Products\V1\ProductInput;

/**
 * Class Availability
 * Google merchant api availability attribute
 */
class Availability extends Base
{
    /**
     * @var array
     */
    protected $googleAvailabilityMap = [
        0 => 'out_of_stock',
        1 => 'in_stock'
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
    public function convertAttribute($product, $shoppingProduct)
    {
        $stockItem = $this->stockRegistryProvider->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        );
        $isAvailable = (int) $stockItem->getIsInStock();

        $value = $this->googleAvailabilityMap[$isAvailable];
        $shoppingProduct->setAvailability($value);
        return $shoppingProduct;
    }
}
