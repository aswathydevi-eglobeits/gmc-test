<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Ui\Component\Listing\Column\Attribute;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 *
 * Product attribute options provider
 */
class Options implements OptionSourceInterface
{

    /**
     * Default ignored attribute codes
     *
     * @var array
     */
    protected $ignoredAttributeCodes
        = [
            'admin_product_note',
            'custom_design',
            'custom_design_from',
            'custom_design_to',
            'custom_layout',
            'custom_layout_update',
            'gift_message_available',
            'giftcard_amounts',
            'news_from_date',
            'news_to_date',
            'options_container',
            'price_view',
            'sku_type',
            'use_config_is_redeemable',
            'use_config_allow_message',
            'use_config_lifetime',
            'use_config_email_template',
            'tier_price',
            'minimal_price',
            'page_layout',
            'recurring_profile',
            'required_options',
            'shipment_type'
        ];

    /**
     * @var null|array
     */
    protected $options;

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $productAttributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Product $product
     * @param FilterBuilder $filterBuilder
     * @internal param CollectionFactory $collectionFactory
     * @internal param Product $product
     */
    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Product $product,
        FilterBuilder $filterBuilder
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->product = $product;

        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {

        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('entity_type_code')
                    ->setValue(ProductAttributeInterface::ENTITY_TYPE_CODE)
                    ->setConditionType('eq')
                    ->create(),
            ]
        );
        $searchResult = $this->productAttributeRepository->getList($this->searchCriteriaBuilder->create());

        $this->options[] = [
            'label'          => 'Select Attribute',
            'value'          => '',
            'entity_type_id' => ''
        ];

        foreach ($searchResult->getItems() as $productAttribute) {
            if (!in_array($productAttribute->getAttributeCode(), $this->ignoredAttributeCodes)
                && $productAttribute->getFrontendLabel() != null
            ) {
                $this->options[] = [
                    'label'          => $productAttribute->getFrontendLabel(),
                    'value'          => $productAttribute->getAttributeId(),
                    'entity_type_id' => $productAttribute->getEntityTypeId()
                ];
            }
        }

        return $this->options;
    }
}
