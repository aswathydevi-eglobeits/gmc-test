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

use Egits\GoogleMerchantApi\Helper\GoogleHelper;
use Google\Shopping\Merchant\Products\V1\ProductAttributes;
use Google\Shopping\Type\Price as GooglePrice;
use Magento\Catalog\Model\ProductRepository;
use Magento\Tax\Helper\Data as TaxData;
use Magento\Tax\Model\Config;
use Magento\Catalog\Model\Product\CatalogPrice;

/**
 * Class Price
 * Google merchant api price attribute
 */
class Price extends Base
{
    /**
     * @var TaxData
     */
    protected $taxData;

    /**
     * @var CatalogPrice
     */
    private $catalogPrice;

    /**
     * Price constructor.
     *
     * @param GoogleHelper $googleHelper
     * @param ProductRepository $productRepository
     * @param TaxData $taxData
     * @param CatalogPrice $catalogPrice
     */
    public function __construct(
        GoogleHelper $googleHelper,
        ProductRepository $productRepository,
        TaxData $taxData,
        CatalogPrice $catalogPrice
    ) {
        parent::__construct($googleHelper, $productRepository);
        $this->taxData = $taxData;
        $this->catalogPrice = $catalogPrice;
    }

    /**
     * @inheritdoc
     */
    public function convertAttribute($product, $shoppingProduct, $googleAttributes)
    {
        $product->setWebsiteId($product->getStore()->getWebsiteId());
        $store = $product->getStore();
        $isSalePriceAllowed = true;

        // get tax settings
        $priceDisplayType = $this->taxData->getPriceDisplayType($product->getStoreId());
        $inclTax = (
            $priceDisplayType == Config::DISPLAY_TYPE_INCLUDING_TAX
            || $priceDisplayType == Config::DISPLAY_TYPE_BOTH
        );

        // calculate sale_price attribute value
        $salePriceAttribute = $this->getGroupAttributeSalePrice();
        $salePriceMapValue = null;
        $finalPrice = null;
        if ($salePriceAttribute !== null) {
            $salePriceMapValue = $salePriceAttribute->getProductAttributeValue($product);
        }

        if ($salePriceMapValue !== null && floatval($salePriceMapValue) > .0001) {
            $finalPrice = $salePriceMapValue;
        } else {
            if ($isSalePriceAllowed) {
                $finalPrice = $this->catalogPrice->getCatalogPrice($product, $store, $inclTax);
            }
        }

        // calculate price attribute value
        $priceMapValue = $this->getProductAttributeValue($product);
        $price = null;
        if ($priceMapValue && floatval($priceMapValue) > .0001) {
            $price = $priceMapValue;
        } else {
            if ($isSalePriceAllowed) {
                $price = $this->catalogPrice->getCatalogRegularPrice($product);
            } else {
                $inclTax = ($priceDisplayType != Config::DISPLAY_TYPE_EXCLUDING_TAX);
                $price = (int)$this->catalogPrice->getCatalogPrice($product, $store, $inclTax);
            }
        }

        $currencyCode = $store->getBaseCurrencyCode();

        $shoppingPrice = new GooglePrice();
        $shoppingPrice->setCurrencyCode($currencyCode);

        if ($isSalePriceAllowed) {
            // set sale_price and effective dates for it
            if ($price && ($price - $finalPrice) > .0001) {
                $salesPrice = new GooglePrice();
                $salesPrice->setCurrencyCode($currencyCode);
                $salesPrice->setAmountMicros((int)(round((float)$finalPrice, 2) * 1000000));
                $shoppingPrice->setAmountMicros((int)(round((float)$price, 2) * 1000000));
                $googleAttributes->setSalePrice($salesPrice);

                $effectiveDate = $this->getGroupAttributeSalePriceEffectiveDate();
                if ($effectiveDate) {
                    $effectiveDate->setGroupAttributeSalePriceEffectiveDateFrom(
                        $this->getGroupAttributeSalePriceEffectiveDateFrom()
                    )->setGroupAttributeSalePriceEffectiveDateTo(
                        $this->getGroupAttributeSalePriceEffectiveDateTo()
                    )->convertAttribute($product, $shoppingProduct, $googleAttributes);
                }
            } else {
                $shoppingPrice->setAmountMicros((int)(round((float)$price, 2) * 1000000));
            }

            // calculate taxes
            $tax = $this->getGroupAttributeTax();
            if (!$inclTax && $tax) {
                $tax->convertAttribute($product, $shoppingProduct, $googleAttributes);
            }
        } else {
            $shoppingPrice->setAmountMicros((int)(round((float)$price, 2) * 1000000));
        }

        $googleAttributes->setPrice($shoppingPrice);
        $shoppingProduct->setProductAttributes($googleAttributes);

        return $shoppingProduct;
    }
}
