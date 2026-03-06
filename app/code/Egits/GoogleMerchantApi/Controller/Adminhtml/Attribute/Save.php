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

use Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterface;
use Egits\GoogleMerchantApi\Controller\Adminhtml\Attribute;
use Egits\GoogleMerchantApi\Model\AttributeMapType;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Save Controller
 * Attribute map save action
 */
class Save extends Attribute
{

    /**
     * SaveAction For AttributeMapping
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        try {
            $model = $this->save();
            $this->messageManager->addSuccessMessage(__('You saved the Attribute Mapping.'));

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('googlemerchant/attribute/edit', ['id' => $model->getId()]);
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
                __('Something went wrong while saving the Attribute Map data. Please review the error log.')
            );
            $this->logger->debug($e);
            $this->_session->getAttributeMappingData($data);
            $this->_redirect('googlemerchant/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            return;
        }

        $this->_redirect('googlemerchant/*/');
    }

    /**
     * Save Function
     *
     * @return \Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterface
     * @throws LocalizedException
     */
    protected function save()
    {
        $data = $this->getRequest()->getPostValue();
        $attributeMappingType = $this->attributeMapTypeFactory->create();
        if ($data) {
            $id = $this->getCurrentTypeId();
            if ($id) {
                $attributeMappingType = $this->attributeMapTypeRepository->getAttributeMapTypeById(
                    $id,
                    $attributeMappingType
                );
                if ($id != $attributeMappingType->getId()) {
                    throw new LocalizedException(__('The wrong Attribute Map Id is specified.'));
                }

                $attributeMappingType->setId($attributeMappingType->getId());
            }

            //change this to some function
            $attributeTypeData = $data['attribute'];
            if (!$id
                && $this->isMapTypeAlreadyExist(
                    $attributeTypeData['attribute_store_id'],
                    $attributeTypeData['target_country']
                )
            ) {
                throw new LocalizedException(__('The Store and Target Country combination already exist'));
            }

            $attributeMappingType->setName($attributeTypeData['name'])
                ->setStoreId($attributeTypeData['attribute_store_id'])
                ->setTargetCountry($attributeTypeData['target_country'])
                ->setAttributeMap($this->getAttributeMappingPostData());

            $this->_session->setAttributeMappingData($attributeMappingType->getData());
            $this->attributeMapTypeRepository->save($attributeMappingType);
            $this->_session->setAttributeMappingData(false);
        }

        return $attributeMappingType;
    }

    /**
     * Retrieve current type ID
     *
     * @return int
     */
    private function getCurrentTypeId()
    {
        $originalRequestData = $this->getRequest()->getPostValue(AttributeMapType::ENTITY_TYPE);

        $typeId = isset($originalRequestData['type_id'])
            ? $originalRequestData['type_id']
            : null;

        return $typeId;
    }

    /**
     * Get Attribute mapping data from post
     *
     * @return array
     */
    private function getAttributeMappingPostData()
    {
        $postMappingData = [];
        $data = $this->getRequest()->getPostValue('attribute');
        if (isset($data['attribute_map_rows_container'])
            && isset($data['attribute_map_rows_container']['attribute_map_rows_container'])
        ) {
            $postMappingData = $data['attribute_map_rows_container']['attribute_map_rows_container'];
        }

        return $postMappingData;
    }

    /**
     * Check if map type already exist.
     *
     * @param int $storeId
     * @param string $targetCountry
     * @return bool
     */
    protected function isMapTypeAlreadyExist($storeId, $targetCountry)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            'main_table.' . AttributeMapTypeInterface::STORE_ID,
            $storeId
        )->addFilter(
            AttributeMapTypeInterface::TARGET_COUNTRY,
            $targetCountry
        )->create();
        $totalCount = $this->attributeMapTypeRepository->getList($searchCriteria)->getTotalCount();
        return $totalCount > 0 ? true : false;
    }
}
