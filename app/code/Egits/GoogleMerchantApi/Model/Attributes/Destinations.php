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

use Google\Shopping\Merchant\Products\V1\ProductInput;
use Google\Shopping\Type\Destination\DestinationEnum;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;

/**
 * Class Destinations
 * Google merchant api destinations attribute
 */
class Destinations extends Base
{
    /**
     * Google Shopping supported destinations.
     *
     * @var array $supportedValues
     */
    protected array $supportedValues = [
        0 => DestinationEnum::SHOPPING_ADS,        // 1
        1 => DestinationEnum::DISPLAY_ADS,          // 2
        2 => DestinationEnum::FREE_LISTINGS,        // 4
    ];

    /**
     * @inheritdoc
     * @param ProductInterface|Product $product
     * @param ProductInput $shoppingProduct
     * @return ProductInput
     */
    public function convertAttribute($product, $shoppingProduct, $googleAttributes)
    {
        $selectedOptions = $this->googleHelper->getConfig()->getDestinationExclude($product->getStoreId());
        $destinationExcludes = [];
        $destinationInclude = [];
        $destinationExclude = [];
        $availableValues = array_keys($this->supportedValues);

        foreach ($selectedOptions as $option) {
            $destinationExcludes[] = $option;
        }

        $destinationIncludes = array_diff($availableValues, $destinationExcludes);
        foreach ($destinationIncludes as $include) {
            $destinationInclude[] = $this->supportedValues[$include];
        }

        foreach ($destinationExcludes as $exclude) {
            $destinationExclude[] = $this->supportedValues[$exclude];
        }

        $googleAttributes->setIncludedDestinations($destinationInclude);
        $googleAttributes->setExcludedDestinations($destinationExclude);
        $shoppingProduct->setProductAttributes($googleAttributes);

        return $shoppingProduct;
    }
}
