<?php

namespace Egits\GoogleMerchantApi\Controller\Adminhtml\Attribute;

use Egits\GoogleMerchantApi\Controller\Adminhtml\Attribute;

/**
 * Class Index Controller
 * Attribute index action
 */
class Index extends Attribute
{
    /**
     * Google Merchant attribute index action
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Google Merchant attribute mapping'));
        $this->_view->renderLayout();
    }
}
