<?php

namespace Egits\GoogleMerchantApi\Controller\Adminhtml\CategoryPriority;

use Egits\GoogleMerchantApi\Controller\Adminhtml\CategoryPriority;

/**
 * Class Index
 * Entry point for the Category Priority admin section.
 */
class Index extends CategoryPriority
{
    /**
     * Category Priority index action
     */
    public function execute()
    {
        $this->_forward('priority');
    }
}
