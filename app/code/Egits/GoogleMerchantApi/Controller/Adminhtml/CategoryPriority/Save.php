<?php
namespace Egits\GoogleMerchantApi\Controller\Adminhtml\CategoryPriority;

use Egits\GoogleMerchantApi\Controller\Adminhtml\CategoryPriority;
use Magento\Backend\App\Action\Context;
use Egits\GoogleMerchantApi\Api\CategoryPriorityRepositoryInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Save
 *
 * Handles saving of category priority and type settings from the admin panel.
 */
class Save extends CategoryPriority
{
    /**
     * @var CategoryPriorityRepositoryInterface
     */
    protected CategoryPriorityRepositoryInterface $categoryPriorityRepository;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param CategoryPriorityRepositoryInterface $categoryPriorityRepository
     */
    public function __construct(
        Context $context,
        CategoryPriorityRepositoryInterface $categoryPriorityRepository
    ) {
        parent::__construct($context);
        $this->categoryPriorityRepository = $categoryPriorityRepository;
    }

    /**
     * Save action to process posted category priority and type values.
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        if (!$data || !isset($data['categoryId'])) {
            $this->messageManager->addErrorMessage(__('No data received.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        try {
            $categoryIds = $data['categoryId'] ?? [];
            $priorities = $data['priority'] ?? [];
            $types = $data['type'] ?? [];

            $bulkData = [];

            foreach ($categoryIds as $categoryId) {
                $priorityValue = isset($priorities[$categoryId]) ? trim($priorities[$categoryId]) : null;
                $typeValue = isset($types[$categoryId]) ? trim($types[$categoryId]) : null;

                if ($priorityValue === '' && $typeValue === '') {
                    continue;
                }

                // Validate priority if it is provided (0 is acceptable)
                if ($priorityValue !== '') {
                    // Allow only numeric values that are integers (including "0")
                    if (!is_numeric($priorityValue) || (int)$priorityValue < 0) {
                        $this->messageManager->addErrorMessage(
                            __('Invalid priority value for category ID %1.
                            Only non-negative integers are allowed.', $categoryId)
                        );
                        return $this->resultRedirectFactory->create()->setPath('*/*/');
                    }
                }

                $bulkData[] = [
                    'category_id' => $categoryId,
                    'priority' => ($priorityValue === '') ? null : $priorityValue,
                    'type' => ($typeValue === '') ? null : $typeValue
                ];
            }

            if (!empty($bulkData)) {
                $this->categoryPriorityRepository->saveMultiple($bulkData);
                $this->messageManager->addSuccessMessage(__('Category priorities have been saved.'));
            } else {
                $this->messageManager->addNoticeMessage(__('No valid category priorities to save.'));
            }

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong: %1', $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
