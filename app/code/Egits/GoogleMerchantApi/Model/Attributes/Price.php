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
    public function convertAttribute($product, $shoppingProduct)
    {
        $product->setWebsiteId($product->getStore()->getWebsiteId());
        $store = $product->getStore();
        // need to store current target country in registry and get it;
        //$targetCountry = getTargetCountry($product->getStoreId());
        $isSalePriceAllowed = true;//($targetCountry == 'US');

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

        $shoppingPrice = new \Google_Service_ShoppingContent_Price();
        $shoppingPrice->setCurrency($store->getBaseCurrencyCode());
        if ($isSalePriceAllowed) {
            // set sale_price and effective dates for it
            if ($price && ($price - $finalPrice) > .0001) {
                $salesPrice = new \Google_Service_ShoppingContent_Price();
                $salesPrice->setCurrency($store->getBaseCurrencyCode());
                $shoppingPrice->setValue(sprintf('%.2f', $price));
                $salesPrice->setValue($finalPrice);
                $shoppingProduct->setSalePrice($salesPrice);

                $effectiveDate = $this->getGroupAttributeSalePriceEffectiveDate();
                if ($effectiveDate) {
                    $effectiveDate->setGroupAttributeSalePriceEffectiveDateFrom(
                        $this->getGroupAttributeSalePriceEffectiveDateFrom()
                    )->setGroupAttributeSalePriceEffectiveDateTo(
                        $this->getGroupAttributeSalePriceEffectiveDateTo()
                    )->convertAttribute($product, $shoppingProduct);
                }
            } else {
                $shoppingPrice->setValue(sprintf('%.2f', $finalPrice));
            }

            // calculate taxes
            $tax = $this->getGroupAttributeTax();
            if (!$inclTax && $tax) {
                $tax->convertAttribute($product, $shoppingProduct);
            }
        } else {
            $shoppingPrice->setValue(sprintf('%.2f', $price));
        }

        $shoppingProduct->setPrice($shoppingPrice);

        return $shoppingProduct;
    }
}
