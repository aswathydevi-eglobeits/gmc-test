<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Ui\Component\Listing\Column\TargetCountry;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 * Target country option provider
 */
class Options implements OptionSourceInterface
{

    /**
     * Google Attribute list
     *
     * @var array
     */
    private $targetCountries
        = [
            ['value' => 'AR', 'label' => 'Argentina'],
            ['value' => 'AU', 'label' => 'Australia'],
            ['value' => 'AT', 'label' => 'Austria'],
            ['value' => 'BE', 'label' => 'Belgium'],
            ['value' => 'BR', 'label' => 'Brazil'],
            ['value' => 'CA', 'label' => 'Canada'],
            ['value' => 'CL', 'label' => 'Chile'],
            ['value' => 'CO', 'label' => 'Colombia'],
            ['value' => 'CZ', 'label' => 'Czechia'],
            ['value' => 'DK', 'label' => 'Denmark'],
            ['value' => 'FR', 'label' => 'France'],
            ['value' => 'DE', 'label' => 'Germany'],
            ['value' => 'HK', 'label' => 'Hong Kong'],
            ['value' => 'HU', 'label' => 'Hungary'],
            ['value' => 'IN', 'label' => 'India'],
            ['value' => 'ID', 'label' => 'Indonesia'],
            ['value' => 'IE', 'label' => 'Ireland'],
            ['value' => 'IL', 'label' => 'Israel'],
            ['value' => 'IT', 'label' => 'Italy'],
            ['value' => 'JP', 'label' => 'Japan'],
            ['value' => 'MY', 'label' => 'Malaysia'],
            ['value' => 'MX', 'label' => 'Mexico'],
            ['value' => 'NL', 'label' => 'Netherlands'],
            ['value' => 'NZ', 'label' => 'New Zealand'],
            ['value' => 'NO', 'label' => 'Norway'],
            ['value' => 'PH', 'label' => 'Philippines'],
            ['value' => 'PL', 'label' => 'Poland'],
            ['value' => 'PT', 'label' => 'Portugal'],
            ['value' => 'RU', 'label' => 'Russia'],
            ['value' => 'SA', 'label' => 'Saudi Arabia'],
            ['value' => 'SG', 'label' => 'Singapore'],
            ['value' => 'KR', 'label' => 'South Korea'],
            ['value' => 'ZA', 'label' => 'South Africa'],
            ['value' => 'ES', 'label' => 'Spain'],
            ['value' => 'SE', 'label' => 'Sweden'],
            ['value' => 'CH', 'label' => 'Switzerland'],
            ['value' => 'TW', 'label' => 'Taiwan'],
            ['value' => 'TH', 'label' => 'Thailand'],
            ['value' => 'TR', 'label' => 'Turkey'],
            ['value' => 'UA', 'label' => 'Ukraine'],
            ['value' => 'AE', 'label' => 'United Arab Emirates'],
            ['value' => 'UK', 'label' => 'United Kingdom'],
            ['value' => 'US', 'label' => 'United States'],
            ['value' => 'VN', 'label' => 'Vietnam']
        ];

    /**
     * @var array
     */
    protected $options = null;

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' ,'label'=>'<value>',,'label'=>'label' ,'label'=>'<label>'), ...)
     */
    public function toOptionArray()
    {
        return $this->targetCountries;
    }
}
