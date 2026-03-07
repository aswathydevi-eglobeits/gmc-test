<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Api\Data;

/**
 * Interface AttributeInterface
 * Interface for google attributes
 */
interface AttributeInterface
{
    /**
     * Convert Mapped Attribute and default attributes.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product $product
     * @param \Google\Shopping\Merchant\Products\V1\ProductInput $shoppingProduct
     * @return \Google\Shopping\Merchant\Products\V1\ProductInput
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function convertAttribute($product, $shoppingProduct, $googleAttributes);
}
