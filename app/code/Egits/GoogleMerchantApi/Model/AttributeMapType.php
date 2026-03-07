<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model;

use Egits\GoogleMerchantApi\Api\Data\AttributeMappingInterface;
use Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterface;
use Egits\GoogleMerchantApi\Api\Data\ProductsInterface;
use Egits\GoogleMerchantApi\Api\Data\AttributeInterface;
use Egits\GoogleMerchantApi\Helper\GoogleHelper;
use Egits\GoogleMerchantApi\Model\Config\Source\AgeGroup;
use Egits\GoogleMerchantApi\Model\Config\Source\Gender;
use Egits\GoogleMerchantApi\Model\ResourceModel\AttributeMapping\Collection;
use Egits\GoogleMerchantApi\Model\ResourceModel\AttributeMapType as AttributeMapResourceModel;
use Google\Shopping\Merchant\Products\V1\ProductAttributes;
use Google\Shopping\Merchant\Products\V1\AgeGroup as MerchantAgeGroup;
use Google\Shopping\Merchant\Products\V1\Gender as MerchantGender;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use \Magento\Framework\Model\Context;
use Egits\GoogleMerchantApi\Api\SetParentProductOnChildInterface;
use Google\Shopping\Merchant\Products\V1\ProductInput;

/**
 * Class AttributeMapType
 * Attribute map type model
 */
class AttributeMapType extends AbstractModel implements AttributeMapTypeInterface
{
    /**
     * Entity type field for attribute map type
     */
    public const ENTITY_TYPE = 'attribute';

    /**
     * Map Magento AgeGroup string values to Merchant API v1 enum integers
     */
    private const AGE_GROUP_MAP = [
        'adult'   => MerchantAgeGroup::ADULT,
        'kids'    => MerchantAgeGroup::KIDS,
        'toddler' => MerchantAgeGroup::TODDLER,
        'infant'  => MerchantAgeGroup::INFANT,
        'newborn' => MerchantAgeGroup::NEWBORN,
        ''        => MerchantAgeGroup::AGE_GROUP_UNSPECIFIED,
    ];

    /**
     * Map Magento Gender string values to Merchant API v1 enum integers
     */
    private const GENDER_MAP = [
        'male'   => MerchantGender::MALE,
        'female' => MerchantGender::FEMALE,
        'unisex' => MerchantGender::UNISEX,
        ''       => MerchantGender::GENDER_UNSPECIFIED,
    ];

    /**
     * @var ResourceModel\AttributeMapping\CollectionFactory
     */
    protected $attributeMappingCollectionFactory;

    /**
     * @var ResourceModel\AttributeMapping\Collection
     */
    protected $attributeMappingCollection;

    /**
     * @var GoogleHelper
     */
    protected $googleHelper;

    /**
     * @var array
     */
    protected $attributesCollection = [];

    /**
     * @var SetParentProductOnChildInterface
     */
    private SetParentProductOnChildInterface $setParentProductOnChild;

    /**
     * AttributeMapType constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param GoogleHelper $googleHelper
     * @param ResourceModel\AttributeMapping\CollectionFactory $attributeMappingCollectionFactory
     * @param SetParentProductOnChildInterface $setParentProductOnChild
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        GoogleHelper $googleHelper,
        ResourceModel\AttributeMapping\CollectionFactory $attributeMappingCollectionFactory,
        SetParentProductOnChildInterface $setParentProductOnChild,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->attributeMappingCollectionFactory = $attributeMappingCollectionFactory;
        $this->googleHelper = $googleHelper;
        $this->setParentProductOnChild = $setParentProductOnChild;
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(AttributeMapResourceModel::class);
        $this->setIdFieldName('type_id');
    }

    /**
     * Get Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_getData(self::NAME);
    }

    /**
     * Set target Country
     *
     * @param string $countryCode
     * @return $this
     */
    public function setTargetCountry($countryCode)
    {
        $this->setData(self::TARGET_COUNTRY, $countryCode);
        return $this;
    }

    /**
     * Set Store Id
     *
     * @param   int   $storeId
     * @return  $this
     */
    public function setStoreId($storeId)
    {
        $this->setData(self::STORE_ID, $storeId);
        return $this;
    }

    /**
     * Get Name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->setData(self::NAME, $name);
        return $this;
    }

    /**
     * Get Target Country
     *
     * @return string
     */
    public function getTargetCountry()
    {
        return $this->_getData(self::TARGET_COUNTRY);
    }

    /**
     * Set Attribute Mapping
     *
     * @param array $attributeMap
     * @return $this
     */
    public function setAttributeMap(array $attributeMap = [])
    {
        $this->setData(self::ATTRIBUTE_MAP, $attributeMap);
        return $this;
    }

    /**
     * Get attribute Mapping
     *
     * @return array
     */
    public function getAttributeMap()
    {
        return $this->_getData(self::ATTRIBUTE_MAP);
    }

    /**
     * Get attribute Mapping items
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getAttributeMapping()
    {
        return $this->getAttributesMappingCollection()->getItems();
    }

    /**
     * Create Attribute Collection from collection factory
     *
     * @return Collection
     */
    protected function createAttributeMappingCollection()
    {
        return $this->attributeMappingCollectionFactory->create();
    }

    /**
     * Retrieve Attribute Mapping
     *
     * @return Collection
     */
    public function getAttributeMappingCollection()
    {
        return $this->createAttributeMappingCollection();
    }

    /**
     * Get attribute Collection for attribute type
     *
     * @return Collection
     */
    public function getAttributesMappingCollection()
    {
        if ($this->attributeMappingCollection === null) {
            $this->attributeMappingCollection = $this->getAttributeMappingCollection()
                ->setTypeFilter(
                    $this
                )->addFieldToSelect(
                    '*'
                );
            foreach ($this->attributeMappingCollection as $attributeMapping) {
                $attributeMapping->setType($this);
            }
        }

        return $this->attributeMappingCollection;
    }

    /**
     * Convert attribute to model class object
     *
     * @param ProductsInterface $product
     * @return ProductInput
     * @throws LocalizedException
     */
    public function convertAttributes($product)
    {
        $productObject = $product->getProduct();
        $productObject->setData('current_target_country', $this->getTargetCountry());

        $newShoppingProduct = new ProductInput();
        $googleAttributes   = new ProductAttributes();

        $map        = $this->getAttributesMapByProduct($productObject);
        $base       = $this->getBaseAttributes();
        $attributes = array_merge($base, $map);

        if (Visibility::VISIBILITY_NOT_VISIBLE == $productObject->getVisibility()
            && !$productObject->getData('item_parent_product')
        ) {
            $this->setParentProductOnChild->execute($productObject);
        }

        foreach ($attributes as $attribute) {
            try {
                $attribute->convertAttribute($productObject, $newShoppingProduct, $googleAttributes);
            } catch (LocalizedException $exception) {
                $product->setStatus(ProductsInterface::ERROR_STATUS);
                throw $exception;
            } catch (\Exception $exception) {
                if (!stristr($exception->getMessage(), 'Indirect modification of overloaded property')) {
                    throw $exception;
                }
            }
        }

        $this->checkForIdentifierExist($googleAttributes);
        $this->checkForValidProduct($googleAttributes);

        // ── Required fields on ProductInput (Merchant API v1) ──────────────────
        // Content API: these were set inside the product body automatically.
        // Merchant API v1: must be set explicitly as top-level fields on ProductInput.
        // ─────────────────────────────────────────────────────────────────────────

        $storeId         = $productObject->getStoreId();
        $targetCountry   = $this->getTargetCountry();
        $contentLanguage = $this->googleHelper->getConfig()
            ->getDefaultContentLanguage($storeId) ?: 'en';

        // offerId — your unique product identifier (SKU)
        $newShoppingProduct->setOfferId((string) $productObject->getSku());

        // contentLanguage — ISO 639-1 two-letter language code e.g. 'en'
        $newShoppingProduct->setContentLanguage(strtolower($contentLanguage));

        // feedLabel — target country code e.g. 'US'
        // Replaces the old targetCountry / feedLabel concept from Content API
        $newShoppingProduct->setFeedLabel(strtoupper($targetCountry));

        // ❌ REMOVED: setChannel() does NOT exist in Merchant API v1 PHP client.
        // Channel (ONLINE/LOCAL) is set automatically by the data source type
        // configured in Google Merchant Center — no code needed here.

        // Attach all product attributes to the ProductInput
        $newShoppingProduct->setProductAttributes($googleAttributes);

        return $newShoppingProduct;
    }

    /**
     * Check for specific value is set or not.
     * For some Google categories (e.g. 166) age group and gender are required.
     * If those values are not set then apply defaults.
     *
     * Merchant API v1: setAgeGroup() and setGender() require enum integers,
     * NOT plain strings as in the Content API.
     *
     * @param ProductAttributes $googleAttributes
     */
    protected function checkForValidProduct(ProductAttributes $googleAttributes): void
    {
        // --- Age Group ---
        // getAgeGroup() returns an int (enum). 0 == AGE_GROUP_UNSPECIFIED (not set).
        if (!$googleAttributes->getAgeGroup()
            || $googleAttributes->getAgeGroup() === MerchantAgeGroup::AGE_GROUP_UNSPECIFIED
        ) {
            $defaultAgeGroup = strtolower(trim(AgeGroup::AGE_GROUP_DEFAULT_FOR_GOOGLE));
            $googleAttributes->setAgeGroup(
                self::AGE_GROUP_MAP[$defaultAgeGroup] ?? MerchantAgeGroup::ADULT
            );
        }

        // --- Gender ---
        // getGender() returns an int (enum). 0 == GENDER_UNSPECIFIED (not set).
        if (!$googleAttributes->getGender()
            || $googleAttributes->getGender() === MerchantGender::GENDER_UNSPECIFIED
        ) {
            $defaultGender = strtolower(trim(Gender::GENDER_DEFAULT_FOR_GOOGLE));
            $googleAttributes->setGender(
                self::GENDER_MAP[$defaultGender] ?? MerchantGender::MALE
            );
        }
    }

    /**
     * If no unique product identifiers are present, set identifierExists = false.
     *
     * Merchant API v1: getGtin() is now getGtins() (repeated field).
     *
     * @param ProductAttributes $googleAttributes
     */
    protected function checkForIdentifierExist(ProductAttributes $googleAttributes): void
    {
        // getGtins() returns a RepeatedField – convert to array and filter empty values
        $gtins    = iterator_to_array($googleAttributes->getGtins());
        $hasGtin  = !empty(array_filter($gtins));

        $brand    = $googleAttributes->getBrand();
        $hasBrand = !empty($brand) && $brand !== 'Unbranded';

        $hasMpn   = !empty($googleAttributes->getMpn());

        if (!$hasGtin && !$hasBrand && !$hasMpn) {
            $googleAttributes->setIdentifierExists(false);
        }
    }

    /**
     * Get map by product
     *
     * @param ProductInterface $product
     * @return array
     */
    protected function getAttributesMapByProduct(ProductInterface $product)
    {
        $result = [];
        $group  = $this->googleHelper->getAttributeGroupsFlat();

        foreach ($this->getAttributesCollection() as $attribute) {
            $productAttribute = $this->googleHelper
                ->getProductAttribute($product, $attribute->getAttributeId());

            if ($productAttribute) {
                if ($attribute->getGoogleAttribute()) {
                    $name = $attribute->getGoogleAttribute();
                } else {
                    $name = $this->googleHelper->getAttributeLabel($productAttribute, $product->getStoreId());
                }

                if ($name) {
                    $name = $this->googleHelper->normalizeName($name);
                    if (isset($group[$name])) {
                        if (!isset($result[$group[$name]])) {
                            $result[$group[$name]] = $this->createAttribute($group[$name]);
                        }

                        $result[$group[$name]]->addData(
                            [
                                'group_attribute_' . $name => $this->createAttribute($name)->addData(
                                    $attribute->getData()
                                )
                            ]
                        );
                        unset($group[$name]);
                    } else {
                        if (!isset($result[$name])) {
                            $result[$name] = $this->createAttribute($name);
                        }

                        $result[$name]->addData($attribute->getData());
                    }
                }
            }
        }

        return $this->initGroupAttributes($result);
    }

    /**
     * Return array with base attributes
     *
     * @return array
     */
    protected function getBaseAttributes()
    {
        $names      = $this->googleHelper->getBaseAttributes();
        $attributes = [];
        foreach ($names as $name) {
            $attributes[$name] = $this->createAttribute($name);
        }

        return $this->initGroupAttributes($attributes);
    }

    /**
     * Append to attributes array sub attribute's models
     *
     * @param array $attributes
     * @return array
     */
    protected function initGroupAttributes($attributes)
    {
        $group = $this->googleHelper->getAttributeGroupsFlat();
        foreach ($group as $child => $parent) {
            if (isset($attributes[$parent]) && !isset($attributes[$parent]['group_attribute_' . $child])) {
                $attributes[$parent]->addData(
                    ['group_attribute_' . $child => $this->createAttribute($child)]
                );
            }
        }

        return $attributes;
    }

    /**
     * Prepare Google Content attribute model name
     *
     * @param string $string Attribute name
     * @return string Normalized attribute name
     */
    protected function prepareModelName($string)
    {
        $string = $this->googleHelper->normalizeName($string);
        return '\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    /**
     * Create attribute instance using attribute's name
     *
     * @param string $name
     * @return Attributes\Base
     */
    protected function createAttribute($name)
    {
        $modelNamePrefix = \Egits\GoogleMerchantApi\Model\Attributes::class;
        $modelName       = $modelNamePrefix . $this->prepareModelName($name);
        $useDefault      = false;
        $attributeModel  = null;
        $objectManager   = ObjectManager::getInstance();

        try {
            $attributeModel = $objectManager->create($modelName);
            $useDefault     = !$attributeModel;
        } catch (\Exception $e) {
            $useDefault = true;
        }

        if ($useDefault) {
            $attributeModel = $objectManager->create($modelNamePrefix . $this->prepareModelName('base'));
        }

        $attributeModel->setName($name);

        return $attributeModel;
    }

    /**
     * Retrieve type's attributes mapping collection
     *
     * It is protected, because only Type knows about its attributes
     *
     * @return array|AttributeMappingInterface[]
     */
    protected function getAttributesCollection()
    {
        if (!$this->attributesCollection && $this->getId()) {
            $this->attributesCollection = $this->getAttributeMapping();
        }

        return $this->attributesCollection;
    }
}
