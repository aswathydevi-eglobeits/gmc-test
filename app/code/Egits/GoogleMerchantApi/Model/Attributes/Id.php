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

use Google\Shopping\Merchant\Products\V1\ProductInput;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Id
 * Google merchant api id attribute
 */
class Id extends Base
{
    /**
     * @inheritdoc
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product $product
     * @param ProductInput $shoppingProduct
     * @return ProductInput
     * @throws LocalizedException
     */
    public function convertAttribute($product, $shoppingProduct)
    {
        if (strlen($product->getSku()) > 30) {
            throw new LocalizedException(__('Product sku length exceeded 25 characters'));
        }

        $value = $this->googleHelper->buildContentProductId($product->getSku(), $product->getStoreId());
        if ($product->getData('item_parent_product')) {
            $parentProduct = $product->getData('item_parent_product');
            if (strlen($parentProduct->getSku()) > 30) {
                throw new LocalizedException(__('Parent product sku length exceeded 25 characters'));
            }

            //set configurable parent as item_group_id
            $value = $this->googleHelper->buildContentProductId(
                'P-' . $parentProduct->getId() . '-' . $product->getSku(),
                $product->getStoreId()
            );
            $shoppingProduct->setItemGroupId(
                $this->googleHelper->buildContentProductId($parentProduct->getSku(), $product->getStoreId())
            );
        }

        $shoppingProduct->setOfferId($value);
        return $shoppingProduct;
    }
}
