<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package    Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Destinations
 * Destination source class
 */
class Destinations implements OptionSourceInterface
{
    /**
     * Feed Destinations
     */
    public const USE_SHOPPING_ADS = 0;
    public const USE_DISPLAY_ADS = 1;
    public const USE_SHOPPING_ACTION = 2;

    /**
     * Retrieve all product destinations
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::USE_SHOPPING_ADS, 'label' => __('Shopping Ads')],
            ['value' => self::USE_DISPLAY_ADS, 'label' => __('Display Ads')],
            ['value' => self::USE_SHOPPING_ACTION, 'label' => __('Shopping Actions')],
        ];
    }
}
