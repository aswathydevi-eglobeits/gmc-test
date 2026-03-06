<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package    Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Controller\Adminhtml\Filter;

use Egits\GoogleMerchantApi\Controller\Adminhtml\Filter;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Egits\GoogleMerchantApi\Model\Filter as FilterModel;

/**
 * Class Save
 * Filter save action
 */
class Save extends Filter
{

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface|void
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        try {
            $model = $this->save();
            $this->messageManager->addSuccessMessage(__('You saved the Filter.'));
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('googlemerchant/filter/edit', ['id' => $model->getId()]);
                return;
            }

            $this->_redirect('googlemerchant/*/');
            return;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $id = (int)$this->getRequest()->getParam('id');
            if (!empty($id)) {
                $this->_redirect('googlemerchant/*/edit', ['id' => $id]);
            } else {
                $this->_redirect('googlemerchant/*/new');
            }

            return;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the filter data. Please review the error log.')
            );
            $this->logger->debug($e);
            $this->_session->setFilterData($data);
            $this->_redirect('googlemerchant/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            return;
        }

        $this->_redirect('googlemerchant/*/');
    }

    /**
     * Save Action
     *
     * @return mixed
     * @throws LocalizedException
     */
    protected function save()
    {
        $data = $this->getRequest()->getPostValue();
       
        $filterModel = $this->filterFactory->create();
        if ($data) {
            $id = $this->getCurrentFilterId();
            if ($id) {
                $this->filterRepository->loadFilterById($id, $filterModel);
                if ($id != $filterModel->getId()) {
                    throw new LocalizedException(__('The wrong Filter Id is specified.'));
                }

                $filterModel->setId($filterModel->getId());
            }

            $filterData = [];
            if (isset($data['filter'])) {
                $filterData = $data['filter'];
                unset($data['filter']);
            }

            if (isset($data['rule']) && isset($data['rule']['conditions'])) {
                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);
                $this->rule->loadPost($data);
                $filterData['conditions'] = $filterModel->getSerializer()
                    ->serialize($this->rule->getConditions()->asArray());
            }

            $filterModel->setData($filterData);
            $this->_session->setFilterData($filterModel->getData());
            $this->filterRepository->save($filterModel);
            $this->_session->setFilterData(false);
        }

        return $filterModel;
    }

    /**
     * Retrieve current filter ID
     *
     * @return int
     */
    private function getCurrentFilterId()
    {
        $originalRequestData = $this->getRequest()->getPostValue(FilterModel::ENTITY_TYPE);
        $id = isset($originalRequestData['entity_id'])
            ? $originalRequestData['entity_id']
            : null;

        return $id;
    }
}
