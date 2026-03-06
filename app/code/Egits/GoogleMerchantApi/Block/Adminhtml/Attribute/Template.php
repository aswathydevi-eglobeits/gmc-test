<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Block\Adminhtml\Attribute;

use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;

/**
 * Attribute
 *
 * Class Template
 */
class Template extends Container
{
    /**
     * Template constructor.
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->addNewButton();
    }

    /**
     *  Add New Attribute Mapping Button
     */
    protected function addNewButton()
    {
        $this->addButton(
            'add',
            [
                'label'      => __("Add New Mapping"),
                'class'      => 'add primary',
                'class_name' => Button::class,
                'onclick'    => 'setLocation(\'' . $this->getCreateUrl() . '\')',
            ]
        );
    }

    /**
     * Ger New Attribute Mapping action url
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/newAction');
    }
}
