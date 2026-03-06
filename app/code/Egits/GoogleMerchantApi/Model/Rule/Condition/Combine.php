<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model\Rule\Condition;

use Egits\GoogleMerchantApi\Model\Rule\Condition\Combine as ConditionCombine;
use Magento\Rule\Model\Condition\Context;

/**
 * Class Combine
 * Custom rule combine class
 */
class Combine extends \Magento\CatalogRule\Model\Rule\Condition\Combine
{
    /**
     * Combine constructor.
     *
     * @param Context $context
     * @param ProductFactory $conditionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductFactory $conditionFactory,
        array $data = []
    ) {
        parent::__construct($context, $conditionFactory, $data);
        $this->setType(ConditionCombine::class);
    }

    /**
     * Get New Child select option
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->_productFactory->create()->loadAttributeOptions()->getAttributeOption();
        $productAttributes['type_id'] = __('Type');
        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Egits\GoogleMerchantApi\Model\Rule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }

        $conditions = [['value' => '', 'label' => __('Please choose a condition to add.')]];
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => ConditionCombine::class,
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('Product Attribute'), 'value' => $attributes]
            ]
        );

        return $conditions;
    }
}
