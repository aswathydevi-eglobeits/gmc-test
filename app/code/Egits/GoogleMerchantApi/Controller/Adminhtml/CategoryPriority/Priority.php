<?php
namespace Egits\GoogleMerchantApi\Controller\Adminhtml\CategoryPriority;

use Egits\GoogleMerchantApi\Controller\Adminhtml\CategoryPriority;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Priority
 *
 * Controller for rendering the admin configuration page
 * for Google Merchant Category Priority .
 */
class Priority extends CategoryPriority
{
    /**
     * Execute method to load and render the layout for category priority settings.
     *
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Google Merchant Category Priority '));
        $this->_view->renderLayout();
    }
}
