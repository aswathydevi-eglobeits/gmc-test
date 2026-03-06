<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Controller\Adminhtml\Category;

use Egits\GoogleMerchantApi\Controller\Adminhtml\Category;
use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Save
 * Category mapping save action
 */
class Save extends Category
{

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return ResultInterface|ResponseInterface|void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        try {
            $mappingAddedCount = $this->save();
            if ($mappingAddedCount > 0) {
                $this->messageManager->addSuccessMessage(
                    __('You saved Category Mapping for ' . $mappingAddedCount . ' Categories')
                );
            }

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('googlemerchant/category/edit');
                return;
            }

            $this->_redirect('admin/dashboard/index');
            return;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('googlemerchant/*/edit');
            return;
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the Category Mapping data. Please review the error log.')
            );
            $this->logger->debug($e);
            $this->_session->setMappingData($data);
            $this->_redirect('googlemerchant/*/edit');
            return;
        }

        $this->_redirect('admin/dashboard/index');
    }

    /**
     * Save category mapping
     *
     * @return int
     */
    protected function save()
    {
        $data = $this->getRequest()->getPostValue();
        $noOfRowsAdded = 0;
        if ($data) {
            $mappingData = [];
            if (isset($data['mapping'])) {
                $mappingData = $data['mapping'];
                unset($data['mapping']);
            }

            $insertData = [];
            foreach ($mappingData as $categoryId => $googleCategoryId) {
                if ($googleCategoryId) {
                    $insertData[] = ['category_id' => (int)$categoryId, 'google_category_id' => (int)$googleCategoryId];
                }
            }

            $this->_session->setMappingData($mappingData);
            $noOfRowsAdded = $this->mappingRepositoryFactory->create()->saveMultiple($insertData);
            $this->_session->setMappingData(false);
        }

        return $noOfRowsAdded;
    }
}
