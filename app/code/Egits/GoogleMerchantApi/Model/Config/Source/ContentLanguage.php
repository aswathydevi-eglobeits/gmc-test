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
 * Class ContentLanguage
 * Language source class
 */
class ContentLanguage implements OptionSourceInterface
{
    /**
     * Supported languages
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'en' => 'English',
            'es' => 'Spanish',
            'de' => 'German',
            'fr' => 'French',
            'nl' => 'Dutch',
            'pt' => 'Portuguese',
            'cs' => 'Czech',
            'da' => 'Danish',
            'id' => 'Indonesian',
            'he' => 'Hebrew',
            'it' => 'Italian',
            'ja' => 'Japanese',
            'pl' => 'Polish',
            'ru' => 'Russian',
            'ar' => 'Arabic',
            'ko' => 'Korean',
            'sv' => 'Swedish',
            'th' => 'Thai',
            'tr' => 'Turkish',
            'uk' => 'Ukrainian',
            'zh_CN' => 'Chinese',
            'vi' => 'Vietnamese',
        ];
    }
}
