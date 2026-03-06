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

use Egits\GoogleMerchantApi\Api\SetParentProductOnChildInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;

class SetParentProductOnChild extends DataObject implements SetParentProductOnChildInterface
{

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var GetParentIdsByProduct
     */
    private GetParentIdsByProduct $parentIdsByProduct;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param GetParentIdsByProduct $parentIdsByProduct
     * @param array $data
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        GetParentIdsByProduct $parentIdsByProduct,
        array $data = []
    ) {
        parent::__construct($data);
        $this->productRepository = $productRepository;
        $this->parentIdsByProduct = $parentIdsByProduct;
    }

    /**
     * @inheritDoc
     *
     * @throws NoSuchEntityException
     */
    public function execute(ProductInterface $product): ? ProductInterface
    {
        $parentIds = $this->parentIdsByProduct->execute($product);
        /**
         * Take First Parent Item & set as child's parent
         */
        if ($parentIds) {
            $parentId = $parentIds[0];
            $parentProduct = $this->productRepository->getById($parentId);
            $product->setData('item_parent_product', $parentProduct);
        }

        return $product;
    }
}
