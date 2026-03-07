<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Controller\Adminhtml\Test;

use Egits\GoogleMerchantApi\Model\GoogleShopping;
use Google\Shopping\Merchant\Products\V1\Client\ProductsServiceClient;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class Test Index
 * Test google merchant api connection action
 */
class Index extends Action
{
    /**
     * @var GoogleShopping
     */
    protected $googleShopping;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Index constructor.
     *
     * @param Action\Context $context
     * @param GoogleShopping $googleShopping
     * @param JsonFactory    $resultJsonFactory
     */
    public function __construct(
        Action\Context $context,
        GoogleShopping $googleShopping,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->googleShopping = $googleShopping;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute action based on request and return result
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $status = ['error' => true, 'message' => __('Test Connection failed')];
        $storeId = $this->getRequest()->getParam('store', 0);
        try {
            $shoppingService = $this->googleShopping->setStore($storeId)->getShoppingService();
            if ($shoppingService instanceof ProductsServiceClient) {
                $status = ['error' => false, 'message' => __('Connection Successful')];
            } else {
                $status = ['error' => true, 'message' => __('Test Connection failed')];
            }
        } catch (\Exception $e) {
            $this->googleShopping->getGoogleHelper()->writeDebugLogFile($e);
            $status = ['error' => true, 'message' => $e->getMessage()];
        }

        return $result->setData($status);
    }
}
