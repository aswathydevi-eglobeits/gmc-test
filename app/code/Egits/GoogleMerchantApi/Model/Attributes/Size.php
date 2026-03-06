<?php
namespace Egits\GoogleMerchantApi\Model\Attributes;

use Google\Shopping\Merchant\Products\V1\ProductInput;
use Google\Shopping\Merchant\Products\V1\ProductAttributes;

/**
 * Class Size
 * Google merchant api size attribute
 */
class Size extends Base
{
    /**
     * Convert Attribute
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product $product
     * @param ProductInput $shoppingProduct
     * @param ProductAttributes $googleAttributes
     * @return ProductInput
     */
    public function convertAttribute($product, $shoppingProduct, $googleAttributes)
    {
        $value = [];
        $value[] = $this->getProductAttributeValue($product);

        // FIX: setSizes() belongs on ProductAttributes, not ProductInput
        $googleAttributes->setSizes($value);
        $shoppingProduct->setProductAttributes($googleAttributes);

        return $shoppingProduct;
    }
}
