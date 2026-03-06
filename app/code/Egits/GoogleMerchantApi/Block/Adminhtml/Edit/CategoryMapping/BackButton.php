<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Block\Adminhtml\Edit\CategoryMapping;

use Egits\GoogleMerchantApi\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class BackButton
 * Button class Back button in form
 */
class BackButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Admin dashboard url path
     */
    public const PATH_ADMIN_DASHBOARD = 'admin/dashboard/index';

    /**
     * Get Button data
     *
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10
        ];
    }

    /**
     * Get URL for back (reset) button,back to dash board.
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl(self::PATH_ADMIN_DASHBOARD);
    }
}
