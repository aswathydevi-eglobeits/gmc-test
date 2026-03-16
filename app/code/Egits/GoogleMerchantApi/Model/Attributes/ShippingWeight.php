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

use Google\Shopping\Merchant\Products\V1\ShippingWeight as GoogleShippingWeight;

/**
 * Class ShippingWeight
 * Google merchant api shipping weight attribute
 */
class ShippingWeight extends Base
{
    /**
     * Default weight unit
     *
     * @var string
     */
    public const WEIGHT_UNIT = 'kg';

    /**
     * @inheritdoc
     * @throws \Zend_Date_Exception
     */
    public function convertAttribute($product, $shoppingProduct,$googleAttributes = null)
    {
        $mapValue = $this->getProductAttributeValue($product);
        $weightUnit = $this->googleHelper->getDefaultWeightUnit() ?: self::WEIGHT_UNIT;
        if (!$mapValue) {
            $weight = $this->getGroupAttributeWeight();
            $mapValue = $weight ? $weight->getProductAttributeValue($product) : null;
        }

        if ($mapValue) {
            $shippingWeight = new GoogleShippingWeight();
            $shippingWeight->setValue($mapValue);
            $shippingWeight->setUnit($weightUnit);
            $googleAttributes->setShippingWeight($shippingWeight);
            $shoppingProduct->setProductAttributes($googleAttributes);
        }

        return $shoppingProduct;
    }
}
