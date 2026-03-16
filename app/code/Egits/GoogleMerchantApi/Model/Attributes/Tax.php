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
use Magento\Tax\Model\Calculation;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Parse\Zip;
use Magento\Tax\Helper\Data as TaxData;

/**
 * Class Tax
 * Google merchant api tax attribute
 */
class Tax extends Base
{
    /**
     * Maximum number of tax rates per product supported by google shopping api
     */
    public const RATES_MAX = 100;

    /**
     * @var TaxData
     */
    protected $taxData;

    /**
     * @var Calculation
     */
    private $calculation;

    /**
     * Tax constructor.
     *
     * @param GoogleHelper $googleHelper
     * @param ProductRepository $productRepository
     * @param TaxData $taxData
     * @param Calculation $calculation
     */
    public function __construct(
        GoogleHelper $googleHelper,
        ProductRepository $productRepository,
        TaxData $taxData,
        Calculation $calculation
    ) {
        parent::__construct($googleHelper, $productRepository);
        $this->taxData = $taxData;
        $this->calculation = $calculation;
    }

    /**
     * @inheritdoc
     */
    public function convertAttribute($product, $shoppingProduct, $googleAttributes = null)
    {

        if ($this->taxData->getConfig()->priceIncludesTax()) {
            return $shoppingProduct;
        }

        return $shoppingProduct;
    }

    /**
     * Retrieve array of regions characterized by provided params
     *
     * @param string $state
     * @param string $zip
     * @return array
     */
    protected function parseRegions($state, $zip)
    {
        return (!empty($zip) && $zip != '*') ? $this->parseZip($zip) : (($state) ? [$state] : ['*']);
    }

    /**
     * Retrieve array of regions characterized by provided zip code
     *
     * @param string $zip
     * @return array
     */
    protected function parseZip($zip)
    {
        if (strpos($zip, '-') === -1) {
            return [$zip];
        } else {
            return Zip::zipRangeToZipPattern($zip);
        }
    }
}
