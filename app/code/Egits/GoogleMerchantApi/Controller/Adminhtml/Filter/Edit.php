<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Controller\Adminhtml\Filter;

use Egits\GoogleMerchantApi\Block\Adminhtml\Filter\Tabs\Conditions;
use Egits\GoogleMerchantApi\Controller\Adminhtml\Filter;
use Egits\GoogleMerchantApi\Model\Rule;
use Egits\GoogleMerchantApi\Model\Filter as FilterModel;

/**
 * Class Edit
 * Filer Edit action
 */
class Edit extends Filter
{
    /**
     * Filter Edit Action
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $filterModel = $this->filterFactory->create();
        if ($id) {
            $this->filterRepository->loadFilterById($id, $filterModel);
            if (!$filterModel->getId()) {
                $this->messageManager->addErrorMessage(__('This Filter no longer exists.'));
                $this->_redirect('googlemerchant/*');
                return;
            }
        }

        $this->rule->setConditions([]);
        $this->rule->setConditionsSerialized($filterModel->getConditionsSerialized());
        $this->rule->getConditions()->setJsFormObject(Conditions::FORM_FIELD_SET);
        // set entered data if was error when we do save
        $data = $this->_session->getFilterData(true);
        if (!empty($data)) {
            $filterModel->addData($data);
        }

        $this->coreRegistry->register(FilterModel::CURRENT_FILTER_DATA_KEY, $filterModel);
        $this->coreRegistry->register(Rule::CURRENT_RULE_DATA_KEY, $this->rule);
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $filterModel->getId() ? $filterModel->getFilterName() : __('New Filter')
        );

        $this->_view->renderLayout();
    }
}
