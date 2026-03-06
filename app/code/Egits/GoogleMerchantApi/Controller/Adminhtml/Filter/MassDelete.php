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

use Egits\GoogleMerchantApi\Api\Data\FilterInterfaceFactory;
use Egits\GoogleMerchantApi\Api\FilterRepositoryInterface;
use Egits\GoogleMerchantApi\Controller\Adminhtml\Filter;
use Egits\GoogleMerchantApi\Logger\Logger;
use Egits\GoogleMerchantApi\Model\Filter as FilterModel;
use Egits\GoogleMerchantApi\Model\Rule;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Egits\GoogleMerchantApi\Model\ResourceModel\Filter\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter as MassActionFilter;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 * Mass delete filter action
 */
class MassDelete extends Filter
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var MassActionFilter
     */
    private $massActionFilter;

    /**
     * MassDelete constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param LayoutFactory $resultLayoutFactory
     * @param Logger $logger
     * @param FilterInterfaceFactory $filterFactory
     * @param FilterRepositoryInterface $filterRepository
     * @param Rule $rule
     * @param CollectionFactory $collectionFactory
     * @param MassActionFilter $massActionFilter
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        LayoutFactory $resultLayoutFactory,
        Logger $logger,
        FilterInterfaceFactory $filterFactory,
        FilterRepositoryInterface $filterRepository,
        Rule $rule,
        CollectionFactory $collectionFactory,
        MassActionFilter $massActionFilter
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $coreRegistry,
            $resultLayoutFactory,
            $logger,
            $filterFactory,
            $filterRepository,
            $rule
        );
        $this->collectionFactory = $collectionFactory;
        $this->massActionFilter = $massActionFilter;
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
        $collection = $this->massActionFilter->getCollection($this->collectionFactory->create());
        $itemsDeleted = 0;
        /** @var FilterModel $filter */
        foreach ($collection->getItems() as $filter) {
            $this->filterRepository->delete($filter);
            $itemsDeleted++;
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been deleted.', $itemsDeleted)
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('googlemerchant/*/index');
    }
}
