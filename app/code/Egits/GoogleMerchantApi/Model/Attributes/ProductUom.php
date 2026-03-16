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

use Google\Shopping\Merchant\Products\V1\UnitPricingBaseMeasure;
use Google\Shopping\Merchant\Products\V1\UnitPricingMeasure;

/**
 * Class ProductUom
 * Google merchant api product unit of measurement attribute
 */
class ProductUom extends Base
{

    /**
     * @inheritdoc
     */
    public function convertAttribute($product, $shoppingProduct,$googleAttributes = null)
    {
        $availableUnits = [
            'mg',
            'g',
            'kg',
            'ml',
            'cl',
            'l',
            'cbm',
            'cm',
            'm',
            'sqm'
        ];

        $basePriceAmount = $product->getBasePriceAmount();
        $basePriceUnit = !empty($product->getBasePriceUnit()) ? strtolower($product->getBasePriceUnit()) : '';
        $unitPricingMeasure = $basePriceAmount . ' ' . $basePriceUnit;
        $basePriceReferenceAmount = $product->getBasePriceBaseAmount();
        $basePriceReferenceUnit = !empty($product->getBasePriceBaseUnit())
            ? strtolower($product->getBasePriceBaseUnit()) : '';
        $unitPricingBaseMeasure = $basePriceReferenceAmount . ' ' . $basePriceReferenceUnit;

        // skip attribute if unit not available
        if (!in_array($basePriceUnit, $availableUnits) || !in_array($basePriceReferenceUnit, $availableUnits)) {
            return $shoppingProduct;
        }

        if (!empty($basePriceAmount) && !empty($basePriceReferenceAmount)) {
            $unitPricingMeasure = new UnitPricingMeasure();
            $unitPricingMeasure->setUnit($basePriceUnit);
            $unitPricingMeasure->setValue($basePriceAmount);
            $unitPricingBaseMeasure = new UnitPricingBaseMeasure();
            $unitPricingBaseMeasure->setUnit($basePriceReferenceUnit);
            $unitPricingBaseMeasure->setValue($basePriceReferenceAmount);
            $googleAttributes->setUnitPricingMeasure($unitPricingMeasure);
            $googleAttributes->setUnitPricingBaseMeasure($unitPricingBaseMeasure);
            $shoppingProduct->setProductAttributes($googleAttributes);
        }

        return $shoppingProduct;
    }
}
