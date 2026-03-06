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
/**
 * Class IsBundle
 * Google merchant api is bundle attribute
 */
class IsBundle extends Base
{

    /**
     * @inheritdoc
     * @param \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product $product
     * @param ProductInput $shoppingProduct
     * @return ProductInput
     */
    public function convertAttribute($product, $shoppingProduct)
    {
        if ($product->getTypeId() == 'bundle') {
            $shoppingProduct->setIsBundle(true);
        }

        return $shoppingProduct;
    }
}
