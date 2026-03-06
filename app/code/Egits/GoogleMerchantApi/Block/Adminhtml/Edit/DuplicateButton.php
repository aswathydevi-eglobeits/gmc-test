<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DuplicateButton
 * Duplicate button block class
 */
class DuplicateButton extends GenericButton implements ButtonProviderInterface
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
            $data = [
                'label' => __('Duplicate'),
                'class' => 'duplicate',
                'on_click' => 'window.location=\'' . $this->getDuplicateUrl() . '\'',
                'sort_order' => 40,
            ];
        }

        return $data;
    }

    /**
     * Get duplicate url
     *
     * @return string
     */
    public function getDuplicateUrl()
    {
        return $this->getUrl('*/*/duplicate', ['id' => $this->getId()]);
    }
}
