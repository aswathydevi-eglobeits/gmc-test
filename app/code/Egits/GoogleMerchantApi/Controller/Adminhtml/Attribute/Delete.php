<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Controller\Adminhtml\Attribute;

use Egits\GoogleMerchantApi\Controller\Adminhtml\Attribute;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Delete Attribute Controller
 * Attribute delete action
 */
class Delete extends Attribute
{

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface|void
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->attributeMapTypeRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the attribute mapping.'));
                $this->_redirect('googlemerchant/*/');
                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete the attribute mapping right now. Please review the log and try again.')
                );
                $this->logger->debug($e);
                $this->_redirect('googlemerchant/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a attribute mapping to delete.'));
        $this->_redirect('googlemerchant/*/');
    }
}
