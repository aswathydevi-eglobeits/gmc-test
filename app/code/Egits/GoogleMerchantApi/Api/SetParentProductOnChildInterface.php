<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Api;

use Magento\Catalog\Api\Data\ProductInterface;

interface SetParentProductOnChildInterface
{
    /**
     * SetParentProductOnChildInterface
     *
     * @param ProductInterface $product
     * @return ProductInterface|null
     */
    public function execute(ProductInterface $product): ?ProductInterface;
}
