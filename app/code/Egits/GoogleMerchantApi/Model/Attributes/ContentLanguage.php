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

/**
 * Class ContentLanguage
 * Google merchant api language attribute
 */
class ContentLanguage extends Base
{
    /**
     * Default language if not set from config
     */
    public const DEFAULT_LANGUAGE = 'en';

    /**
     * @inheritdoc
     * @param \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product $product
     * @param \Google_Service_ShoppingContent_Product $shoppingProduct
     * @return \Google_Service_ShoppingContent_Product
     */
    public function convertAttribute($product, $shoppingProduct)
    {
        $value = $this->googleHelper->getConfig()->getDefaultContentLanguage($product->getStoreId());
        if (!$value) {
            $value = self::DEFAULT_LANGUAGE;
        }

        $shoppingProduct->setContentLanguage($value);
        return $shoppingProduct;
    }
}
