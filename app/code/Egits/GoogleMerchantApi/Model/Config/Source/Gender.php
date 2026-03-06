<?php
/**
 *
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Gender
 * Gender source class
 */
class Gender extends AbstractSource
{
    /**
     * Gender default for google
     * default gender provided if no gender set on product
     */
    public const GENDER_DEFAULT_FOR_GOOGLE = 'unisex';

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            ['label' => __(' '), 'value' => ''],
            ['label' => __('Male'), 'value' => 'male'],
            ['label' => __('Female'), 'value' => 'female'],
            ['label' => __('Unisex'), 'value' => 'unisex'],
        ];

        return $this->_options;
    }
}
