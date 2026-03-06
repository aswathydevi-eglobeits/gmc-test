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

use Egits\GoogleMerchantApi\Helper\GoogleHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ProductSyncButton
 * Product sync button in store config
 */
class ProductSyncButton extends Field
{

    /**
     * @var GoogleHelper
     */
    private $googleHelper;

    /**
     * Button template
     *
     * @var string
     */
    protected $_template = 'Egits_GoogleMerchantApi::system/config/sync_products_button.phtml';

    /**
     * ProductSyncButton constructor.
     *
     * @param Context $context
     * @param GoogleHelper $googleHelper
     * @param array $data
     */
    public function __construct(Context $context, GoogleHelper $googleHelper, array $data = [])
    {
        parent::__construct($context, $data);
        $this->setTemplate($this->_template);
        $this->googleHelper = $googleHelper;
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAjaxUrl()
    {
        return $this->escapeUrl(
            $this->getUrl(
                'googlemerchant/product/syncProducts',
                ['store' => $this->googleHelper->getCurrentStore()->getId()]
            )
        );
    }

    /**
     * Get Sync status ajax url
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSyncStatusAjaxUrl()
    {
        return $this->escapeUrl(
            $this->getUrl(
                'googlemerchant/product/syncProductsStatus',
                ['store' => $this->googleHelper->getCurrentStore()->getId()]
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
                'id' => 'sync_products',
                'label' => __('Sync Product To Queue')
            ]
        );
        $currentStoreId = $this->googleHelper->getCurrentStore()->getId();
        if ($this->googleHelper->getConfig()->isProductsSyncedToQueue($currentStoreId)) {
            $button->setData(
                [
                    'id' => 'sync_products',
                    'label' => __('Sync Product To Queue'),
                    'disabled' => 'disabled'
                ]
            );
        }

        return $button->toHtml();
    }
}
