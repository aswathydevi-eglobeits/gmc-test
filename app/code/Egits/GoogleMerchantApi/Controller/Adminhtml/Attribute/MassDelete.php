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

use Egits\GoogleMerchantApi\Api\AttributeMapTypeRepositoryInterface;
use Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterfaceFactory;
use Egits\GoogleMerchantApi\Controller\Adminhtml\Attribute;
use Egits\GoogleMerchantApi\Logger\Logger;
use Egits\GoogleMerchantApi\Model\AttributeMapType;
use Egits\GoogleMerchantApi\Model\ResourceModel\AttributeMapType\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassDelete
 * Attribute map Mass delete action
 */
class MassDelete extends Attribute
{

    /**
     * Mass action filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * Attribute Map type collection
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * MassDelete constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param LayoutFactory $resultLayoutFactory
     * @param Logger $logger
     * @param AttributeMapTypeRepositoryInterface $attributeMapTypeRepository
     * @param AttributeMapTypeInterfaceFactory $attributeMapTypeFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        LayoutFactory $resultLayoutFactory,
        Logger $logger,
        AttributeMapTypeRepositoryInterface $attributeMapTypeRepository,
        AttributeMapTypeInterfaceFactory $attributeMapTypeFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $collectionFactory,
        Filter $filter
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $coreRegistry,
            $resultLayoutFactory,
            $logger,
            $attributeMapTypeRepository,
            $attributeMapTypeFactory,
            $searchCriteriaBuilder
        );
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $itemsDeleted = 0;
        /** @var AttributeMapType $attributeMapType */
        foreach ($collection->getItems() as $attributeMapType) {
            $this->attributeMapTypeRepository->delete($attributeMapType);
            $itemsDeleted++;
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been deleted.', $itemsDeleted)
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('googlemerchant/*/index');
    }
}
