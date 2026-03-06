<?php
/**
 *
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_
 * @copyright   Copyright (c) 2018 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Observer;

use Egits\GoogleMerchantApi\Helper\GoogleHelper;
use Egits\GoogleMerchantApi\Model\ProductsHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class RemoveItem
 * Product delete observer
 */
class RemoveItem implements ObserverInterface
{
    /**
     * @var GoogleHelper
     */
    public $googleHelper;

    /**
     * @var ProductsHelper
     */
    public $productsHelper;

    /**
     * AddProductQueue constructor.
     *
     * @param GoogleHelper $googleHelper
     * @param ProductsHelper $productsHelper
     */
    public function __construct(
        GoogleHelper $googleHelper,
        ProductsHelper $productsHelper
    ) {
        $this->googleHelper = $googleHelper;
        $this->productsHelper = $productsHelper;
    }

    /**
     * Product save after
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            $websites = $product->getWebsiteIds();
            foreach ($websites as $websiteId) {
                $website = $this->googleHelper->getWebsite($websiteId);
                $storeIds = $website->getStoreIds();
                foreach ($storeIds as $storeId) {
                    $this->productsHelper->setGoogleHelper($this->googleHelper)
                        ->setProductStoreId($storeId)
                        ->removeItem($product);
                }
            }
        } catch (\Exception $exception) {
            $this->googleHelper->writeDebugLogFile($exception);
            return;
        }
    }
}
