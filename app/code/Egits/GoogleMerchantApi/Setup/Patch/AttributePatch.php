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

namespace Egits\GoogleMerchantApi\Setup\Patch;

use Egits\GoogleMerchantApi\Model\Config\Source\AgeGroup;
use Egits\GoogleMerchantApi\Model\Config\Source\Gender;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class AttributePatch implements
    DataPatchInterface,
    PatchRevertableInterface
{
    public const AGE_GROUP_ATTRIBUTE_CODE = 'age_group';
    public const GENDER_ATTRIBUTE_CODE = 'gender';

    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    /**
     * @var Config
     */
    private Config $eavConfig;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param Config $eavConfig
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $attributeAge = $this->getProductAttribute(self::AGE_GROUP_ATTRIBUTE_CODE);
        if (!$attributeAge || !$attributeAge->getAttributeId()) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                self::AGE_GROUP_ATTRIBUTE_CODE,
                [
                    'group'                   => 'Product Details',
                    'type'                    => 'text',
                    'backend'                 => '',
                    'frontend'                => '',
                    'label'                   => 'Age Group',
                    'input'                   => 'select',
                    'class'                   => '',
                    'source'                  => AgeGroup::class,
                    'global'                  => ScopedAttributeInterface::SCOPE_WEBSITE,
                    'visible'                 => true,
                    'required'                => false,
                    'user_defined'            => false,
                    'default'                 => '',
                    'searchable'              => true,
                    'filterable'              => false,
                    'comparable'              => false,
                    'visible_on_front'        => false,
                    'used_in_product_listing' => true,
                    'unique'                  => false,
                    'apply_to'                => 'simple,grouped,configurable,downloadable,virtual,bundle',
                    'is_used_in_grid'         => false,
                    'is_visible_in_grid'      => false,
                    'is_filterable_in_grid'   => false,
                ]
            );
        }

        $attributeGender = $this->getProductAttribute(self::GENDER_ATTRIBUTE_CODE);
        if (!$attributeGender || !$attributeGender->getAttributeId()) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                self::GENDER_ATTRIBUTE_CODE,
                [
                    'group'                   => 'Product Details',
                    'type'                    => 'text',
                    'backend'                 => '',
                    'frontend'                => '',
                    'label'                   => 'Gender',
                    'input'                   => 'select',
                    'class'                   => '',
                    'source'                  => Gender::class,
                    'global'                  => ScopedAttributeInterface::SCOPE_WEBSITE,
                    'visible'                 => true,
                    'required'                => false,
                    'user_defined'            => false,
                    'default'                 => '',
                    'searchable'              => false,
                    'filterable'              => false,
                    'comparable'              => false,
                    'visible_on_front'        => false,
                    'used_in_product_listing' => true,
                    'unique'                  => false,
                    'apply_to'                => 'simple,grouped,configurable,downloadable,virtual,bundle',
                    'is_used_in_grid'         => false,
                    'is_visible_in_grid'      => false,
                    'is_filterable_in_grid'   => false,
                ]
            );
        }
    }

    /**
     * Get Product Attribute.
     *
     * @param string $attributeCode
     * @return AbstractAttribute|null
     * @throws LocalizedException
     */
    private function getProductAttribute(string $attributeCode): ?AbstractAttribute
    {
        return $this->eavConfig->getAttribute(Product::ENTITY, $attributeCode);
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function revert()
    {
        return void;
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
