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
     * @param \Google_Service_ShoppingContent_Product $shoppingProduct
     * @return \Google_Service_ShoppingContent_Product
     */
    public function convertAttribute($product, $shoppingProduct)
    {
        $value = [];
        $value[] = $this->getProductAttributeValue($product);
        $shoppingProduct->setSizes($value);
        return $shoppingProduct;
    }
}
