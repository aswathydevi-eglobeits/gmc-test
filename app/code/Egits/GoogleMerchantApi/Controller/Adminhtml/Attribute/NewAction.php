<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Controller\Adminhtml\Attribute;

use Egits\GoogleMerchantApi\Controller\Adminhtml\Attribute;

/**
 * Class NewAction
 * Attribute map new action
 */
class NewAction extends Attribute
{
    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
