<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Controller\Adminhtml\Category;

use Egits\GoogleMerchantApi\Controller\Adminhtml\Category;

/**
 * Class Edit
 * Google category mapping Edit action
 */
class Edit extends Category
{

    /**
     *  Category mapping edit action.
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Google Merchant Category Mapping'));
        $this->_view->renderLayout();
    }
}
