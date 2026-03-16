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
 * Class SalePriceEffectiveDate
 * Google merchant api sale price attribute
 */
class SalePriceEffectiveDate extends Base
{
    /**
     * @inheritdoc
     */
    public function convertAttribute($product, $shoppingProduct,$googleAttributes = null)
    {
        $effectiveDateFrom = $this->getGroupAttributeSalePriceEffectiveDateFrom();
        $fromValue = $effectiveDateFrom->getProductAttributeValue($product);

        $effectiveDateTo = $this->getGroupAttributeSalePriceEffectiveDateTo();
        $toValue = $effectiveDateTo->getProductAttributeValue($product);

        $from = $to = null;
        $regularExpression =
            '^((19|20)[0-9][0-9])[-](0[1-9]|1[012])[-](0[1-9]|[12][0-9]|3[01])' .
            '[T]([01][0-9]|[2][0-3])[:]([0-5][0-9])[:]([0-5][0-9])' .
            '([+|-]([01][0-9]|[2][0-3])[:]([0-5][0-9])){0,1}$^';

        if (!empty($fromValue) && preg_match($regularExpression, $fromValue)) {
            $from =  new \DateTime($fromValue, $timezone=null);
        }

        if (!empty($toValue) && preg_match($regularExpression, $toValue)) {
            $to = new \DateTime($toValue, $timezone=null);
        }

        $dateString = null;
        // if we have from an to dates, and if these dates are correct

        if ($from && $to && $from < $to) {
            $dateString = $from->format('c') . '/' . $to->format('c');
        }

        // if we have only "from" date, send "from" day
        if ($from && !$to) {
            $dateString = $from->format('Y-m-d');
        }

        // if we have only "to" date, use "now" date for "from"

        if (!$from && $to) {
            $currentDateTime = date('m/d/Y h:i:s', time());
            $date = new \DateTime($currentDateTime, $timezone=null);
            $currentFromDate=$date->format('c');
            // if "now" date is earlier than "to" date
            if ($from < $to) {
                $dateString = $currentFromDate . '/' . $toValue;
            }
        }

        if ($dateString) {
            $googleAttributes->setSalePriceEffectiveDate($dateString);
            $shoppingProduct->setProductAttributes($googleAttributes);
        }

        return $shoppingProduct;
    }
}
