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
 * Class Content
 * Google merchant api product description attribute
 */
class Content extends Base
{
    /**
     * @inheritdoc
     */
    public function convertAttribute($product, $shoppingProduct)
    {
        $mapValue = $this->getProductAttributeValue($product);
        $description = $this->getGroupAttributeDescription();
        if ($description) {
            $mapValue = $description->getProductAttributeValue($product);
        }

        $parent = $product->getData('item_parent_product');
        if ($product->getVisibility() == Visibility::VISIBILITY_NOT_VISIBLE) {
            if (!$parent) {
                $message = sprintf('Product %s :visibility issue', $product->getName());
                throw new LocalizedException(__($message));
            }

            $mapValue = $this->getProductAttributeValue($parent);
            $description = $this->getGroupAttributeDescription();
            if ($description) {
                $mapValue = $description->getProductAttributeValue($parent);
            }
        }

        if ($mapValue) {
            $descriptionText = $mapValue;
        } elseif ($product->getDescription() && $product->getVisibility() != Visibility::VISIBILITY_NOT_VISIBLE) {
            $descriptionText = $product->getDescription();
        } elseif ($parent) {
            $descriptionText = $parent->getDescription();
        } else {
            $descriptionText = 'no description';
        }

        $descriptionText = $this->googleHelper->cleanAtomAttribute($descriptionText);
        $descriptionText = $this->googleHelper->getEscaper()->escapeHtml($descriptionText);
        $shoppingProduct->setDescription($descriptionText);

        return $shoppingProduct;
    }
}
