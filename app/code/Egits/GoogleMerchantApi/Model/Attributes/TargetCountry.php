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

/**
 * Class TargetCountry
 * Google merchant api target country attribute
 */
class TargetCountry extends Base
{
    /**
     * @inheritdoc
     */
    public function convertAttribute($product, $shoppingProduct)
    {
        $value = $product->getData('current_target_country');
        $shoppingProduct->setTargetCountry($value);
        return $shoppingProduct;
    }
}
