<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Ui\Component\Listing\Column\GoogleAttribute;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Escaper;

/**
 * Class Options
 * Google attribute option provider
 */
class Options implements OptionSourceInterface
{
    /**
     * Google Attribute list
     *
     * @var array
     */
    private $googleAttributes
        = [
            'Item'           => [
                ['label' => 'Title', 'code' => 'title', 'required' => true],
                ['label' => 'Description', 'code' => 'description', 'required' => true],
                ['label' => 'Additional Image URL', 'code' => 'additional_image_link', 'required' => false],
                ['label' => 'Mobile URL', 'code' => 'mobile_link', 'required' => false],
                ['label' => 'Availability', 'code' => 'availability', 'required' => false],
                ['label' => 'Condition', 'code' => 'condition', 'required' => false],
                ['label' => 'Availability Date', 'code' => 'availability_date', 'required' => false],
                ['label' => 'Cost Of Goods Sold', 'code' => 'cost_of_goods_sold', 'required' => false],
                ['label' => 'Expiration date', 'code' => 'expiration_date', 'required' => false],
                ['label' => 'Adult', 'code' => 'adult', 'required' => false],
                ['label' => 'Is Bundle', 'code' => 'is_bundle', 'required' => true],
                ['label' => 'Shipping Length', 'code' => 'shipping_length', 'required' => false],
                ['label' => 'Shipping Width', 'code' => 'shipping_width', 'required' => true],
                ['label' => 'Shipping Height', 'code' => 'shipping_height', 'required' => true],
                ['label' => 'Tax', 'code' => 'tax', 'required' => true],

            ],
            'Product Search' => [
                ['label' => 'Price', 'code' => 'price', 'required' => true],
                ['label' => 'Sale Price', 'code' => 'sale_price', 'required' => false],
                [
                    'label'    => 'Sale Price Effective From Date',
                    'code'     => 'sale_price_effective_date_from',
                    'required' => false
                ],
                [
                    'label'    => 'Sale Price Effective To Date',
                    'code'     => 'sale_price_effective_date_to',
                    'required' => false
                ],
                ['label' => 'Age Group', 'code' => 'age_group', 'required' => true],
                ['label' => 'Brand', 'code' => 'brand', 'required' => true],
                ['label' => 'Color', 'code' => 'color', 'required' => false],
                ['label' => 'Gender', 'code' => 'gender', 'required' => false],
                ['label' => 'Manufacturer Part Number (MPN)', 'code' => 'mpn', 'required' => true],
                ['label' => 'Online Only', 'code' => 'online_only', 'required' => false],
                ['label' => 'GTIN', 'code' => 'gtin', 'required' => true],
                ['label' => 'Product Review Average', 'code' => 'product_review_average', 'required' => false],
                ['label' => 'Product Review Count', 'code' => 'product_review_count', 'required' => false],
                ['label' => 'Material', 'code' => 'material', 'required' => false],
                ['label' => 'Pattern/Graphic', 'code' => 'pattern', 'required' => false],
                ['label' => 'Shipping Weight', 'code' => 'shipping_weight', 'required' => false],
                ['label' => 'Size', 'code' => 'size', 'required' => false],
                ['label' => 'Energy Efficiency Class', 'code' => 'energy_efficiency_class', 'required' => false],
                ['label' => 'Identifier Exist', 'code' => 'identifier_exists', 'required' => false],
            ],
            'CustomLabels'   => [
                ['label' => 'Custom label 0', 'code' => 'custom_label_0', 'required' => false],
                ['label' => 'Custom label 1', 'code' => 'custom_label_1', 'required' => false],
                ['label' => 'Custom label 2', 'code' => 'custom_label_2', 'required' => false],
                ['label' => 'Custom label 3', 'code' => 'custom_label_3', 'required' => false],
                ['label' => 'Custom label 4', 'code' => 'custom_label_4', 'required' => false],
            ],
            'Shipping'       => [
                ['label' => 'Shipping Label', 'code' => 'shipping_label', 'required' => false],
            ]
        ];

    /**
     * Option array
     *
     * @var array
     */
    protected $options;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * Current option
     *
     * @var array
     */
    protected $currentOptions = [];

    /**
     * Constructor
     *
     * @param Escaper $escaper
     */
    public function __construct(Escaper $escaper)
    {
        $this->escaper = $escaper;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $this->generateCurrentOptions();
        $this->options = array_values($this->currentOptions);
        return $this->options;
    }

    /**
     * Get Google Attribute Generated options value
     */
    protected function generateCurrentOptions()
    {
        $groups = [];
        foreach ($this->googleAttributes as $key => $attributeGroups) {
            $attributes = [];
            foreach ($attributeGroups as $groupData) {
                $name = $this->escaper->escapeHtml($groupData['label']);
                $attributes[$groupData['code']]['label'] = str_repeat(' ', 8) . $name;
                $attributes[$groupData['code']]['value'] = $groupData['code'];
            }

            if (!empty($attributes)) {
                $name = $this->escaper->escapeHtml($key);
                $groups[$key]['label'] = str_repeat(' ', 4) . $name;
                $groups[$key]['value'] = array_values($attributes);
            }
        }

        if (!empty($groups)) {
            $this->currentOptions = 'items';
            $this->currentOptions = array_values($groups);
        }
    }
}
