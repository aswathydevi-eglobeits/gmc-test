<?php

namespace Egits\GoogleMerchantApi\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Class CategoryPriority
 * Base admin controller for category priority-related actions.
 */
abstract class CategoryPriority extends Action
{
    /**
     * Check if the current admin user is authorized to access the category priority section.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Egits_GoogleMerchantApi::feed');
    }
}
