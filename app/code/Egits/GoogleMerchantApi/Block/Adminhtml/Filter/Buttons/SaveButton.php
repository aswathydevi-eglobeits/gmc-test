<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Block\Adminhtml\Filter\Buttons;

use Egits\GoogleMerchantApi\Block\Adminhtml\Edit\SaveButton as GenericSaveButton;

/**
 * Class SaveButton
 * Filter save button
 */
class SaveButton extends GenericSaveButton
{
    /**
     * Get Button data
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getId()) {
            $data = parent::getButtonData();
        }

        return $data;
    }
}
