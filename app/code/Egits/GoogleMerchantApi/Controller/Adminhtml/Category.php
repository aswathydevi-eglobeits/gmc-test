<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Controller\Adminhtml;

use Egits\GoogleMerchantApi\Api\CategoryMappingRepositoryInterfaceFactory;
use Egits\GoogleMerchantApi\Api\Data\CategoryMappingInterfaceFactory;
use Egits\GoogleMerchantApi\Logger\Logger;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Category
 * Category mapping base class
 */
abstract class Category extends Action
{
    /**
     * @var bool|PageFactory
     */
    protected $resultPageFactory = false;

    /**
     * @var CategoryMappingInterfaceFactory
     */
    protected $mappingFactory;

    /**
     * @var CategoryMappingRepositoryInterfaceFactory
     */
    protected $mappingRepositoryFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Cont constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Logger $logger
     * @param CategoryMappingInterfaceFactory $mappingFactory
     * @param CategoryMappingRepositoryInterfaceFactory $mappingRepositoryFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Logger $logger,
        CategoryMappingInterfaceFactory $mappingFactory,
        CategoryMappingRepositoryInterfaceFactory $mappingRepositoryFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->mappingFactory = $mappingFactory;
        $this->mappingRepositoryFactory = $mappingRepositoryFactory;
        $this->logger = $logger;
    }

    /**
     * Is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Egits_GoogleMerchantApi::feed');
    }
}
