<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Observer;

use Egits\GoogleMerchantApi\Helper\GoogleHelper;
use Egits\GoogleMerchantApi\Model\ProductsHelper;
use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Egits\GoogleMerchantApi\Api\GetParentIdsByProductInterface;

/**
 * Class AddProductQueue
 * Product save observer
 */
class AddProductQueue implements ObserverInterface
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
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * @var GetParentIdsByProductInterface
     */
    private $getParentIdsByProduct;

    /**
     * AddProductQueue constructor.
     *
     * @param GoogleHelper $googleHelper
     * @param ProductsHelper $productsHelper
     * @param CollectionFactory $collectionFactory
     * @param GetParentIdsByProductInterface $getParentIdsByProduct
     */
    public function __construct(
        GoogleHelper $googleHelper,
        ProductsHelper $productsHelper,
        CollectionFactory $collectionFactory,
        GetParentIdsByProductInterface $getParentIdsByProduct
    ) {
        $this->googleHelper = $googleHelper;
        $this->productsHelper = $productsHelper;
        $this->collectionFactory = $collectionFactory;
        $this->getParentIdsByProduct = $getParentIdsByProduct;
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
            $config = $this->googleHelper->getConfig();
            $product = $observer->getEvent()->getProduct();

            $parentIds = $this->getParentIdsByProduct->execute($product);
            $productsToProcess = [];
            if (count($parentIds) > 0) {
                $parentProducts = $this->collectionFactory->create()->addFieldToFilter('entity_id', $parentIds);
                foreach ($parentProducts as $parentProduct) {
                    $productsToProcess[] = $parentProduct;
                }
            } else {
                $productsToProcess[] = $product;
            }

            $websites = $product->getWebsiteIds();
            // Return if no websites are assigned
            if (empty($websites)) {
                return;
            }
            foreach ($websites as $websiteId) {
                $website = $this->googleHelper->getWebsite($websiteId);
                $storeIds = $website->getStoreIds();
                foreach ($storeIds as $storeId) {
                    $config = $this->googleHelper->getConfig();
                    if (!$config->isGoogleMerchantApiEnabled($storeId)) {
                        continue;
                    }
                    foreach ($productsToProcess as $productToProcess) {
                        $productToProcess->setData('is_product_save', true);
                        $this->productsHelper->setGoogleHelper($this->googleHelper)
                            ->setProductStoreId($storeId)
                            ->addProductsToQueue($productToProcess);
                        $this->addToAllStore($productToProcess);
                    }
                }
            }
        } catch (Exception $exception) {
            $this->googleHelper->writeDebugLogFile($exception);
            return;
        }
    }

    /**
     *  Add to all store
     *
     * @param Product $product
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addToAllStore($product)
    {
        $websites = $product->getWebsiteIds();
        // Return if no websites are assigned
        if (empty($websites)) {
            return;
        }
        foreach ($websites as $websiteId) {
            $website = $this->googleHelper->getWebsite($websiteId);
            $storeIds = $website->getStoreIds();
            foreach ($storeIds as $storeId) {
                if ($storeId != $product->getStoreId()) {
                    $config = $this->googleHelper->getConfig();
                    if ($config->isGoogleMerchantApiEnabled($storeId)) {
                        $product->setData('is_product_save', true);
                        $this->productsHelper->setGoogleHelper($this->googleHelper)
                            ->setProductStoreId($storeId)
                            ->addProductsToQueue($product);
                    }
                }
            }
        }
    }
}
