<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package    Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Controller\Adminhtml\Product;

use Egits\GoogleMerchantApi\Controller\Adminhtml\Product;

/**
 * Class Index
 * Google product index action
 */
class Index extends Product
{
    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Merchant Center Products'));
        $this->_view->renderLayout();
    }
}
