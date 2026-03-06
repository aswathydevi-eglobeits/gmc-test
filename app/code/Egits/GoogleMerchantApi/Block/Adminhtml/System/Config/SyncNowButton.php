<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class SyncNowButton
 * Sync queue button in store config
 */
class SyncNowButton extends Field
{
    /** Button template
     *
     * @var string
     */
    protected $_template = 'Egits_GoogleMerchantApi::system/config/sync_now_button.phtml';

    /**
     * SyncNowButton constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->setTemplate($this->_template);
    }

    /**
     * Method Render
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @inheritDoc
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Get ajax url
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->escapeUrl($this->getUrl('googlemerchant/product/syncQueue'));
    }

    /**
     * Get button html
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            Button::class
        )->setData(
            [
                'id' => 'sync_now_products',
                'label' => __('Sync With Google Merchant'),
            ]
        );

        return $button->toHtml();
    }
}
