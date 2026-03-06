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
 * Class AgeGroup
 * Age group source class
 */
class AgeGroup extends AbstractSource
{
    /**
     * Age group default for google
     * Default age group if no age group on product
     */
    public const AGE_GROUP_DEFAULT_FOR_GOOGLE = 'adult';

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            ['label' => __(' '), 'value' => ''],
            ['label' => __('Newborn (0–3) Months'), 'value' => 'newborn'],
            ['label' => __('Infant (3-12) Months'), 'value' => 'infant'],
            ['label' => __('Toddler (1-5) Year old'), 'value' => 'toddler'],
            ['label' => __('Kids (5-13) Years old'), 'value' => 'kids'],
            ['label' => __('Adult'), 'value' => 'adult']
        ];

        return $this->_options;
    }
}
