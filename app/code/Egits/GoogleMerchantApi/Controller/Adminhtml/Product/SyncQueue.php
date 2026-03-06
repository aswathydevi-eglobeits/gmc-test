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
use Egits\GoogleMerchantApi\Cron\SyncProduct;
use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Json;
use \Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class syncQueue
 * Sync queue action
 */
class SyncQueue extends Product
{
    /**
     * @var SyncProduct
     */
    protected $syncProduct;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * syncQueue constructor.
     *
     * @param Action\Context $context
     * @param SyncProduct $syncProduct
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Action\Context $context,
        SyncProduct $syncProduct,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->syncProduct = $syncProduct;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $status = [
            'error'   => true,
            'message' => 'Sync failed.Please review logs'
        ];
        try {
            $this->syncProduct->setForceSync()->execute();
            if ($this->syncProduct->syncStatus) {
                $status = [
                    'error'   => false,
                    'message' => __('Product Sync completed!!')
                ];
            }
        } catch (Exception $exception) {
            $status = [
                'error'   => true,
                'message' => $exception->getMessage()
            ];
        }

        return $result->setData($status);
    }
}
