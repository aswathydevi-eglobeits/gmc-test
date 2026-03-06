<?php

namespace Egits\GoogleMerchantApi\Block\Adminhtml\CategoryPriority\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class BackButton
 * Provides the configuration for the "Back" button in the admin category priority edit form.
 */
class BackButton extends GenericButton implements ButtonProviderInterface
{
    /**
     *  Get button data for "Back" button.
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
     *  Retrieve the URL to navigate to when "Back" button is clicked.
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('admin/dashboard/index');
    }
}
