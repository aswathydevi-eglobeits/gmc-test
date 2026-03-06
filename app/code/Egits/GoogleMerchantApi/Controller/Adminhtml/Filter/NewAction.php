<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Controller\Adminhtml\Filter;

use Egits\GoogleMerchantApi\Controller\Adminhtml\Filter;

/**
 * Class NewAction
 * Filter new action
 */
class NewAction extends Filter
{
    /**
     * New filter action
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
