<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Controller\Adminhtml\Product;

use Egits\GoogleMerchantApi\Api\Data\ProductSearchResultInterface;
use Egits\GoogleMerchantApi\Api\Data\ProductsInterface;
use Egits\GoogleMerchantApi\Api\Data\ProductsInterfaceFactory;
use Egits\GoogleMerchantApi\Controller\Adminhtml\Product;
use Egits\GoogleMerchantApi\Helper\GoogleHelper;
use Egits\GoogleMerchantApi\Model\ResourceModel\ProductsRepository;
use Exception;
use Magento\Backend\App\Action;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product as ProductObject;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Sync Individual product to google.
 *
 * Class Sync Individual product to google.
 */
class Sync extends Product
{

    /**
     * @var ProductsRepository
     */
    protected $productsRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ProductRepository
     */
    protected $productCatalogRepository;

    /**
     * @var GoogleHelper
     */
    protected $googleHelper;

    /**
     * @var ProductsInterfaceFactory
     */
    protected $productsInterfaceFactory;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Sync constructor.
     *
     * @param Action\Context $context
     * @param ProductsRepository $productsRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductRepository $productCatalogRepository
     * @param ProductsInterfaceFactory $productsInterfaceFactory
     * @param GoogleHelper $googleHelper
     * @param RedirectFactory $redirectFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Action\Context $context,
        ProductsRepository $productsRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductRepository $productCatalogRepository,
        ProductsInterfaceFactory $productsInterfaceFactory,
        GoogleHelper $googleHelper,
        RedirectFactory $redirectFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->productsRepository = $productsRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productCatalogRepository = $productCatalogRepository;
        $this->googleHelper = $googleHelper;
        $this->productsInterfaceFactory = $productsInterfaceFactory;
        $this->redirectFactory = $redirectFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Sync the current product to google manually.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('product_id', null);

        $result = $this->redirectFactory->create();
        $result->setPath('catalog/product/edit', ['id' => $productId]);
        try {
            $stores = $this->storeManager->getStores();
            $storeIds = array_keys($stores);
            foreach ($storeIds as $productStoreId) {
                $config = $this->googleHelper->getConfig();
                if (!$config->isGoogleMerchantApiEnabled($productStoreId)) {
                    continue;
                }
                if (!$productId || !$productStoreId) {
                    throw new LocalizedException(__('invalid product or store!! Please try again.'));
                }
                $productObject = $this->productCatalogRepository->getById($productId, false, $productStoreId);
                $item = $this->getProductDataExistInQueue($productId, $productStoreId);

                if ($productObject->getTypeId() == 'configurable') {
                    $this->syncConfigurableProduct($item, $productObject, $productStoreId);
                } else {
                    $this->syncItem($item, false, $productObject, $productStoreId);
                }

                if (!in_array(
                    $item->getStatus(),
                    [ProductsInterface::UPDATED_STATUS, ProductsInterface::DELETED_STATUS]
                )
                ) {
                    $this->messageManager->addErrorMessage(
                        __('Something went wrong while updating product!!, please review the log')
                    );
                    return $result;
                }

                $this->messageManager->addSuccessMessage(
                    __('The product synced to google successfully!!')
                );
                $this->productsRepository->save($item);
            }
            return $result;
        } catch (Exception $exception) {
            $this->googleHelper->writeDebugLogFile($exception);
            $this->googleHelper->getApiLogger()->setStoreId($productStoreId)->setSyncType(0)->addMajor(
                sprintf(
                    __('Errors happened during synchronization with Google Shopping for Product Id %s'),
                    $productId
                ),
                [$exception->getMessage()]
            );
            $this->messageManager->addErrorMessage(
                __('Something went wrong while updating product!!, please review the log')
            );
            return $result;
        }
    }

    /**
     * Sync item to google
     *
     * @param ProductsInterface|\Egits\GoogleMerchantApi\Model\Product $item
     * @param bool $isConfigurableChild
     * @param ProductInterface|ProductObject $productObject
     * @param int|null $productStoreId
     */
    protected function syncItem($item, $isConfigurableChild = false, $productObject = null, $productStoreId = null)
    {
        if ($isConfigurableChild) {
            $productObject = $item->getProduct();
            $productStoreId = $item->getProductStoreId();
        }

        if ($item->getId()) {
            $removeInactive = (bool)$this->googleHelper->getConfig()->getAutoRemoveDisabled($productStoreId);
            $productStatus = $productObject->getStatus();

            if ($removeInactive
                && ($productStatus == Status::STATUS_DISABLED)
            ) {
                $item->deleteProductFromGoogle();
            } else {
                $item->updateProductToGoogle();
            }
        } else {
            $item = $this->productsInterfaceFactory->create();
            $item->setProductId($productObject->getId())
                ->setStatus(ProductsInterface::READY_TO_UPDATE_STATUS)
                ->setProductStoreId($productStoreId)
                ->setProduct($productObject)
                ->updateProductToGoogle();
        }
    }

    /**
     * Change this load by product id;
     *
     * @param int $productId
     * @param int $storeId
     * @return ProductsInterface|\Egits\GoogleMerchantApi\Model\Product
     */
    protected function getProductDataExistInQueue($productId, $storeId)
    {
        $productItems = $this->productsRepository->loadByProductId($productId, $storeId);
        return $productItems;
    }

    /**
     * Get child product items from queue.
     *
     * @param array $childProductIds
     * @param int $storeId
     * @return ProductSearchResultInterface
     */
    protected function getAllChildItemFromQueue($childProductIds, $storeId)
    {
        return $this->productsRepository->getList($this->getSearchCriteriaForChildProducts($childProductIds, $storeId));
    }

    /**
     * Sync Configurable product
     *
     * @param ProductsInterface $item
     * @param ProductInterface|ProductObject $parent
     * @param int $productStoreId
     */
    protected function syncConfigurableProduct($item, $parent, $productStoreId)
    {
        /** @var ProductInterface[]|ProductObject[] $childProducts */
        $childProducts = $parent->getTypeInstance()->getUsedProducts($parent);
        $childProductsIds = [];

        foreach ($childProducts as $childProduct) {
            $childProduct->setData('item_parent_product', $parent);
            $childProductsIds[$childProduct->getId()] = $childProduct;
        }

        /** @var ProductsInterface[] $childItemsFromQueue */
        $childItemsFromQueue = $this->getAllChilditemFromQueue(
            array_keys($childProductsIds),
            $productStoreId
        )->getItems();
        $failed = 0;
        foreach ($childItemsFromQueue as $childItem) {
            $childItem->setProduct($childProductsIds[$childItem->getProductId()]);
            $this->syncItem($childItem, true);

            if (!in_array(
                $childItem->getStatus(),
                [ProductsInterface::UPDATED_STATUS, ProductsInterface::DELETED_STATUS]
            )
            ) {
                $failed++;
            }

            $this->productsRepository->save($childItem);
        }

        if ($failed > 0) {
            $this->messageManager->addErrorMessage(
                __('One or more child products failed to sync, Please review the logs.')
            );
        }

        $item->setStatus(ProductsInterface::UPDATED_STATUS);
    }

    /**
     * Get Search criteria for child product in queue.
     *
     * @param array $childProductsArray
     * @param int $productStoreId
     * @return SearchCriteria
     */
    private function getSearchCriteriaForChildProducts($childProductsArray, $productStoreId)
    {
        return $this->searchCriteriaBuilder->addFilter(
            ProductsInterface::PRODUCT_ID,
            $childProductsArray,
            'in'
        )->addFilter(
            ProductsInterface::STORE_ID,
            $productStoreId
        )->addFilter(
            ProductsInterface::STATUS,
            [
                ProductsInterface::READY_TO_UPDATE_STATUS,
                ProductsInterface::FAILED_STATUS,
                ProductsInterface::SKIPPED_STATUS
            ],
            'in'
        )->create();
    }
}
