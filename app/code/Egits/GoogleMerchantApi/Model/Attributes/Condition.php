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

    private const CONDITION_MAP = [
        self::CONDITION_NEW         => 0,
        self::CONDITION_USED        => 1,
        self::CONDITION_REFURBISHED => 2,
    ];

    /**
     * @inheritdoc
     */
    public function convertAttribute($product, $shoppingProduct, $googleAttributes)
    {
        $mapValue = $this->getProductAttributeValue($product);

        $condition = self::CONDITION_MAP[$mapValue]
            ?? self::CONDITION_MAP[self::CONDITION_NEW];

        $googleAttributes->setCondition($condition);
        $shoppingProduct->setProductAttributes($googleAttributes);
        return $shoppingProduct;
    }
}
