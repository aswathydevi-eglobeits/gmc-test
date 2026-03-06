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

use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class TestConnectionButton
 * Test google merchant api connection button
 */
class TestConnectionButton extends Field
{
    /** Button template
     *
     * @var string
     */
    protected $_template = 'Egits_GoogleMerchantApi::system/config/test_connection_button.phtml';

      /**
       * @param Context            $context
       * @param array              $data
       */
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->setTemplate($this->_template);
    }

    /**
     * @inheritDoc
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
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
     * Get ajax url
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->escapeUrl(
            $this->getUrl(
                'googlemerchant/test/',
                ['store' => $this->getRequest()->getParam('store', 0)]
            )
        );
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
                'id' => 'test_connection',
                'label' => __('Test Connection'),
            ]
        );

        return $button->toHtml();
    }
}
