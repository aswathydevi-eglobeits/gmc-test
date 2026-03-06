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
 * Class ProductUom
 * Google merchant api product unit of measurement attribute
 */
class ProductUom extends Base
{

    /**
     * @inheritdoc
     */
    public function convertAttribute($product, $shoppingProduct)
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
            $unitPricingMeasure = new \Google_Service_ShoppingContent_ProductUnitPricingMeasure();
            $unitPricingMeasure->setUnit($basePriceUnit);
            $unitPricingMeasure->setValue($basePriceAmount);
            $unitPricingBaseMeasure = new \Google_Service_ShoppingContent_ProductUnitPricingBaseMeasure();
            $unitPricingBaseMeasure->setUnit($basePriceReferenceUnit);
            $unitPricingBaseMeasure->setValue($basePriceReferenceAmount);
            $shoppingProduct->setUnitPricingMeasure($unitPricingMeasure);
            $shoppingProduct->setUnitPricingBaseMeasure($unitPricingBaseMeasure);
        }

        return $shoppingProduct;
    }
}
