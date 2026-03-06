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
use Egits\GoogleMerchantApi\Model\ProductsHelper;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class SyncProducts
 * Sync product action
 */
class SyncProductsStatus extends Product
{
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
     * @param ProductsHelper $productsHelper
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Action\Context $context,
        ProductsHelper $productsHelper,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
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
        $isQueueInitiated = $this->productsHelper->getConfigFromDb(
            GoogleConfig::CONFIG_PATH_FOR_INTERNAL_QUEUE_STARTED
        );
        $response = [
            'init' => false,
            'status' => '',
        ];

        if ($isQueueInitiated) {
            $response['init'] = true;
            $lastProcessedProduct = $this->productsHelper->getConfigFromDb(
                GoogleConfig::CONFIG_PATH_FOR_SET_PRODUCT_SYNC_PROGRESS
            );

            $status = $lastProcessedProduct ? __(
                "Internal Queue In progress, last processed id: %1",
                $lastProcessedProduct
            ) : __(
                "Internal Queue process is initiated, check here again after some time to view the status"
            );
            $response['status'] = $status;
        }

        return $result->setData($response);
    }
}
