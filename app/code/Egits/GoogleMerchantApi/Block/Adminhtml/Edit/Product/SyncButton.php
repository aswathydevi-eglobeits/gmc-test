<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Block\Adminhtml\Edit\Product;

use Egits\GoogleMerchantApi\Block\Adminhtml\Edit\GenericButton;
use Egits\GoogleMerchantApi\Helper\GoogleHelper;
use Egits\GoogleMerchantApi\Model\ResourceModel\ProductsRepository;
use Magento\Backend\Block\Widget\Context;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\Registry;

/**
 * Class SyncButton
 * Product sync button block class
 */
class SyncButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var GoogleHelper
     */
    private $googleHelper;

    /**
     * @var ProductsRepository
     */
    protected $productsRepository;

    /**
     * SyncButton constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param GoogleHelper $googleHelper
     * @param ProductsRepository $productsRepository
     */
    public function __construct(
        Context $context,
        Registry $registry,
        GoogleHelper $googleHelper,
        ProductsRepository $productsRepository
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->googleHelper = $googleHelper;
        $this->productsRepository = $productsRepository;
    }

    /**
     * Retrieve button-specified settings for sync now button on product page
     *
     * @return array
     */
    public function getButtonData()
    {
        $message = __('Are you sure you want to sync this product with Google merchant account?');
        $message .= __(
            'The time for this operation depends on no of child products and target country selected in config.'
        );
        if (!$this->getCurrentProductId() || !$this->isButtonAllowed() || !$this->isProductExistInQueue()) {
            return [];
        }

        return [
            'label'      => __('Sync With Google'),
            'class'      => 'action-secondary',
            'on_click'   => "confirmSetLocation('{$message}', '{$this->getSyncUrl()}')",
            'sort_order' => 10,

        ];
    }

    /**
     * Get Sync url
     *
     * @return string
     */
    protected function getSyncUrl()
    {
        return $this->getUrl(
            'googlemerchant/product/sync',
            ['product_id' => $this->getCurrentProductId(), 'store_id' => $this->getStoreId()]
        );
    }

    /**
     * Get current product id
     *
     * @return int
     */
    private function getCurrentProductId()
    {
        return $this->getCurrentProduct()->getId();
    }

    /**
     * Get product store id
     *
     * @return mixed
     */
    private function getStoreId()
    {
        $productStoreId = [];
        $product= $this->getCurrentProduct();
        $websites = $product->getWebsiteIds();
        // Return null if no websites are assigned
        if (empty($websites)) {
            return null;
        }
        foreach ($websites as $websiteId) {
            $website = $this->googleHelper->getWebsite($websiteId);
            $storeIds = $website->getStoreIds();
            foreach ($storeIds as $storeId) {
                $productStoreId[] = $storeId;
            }
        }

        return $productStoreId;
    }

    /**
     * Check is button allowed for this product.
     *
     * @return bool
     */
    private function isButtonAllowed()
    {
        $result = true;
        if ($this->getCurrentProduct()->getTypeId() == 'configurable'
            && !$this->googleHelper->getConfig()->isAllowedConfigurableParent()
        ) {
            $result = false;
        }

        return $result;
    }

    /**
     * Get Current product
     *
     * @return ProductInterface
     */
    private function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Check if current product exist in queue if no hide button
     */
    protected function isProductExistInQueue()
    {
        $result = false;
        $item = $this->productsRepository->loadByProductId($this->getCurrentProductId(), $this->getStoreId());
        if ($item && $item->getId()) {
            $result = true;
        }

        return $result;
    }
}
