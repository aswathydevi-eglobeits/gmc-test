<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 *
 */
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
    public function convertAttribute($product, $shoppingProduct, $googleAttributes = null)
    {
        $value = [];
        $value[] = $this->getProductAttributeValue($product);
        $googleAttributes->setSizes($value);
        $shoppingProduct->setProductAttributes($googleAttributes);

        return $shoppingProduct;
    }
}
