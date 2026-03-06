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

use Egits\GoogleMerchantApi\Controller\Adminhtml\Product;
use Egits\GoogleMerchantApi\Helper\GoogleConfig;
use Egits\GoogleMerchantApi\Helper\GoogleHelper;
use Egits\GoogleMerchantApi\Model\ProductsHelper;
use Magento\Backend\App\Action;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class SyncProducts
 * Sync product action
 */
class SyncProducts extends Product
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var GoogleHelper
     */
    protected $googleHelper;

    /**
     * @var Config
     */
    protected $configResource;

    /**
     * @var ProductsHelper
     */
    protected $productsHelper;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * SyncProducts constructor.
     *
     * @param Action\Context $context
     * @param ProductRepository $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GoogleHelper $googleHelper
     * @param Config $config
     * @param ProductsHelper $productsHelper
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Action\Context $context,
        ProductRepository $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GoogleHelper $googleHelper,
        Config $config,
        ProductsHelper $productsHelper,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->googleHelper = $googleHelper;
        $this->configResource = $config;
        $this->productsHelper = $productsHelper;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Add all product to queue
     *
     * @return Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $status = $this->productsHelper->setProductQueueSyncFlag();

        if ($status) {
            $status = [
                'error'    => false,
                'message'  => __('Queuing process has initiated, you can refresh this page to see the status.')
            ];
        } else {
            $status = [
                'error' => true,
                'message' => __('Adding product to queue failed !! Please review log file')
            ];
        }
        return $result->setData($status);
    }
}
