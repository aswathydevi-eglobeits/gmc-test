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

use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Title
 * Google merchant api product name attribute
 */
class Title extends Base
{
    /**
     * @inheritdoc
     */
    public function convertAttribute($product, $shoppingProduct,$googleAttributes)
    {
        $mapValue = $this->getProductAttributeValue($product);
        $name = $this->getGroupAttributeName();
        if ($name) {
            $mapValue = $name->getProductAttributeValue($product);
        }

        $parent = $product->getData('item_parent_product');
        if ($product->getVisibility() == Visibility::VISIBILITY_NOT_VISIBLE) {
            if (!$parent) {
                $message = sprintf('Product %s :visibility issue', $product->getName());
                throw new LocalizedException(__($message));
            }

            $mapValue = $this->getProductAttributeValue($parent);
            $name = $this->getGroupAttributeName();
            if ($name) {
                $mapValue = $name->getProductAttributeValue($parent);
            }
        }

        if ($mapValue) {
            $titleText = $mapValue;
        } elseif ($product->getName() && ($product->getVisibility() != Visibility::VISIBILITY_NOT_VISIBLE)) {
            $titleText = $product->getName();
        } elseif ($parent) {
            $titleText = $parent->getName();
        } else {
            $titleText = 'no title';
        }

        if (mb_strlen($titleText, 'utf8') > 150) {
            $message = sprintf('Product %s :Title length exceed more than 150', $product->getName());
            throw new LocalizedException(__($message));
        }

        $titleText = ucwords(strtolower($this->googleHelper->cleanAtomAttribute($titleText)));
        $googleAttributes->setTitle($titleText);

        return $shoppingProduct;
    }
}
