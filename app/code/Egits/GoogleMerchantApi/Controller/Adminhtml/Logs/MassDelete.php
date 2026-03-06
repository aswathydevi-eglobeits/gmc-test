<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package    Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2018 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Controller\Adminhtml\Logs;

use Egits\GoogleMerchantApi\Api\LogRepositoryInterface;
use Egits\GoogleMerchantApi\Controller\Adminhtml\Logs;
use Egits\GoogleMerchantApi\Model\Logs as LogsModel;
use Egits\GoogleMerchantApi\Model\ResourceModel\Logs\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Ui\Component\MassAction\Filter as MassActionFilter;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 * Logs mass delete action
 */
class MassDelete extends Logs
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var MassActionFilter
     */
    protected $massActionFilter;

    /**
     * @var LogRepositoryInterface
     */
    protected $logRepository;

    /**
     * MassDelete constructor.
     *
     * @param Context $context
     * @param LogRepositoryInterface $logRepository
     * @param CollectionFactory $collectionFactory
     * @param MassActionFilter $massActionFilter
     */
    public function __construct(
        Context $context,
        LogRepositoryInterface $logRepository,
        CollectionFactory $collectionFactory,
        MassActionFilter $massActionFilter
    ) {
        parent::__construct($context);
        $this->massActionFilter =$massActionFilter;
        $this->collectionFactory = $collectionFactory;
        $this->logRepository = $logRepository;
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
        /** @var LogsModel $logs */
        foreach ($collection->getItems() as $logs) {
            $this->logRepository->delete($logs);
            $itemsDeleted++;
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been deleted.', $itemsDeleted)
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('googlemerchant/*/index');
    }
}
