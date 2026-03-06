<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Ui\Component\Listing\Column\ProductStatus;

use Egits\GoogleMerchantApi\Model\Product;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ProductStatusOptions
 * Product status in queue option provider
 */
class ProductStatusOptions implements OptionSourceInterface
{
    /**
     * @var Product
     */
    private Product $product;

    /**
     * ProductStatusOptions constructor.
     * @param Product $product
     */
    public function __construct(
        Product $product
    ) {
        $this->product = $product;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->product->getProductStatusArray() as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return $options;
    }
}
