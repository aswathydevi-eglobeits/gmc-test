<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model;

use Egits\GoogleMerchantApi\Api\GetParentIdsByProductInterface;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObject;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class GetParentIdsByProduct extends DataObject implements GetParentIdsByProductInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Configurable
     */
    private $configurableType;

    /**
     * @var BundleType
     */
    private $bundleType;

    /**
     * @var Grouped
     */
    private $groupedType;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Configurable $configurableType
     * @param BundleType $bundleType
     * @param Grouped $groupedType
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Configurable      $configurableType,
        BundleType        $bundleType,
        Grouped           $groupedType,
        array             $data = []
    ) {
        parent::__construct($data);
        $this->collectionFactory = $collectionFactory;
        $this->configurableType = $configurableType;
        $this->bundleType = $bundleType;
        $this->groupedType = $groupedType;
    }

    /**
     * @inheritDoc
     */
    public function execute(ProductInterface $product): array
    {
        if (in_array($product->getTypeId(), [Configurable::TYPE_CODE, BundleType::TYPE_CODE, Grouped::TYPE_CODE])) {
            return [];
        }

        $productId = $product->getId();
        $configurableParents = $this->configurableType->getParentIdsByChild($productId);
        $bundleParents = $this->bundleType->getParentIdsByChild($productId);
        $groupedParents = $this->groupedType->getParentIdsByChild($productId);

        return array_merge($configurableParents, $bundleParents, $groupedParents);
    }
}
