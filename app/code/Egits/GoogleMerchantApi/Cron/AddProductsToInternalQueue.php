<?php

namespace Egits\GoogleMerchantApi\Cron;

use Egits\GoogleMerchantApi\Helper\GoogleConfig;
use Egits\GoogleMerchantApi\Helper\GoogleHelper;
use Egits\GoogleMerchantApi\Model\ProductsHelper;

class AddProductsToInternalQueue
{
    /**
     * @var GoogleHelper
     */
    private GoogleHelper $googleHelper;
    /**
     * @var ProductsHelper
     */
    private ProductsHelper $productsHelper;

    /**
     * AddProductsToInternalQueue constructor
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
     * Execute cron
     */
    public function execute()
    {
        if ($this->productsHelper->getConfigFromDb(GoogleConfig::CONFIG_PATH_FOR_SET_PRODUCT_SYNC_DONE)) {
            return  false;
        }

        if (!$this->productsHelper->getConfigFromDb(GoogleConfig::CONFIG_PATH_FOR_INTERNAL_QUEUE_STARTED)) {
            return  false;
        }

        $this->productsHelper->setGoogleHelper($this->googleHelper)
            ->syncProductsToQueue();
    }
}
