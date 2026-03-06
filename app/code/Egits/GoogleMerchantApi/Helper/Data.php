<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Helper;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * Google merchant api base helper class
 */
class Data extends AbstractHelper
{
    /**
     * Default target language
     */
    public const DEFAULT_LANGUAGE = 'en';

    /**
     * @var TimezoneInterface
     */
    protected $timeZone;

    /**
     * @var DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * Product attributes cache
     *
     * @var array
     */
    protected $productAttributes;

    /**
     * Attribute labels by store ID
     *
     * @var array
     */
    protected $attributeLabels;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param DateTimeFactory $dateTimeFactory
     * @param TimezoneInterface $timezone
     */
    public function __construct(Context $context, DateTimeFactory $dateTimeFactory, TimezoneInterface $timezone)
    {
        parent::__construct($context);
        $this->dateTimeFactory = $dateTimeFactory;
        $this->timeZone = $timezone;
    }

    /**
     * Return Product attribute by attribute's ID
     *
     * @param  ProductInterface|Product $product
     * @param  int $attributeId
     * @return null|\Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public function getProductAttribute($product, $attributeId)
    {
        if (!isset($this->productAttributes[$product->getId()])) {
            $attributes = $product->getAttributes();
            foreach ($attributes as $attribute) {
                $this->productAttributes[$product->getId()][$attribute->getAttributeId()] = $attribute;
            }
        }

        return isset($this->productAttributes[$product->getId()][$attributeId])
            ? $this->productAttributes[$product->getId()][$attributeId]
            : null;
    }

    /**
     * Return Product Attribute Store Label
     *
     * Set attribute name like frontend label for custom attributes (which wasn't defined by Google)
     *
     * @param ProductAttributeInterface $attribute
     * @param int $storeId Store View Id
     * @return string Attribute Store View Label or Attribute code
     */
    public function getAttributeLabel($attribute, $storeId)
    {
        $attributeId = $attribute->getId();
        $frontendLabel = $attribute->getFrontendLabels();

        if (is_array($frontendLabel)) {
            $frontendLabel = array_shift($frontendLabel);
        }

        if (!isset($this->attributeLabels[$attributeId])) {
            $this->attributeLabels[$attributeId] = $attribute->getStoreLabel();
        }

        if (isset($this->attributeLabels[$attributeId]) && is_array($this->attributeLabels[$attributeId])) {
            return $this->attributeLabels[$attributeId][$storeId];
        } else {
            if (!empty($frontendLabel)) {
                return $frontendLabel;
            } else {
                return $attribute->getAttributeCode();
            }
        }
    }

    /**
     * Convert a string to camelCase
     *
     * @param  string $value
     * @return string
     */
    public function camelCase($value)
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));
        $value = str_replace(' ', '', $value);
        $value[0] = strtolower($value[0]);
        return $value;
    }

    /**
     * Remove characters and words not allowed by Google Content in title and content (description).
     * (to avoid "Expected response code 200, got 400.
     * Reason: There is a problem with the character encoding of this attribute")
     *
     * @param string $string
     * @return string
     */
    public function cleanAtomAttribute($string)
    {
        return substr(preg_replace('/[\pC¢€•—™°½]|shipping/ui', '', $string), 0, 3500);
    }

    /**
     * Convert Google Content date format to unix timestamp
     *
     * Ex. 2008-12-08T16:57:23Z -> 2008-12-08 16:57:23
     *
     * @param string $gContentDate Google Content datetime
     * @return string
     */
    public function convertContentDateToTimestamp($gContentDate)
    {
        return $this->timeZone->date($gContentDate)->format(DateTime::DATETIME_PHP_FORMAT);
    }

    /**
     * Normalize attribute's name.
     * The name has to be in lower case and the words are separated by symbol "_".
     * For instance: Meta Description = meta_description
     *
     * @param string $name
     * @return string
     */
    public function normalizeName($name)
    {
        return strtolower(preg_replace('/[\s_]+/', '_', $name));
    }

    /**
     * Google Account target language
     *
     * TODO change it to configuration for country
     *
     * @return string Two-letters country ISO code
     */
    public function getContentLanguage()
    {
        return self::DEFAULT_LANGUAGE;
    }

    /**
     * Get Google Content Product ID
     *
     * @param string $sku
     * @param int $storeId
     * @return string
     */
    public function buildContentProductId($sku, $storeId)
    {
        return 'GP-' . $sku . '-' . $storeId;
    }

    /**
     * Parse Exception Response Body
     *
     * @param string $message Exception message to parse
     * @param null|ProductInterface $product
     * @return string
     */
    public function parseGdataExceptionMessage($message, $product = null)
    {
        $result = [];
        foreach (explode("\n", $message) as $row) {
            if (trim($row) == '') {
                continue;
            }

            if (strip_tags($row) == $row) {
                $row = preg_replace('/@ (.*)/', __("See '\\1'"), $row);
                if ($product) {
                    $row .= ' ' . __(
                        "for product '%1' (in '%2' store)",
                        $product->getName(),
                        $product->getStore()->getName()
                    );
                }

                $result[] = $row;
                continue;
            }

            // parse not well-formatted xml
            preg_match_all('/(reason|field|type)=\"([^\"]+)\"/', $row, $matches);

            if (is_array($matches) && count($matches) == 3) {
                if (is_array($matches[1]) && !empty($matches[1])) {
                    $c = count($matches[1]);
                    for ($i = 0; $i < $c; $i++) {
                        if (isset($matches[2][$i])) {
                            $result[] = ucfirst($matches[1][$i]) . ': ' . $matches[2][$i];
                        }
                    }
                }
            }
        }

        return implode(". ", $result);
    }

    /**
     * Get default weight unit
     *
     * @return mixed
     */
    public function getDefaultWeightUnit()
    {
        return $this->scopeConfig->getValue('general/locale/weight_unit', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get time zone data
     *
     * @return TimezoneInterface
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }
}
