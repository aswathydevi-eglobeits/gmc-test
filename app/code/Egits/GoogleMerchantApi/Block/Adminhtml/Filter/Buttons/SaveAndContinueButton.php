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

use Egits\GoogleMerchantApi\Block\Adminhtml\Edit\SaveAndContinueButton as GenericSaveAndContinueButton;

/**
 * Class SaveAndContinueButton
 * Filter save and continue button block
 */
class SaveAndContinueButton extends GenericSaveAndContinueButton
{
    /**
     * Retrieve button-specified settings
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = parent::getButtonData();
        if (!$this->getId()) {
            $data['class'] = 'save primary';
        }

        return $data;
    }
}
