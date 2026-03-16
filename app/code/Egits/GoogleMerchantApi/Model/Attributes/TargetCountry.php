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

use Google\Shopping\Merchant\Products\V1\Shipping;
use Google\Shopping\Merchant\Products\V1\ProductAttributes;
/**
 * Class TargetCountry
 * Google merchant api target country attribute
 */
class TargetCountry extends Base
{
    /**
     * @inheritdoc
     */
    public function convertAttribute($product, $shoppingProduct, $googleAttributes = null)
    {
        $value = $product->getData('current_target_country');

        if ($value) {
            $shipping = new \Google\Shopping\Merchant\Products\V1\Shipping();
            $shipping->setCountry($value);

            $googleAttributes->setShipping([$shipping]);
        }

        return $googleAttributes;
    }
}
