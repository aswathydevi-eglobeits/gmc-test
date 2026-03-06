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
use Magento\Framework\App\ResponseInterface;

/**
 * AttributeMap Type Edit controller
 *
 * Class Edit
 */
class Edit extends Attribute
{
    /**
     * Edit action for Attribute Map type
     *
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface|void
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $attributeMap = $this->attributeMapTypeFactory->create();
        try {
            if ($id) {
                $attributeMap = $this->attributeMapTypeRepository->getAttributeMapTypeById($id, $attributeMap);
                if (!$attributeMap->getId()) {
                    $this->messageManager->addErrorMessage(__('This Attribute Mapping no longer exists.'));
                    $this->_redirect('googlemerchant/*');
                    return;
                }
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('This Attribute Mapping no longer exists.'));
            $this->_redirect('googlemerchant/*');
            return;
        }

        // set entered data if was error when we do save
        $data = $this->_session->getAttributeMappingData(true);
        if (!empty($data)) {
            $attributeMap->addData($data);
        }

        $this->coreRegistry->register('current_googlemerchant_attribute_map_type', $attributeMap);
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $attributeMap->getId() ? __('Edit Attribute Id ' . $attributeMap->getId()) : __('New Attribute Mapping')
        );

        $this->_view->renderLayout();
    }
}
