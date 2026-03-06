<?php

namespace Egits\GoogleMerchantApi\Controller\Adminhtml\Category;

use Egits\GoogleMerchantApi\Controller\Adminhtml\Category;

/**
 * Class Index
 * Category mapping index action
 */
class Index extends Category
{
    /**
     * Google Merchant Api Category Mapping index action
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
