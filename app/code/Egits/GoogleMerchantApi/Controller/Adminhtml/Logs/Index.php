<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package    Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Controller\Adminhtml\Logs;

use Egits\GoogleMerchantApi\Controller\Adminhtml\Logs;

/**
 * Class Index
 * Logs index action
 */
class Index extends Logs
{

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Api Logs'));
        $this->_view->renderLayout();
    }
}
