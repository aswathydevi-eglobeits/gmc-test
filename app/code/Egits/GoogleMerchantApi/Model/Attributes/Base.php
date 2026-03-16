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

use Egits\GoogleMerchantApi\Api\Data\AttributeInterface;
use Magento\Framework\Phrase;
use Egits\GoogleMerchantApi\Helper\GoogleHelper;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\DataObject;

/**
 * Class Base
 * Base attribute class for google attributes
 */
class Base extends DataObject implements AttributeInterface
{
    /**
     * Google Content attribute types
     *
     * @var string
     */
    private const ATTRIBUTE_TYPE_TEXT = 'text';
    private const ATTRIBUTE_TYPE_INT = 'int';
    private const ATTRIBUTE_TYPE_FLOAT = 'float';
    private const ATTRIBUTE_TYPE_URL = 'url';

    /**
     * @var GoogleHelper
     */
    protected $googleHelper;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * Base constructor.
     *
     * @param GoogleHelper $googleHelper
     * @param ProductRepository $productRepository
     */
    public function __construct(GoogleHelper $googleHelper, ProductRepository $productRepository)
    {
        parent::__construct();
        $this->googleHelper = $googleHelper;
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */

    public function convertAttribute($product, $shoppingProduct, $googleAttributes = null)
    {
        if (!$this->getName()) {
            return $shoppingProduct;
        }
        $value = $this->getProductAttributeValue($product);
        if ($value && $googleAttributes !== null) {
            $name   = $this->googleHelper->camelCase($this->getName());
            $setter = 'set' . ucfirst($name);
            if (method_exists($googleAttributes, $setter)) {
                $googleAttributes->$setter($value);
            }
        }
        $shoppingProduct->setProductAttributes($googleAttributes);
        return $shoppingProduct;
    }

    /**
     * Get current attribute value for specified product
     *
     * @param ProductInterface $product
     * @return null|string
     * @throws \Zend_Date_Exception
     */
    public function getProductAttributeValue($product)
    {
        if (!$this->getAttrId()) {
            return null;
        }

        $productAttribute = $this->googleHelper
            ->getProductAttribute($product, $this->getAttrId());
        if (!$productAttribute) {
            return null;
        }

        if ($productAttribute->getFrontendInput() == 'date'
            || $productAttribute->getBackendType() == 'date'
        ) {
            $value = $product->getData($productAttribute->getAttributeCode());
            $regularExpression =
                '^((19|20)[0-9][0-9])[-](0[1-9]|1[012])[-](0[1-9]|[12][0-9]|3[01])' .
                '[T| ]([01][0-9]|[2][0-3])[:]([0-5][0-9])[:]([0-5][0-9])' .
                '([+|-]([01][0-9]|[2][0-3])[:]([0-5][0-9])){0,1}$^';

            if (empty($value) || !preg_match($regularExpression, $value)) {
                return null;
            }

            $date = new \DateTime($value, $timezone = null);
            $value = $date->format('c');
        } else {
            $value = $productAttribute->getFrontend()->getValue($product);
            if ($value instanceof Phrase && in_array($value->getText(), ['Yes', 'No', ' '])) {
                $value = null;
            }

            // we need size value as M for medium
            if ($this->getName() == 'size') {
                $valueOptions = $productAttribute->getOptions();
                foreach ($valueOptions as $option) {
                    if ($option->getLabel() == $value) {
                        $value = $option->getLabel();
                    }
                }
            }
        }

        return $value;
    }

    /**
     * Return Google Content Attribute Type By Product Attribute
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @return string Google Content Attribute Type
     */
    public function getGoogleContentAttributeType($attribute)
    {
        $typesMapping = [
            'price' => self::ATTRIBUTE_TYPE_FLOAT,
            'decimal' => self::ATTRIBUTE_TYPE_INT,
        ];
        if (isset($typesMapping[$attribute->getFrontendInput()])) {
            return $typesMapping[$attribute->getFrontendInput()];
        } elseif (isset($typesMapping[$attribute->getBackendType()])) {
            return $typesMapping[$attribute->getBackendType()];
        } else {
            return self::ATTRIBUTE_TYPE_TEXT;
        }
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get PwaUrl
     *
     * @param string $baseUrl
     * @param string $url
     * @return mixed|string
     */
    protected function getPwaUrl($baseUrl, $url)
    {
        if ($pwaUrl = $this->googleHelper->getConfig()->getPwaUrl()) {
            $url = str_replace($baseUrl, $pwaUrl, $url);
        }
        return $url;
    }
}
