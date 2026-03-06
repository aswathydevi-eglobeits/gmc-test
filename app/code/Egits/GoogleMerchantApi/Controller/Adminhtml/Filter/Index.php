<?php

namespace Egits\GoogleMerchantApi\Controller\Adminhtml\Filter;

use Egits\GoogleMerchantApi\Controller\Adminhtml\Filter;

/**
 * Class Index
 * Filter index action
 */
class Index extends Filter
{
    /**
     * Google Merchant Api Filter index action
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Filters'));
        $this->_view->renderLayout();
    }
}
