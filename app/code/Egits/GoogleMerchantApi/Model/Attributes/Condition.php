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
 * Class Condition
 * Google merchant api product condition attribute
 */
class Condition extends Base
{
    /**
     * Available condition values
     *
     * @var string
     */
    public const CONDITION_NEW = 'new';
    public const CONDITION_USED = 'used';
    public const CONDITION_REFURBISHED = 'refurbished';

    /**
     * @inheritdoc
     */
    public function convertAttribute($product, $shoppingProduct)
    {
        $availableConditions = [
            self::CONDITION_NEW,
            self::CONDITION_USED,
            self::CONDITION_REFURBISHED
        ];

        $mapValue = $this->getProductAttributeValue($product);
        if ($mapValue && in_array($mapValue, $availableConditions)) {
            $condition = $mapValue;
        } else {
            $condition = self::CONDITION_NEW;
        }

        return $shoppingProduct->setCondition($condition);
    }
}
