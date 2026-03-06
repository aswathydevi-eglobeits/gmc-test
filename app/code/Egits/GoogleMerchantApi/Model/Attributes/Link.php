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

use Google_Service_ShoppingContent_Product;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Link
 * Google merchant api product url attribute
 */
class Link extends Base
{
    /**
     * Convert Attribute
     *
     * @param ProductInterface|Product $product
     * @param Google_Service_ShoppingContent_Product $shoppingProduct
     * @return Google_Service_ShoppingContent_Product
     * @throws LocalizedException
     */
    public function convertAttribute($product, $shoppingProduct)
    {
        $url = $product->getProductUrl();
        if ($product->getVisibility() == Visibility::VISIBILITY_NOT_VISIBLE) {
            $parentProduct = $product->getData('item_parent_product');
            if (!$parentProduct) {
                $message = sprintf('Product %s : visibility issue', $product->getName());
                throw new LocalizedException(__($message));
            }

            $url = $parentProduct->getProductUrl();
        }

        $storeId = $product->getStoreId();
        if ($url) {
            $config = $this->googleHelper->getConfig();
            if (!$config->getUseStoreUrlDefault($storeId)
                && $config->getAddStoreCodeToUrl($storeId)
            ) {
                $urlInfo = \Laminas\Uri\UriFactory::factory($url);
                $store = $product->getStore()->getCode();
                $query = $urlInfo->getQuery();

                if (!empty($query)) {
                    $url .= '&___store=' . $store;
                } else {
                    $url .= '?___store=' . $store;
                }
            }

            if ($config->getAddUtmSourceGoogleShopping()) {
                $url .= strpos($url, '?') === false ? '?' : '&';
                $url .= 'utm_source=GoogleShopping';
            }
            $url = $this->getPwaUrl($product->getStore()->getBaseUrl(), $url);
            $shoppingProduct->setLink($url);
        }

        return $shoppingProduct;
    }
}
