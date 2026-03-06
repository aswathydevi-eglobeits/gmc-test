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

use Egits\GoogleMerchantApi\Api\Data\FilterInterfaceFactory;
use Egits\GoogleMerchantApi\Api\FilterRepositoryInterface;
use Egits\GoogleMerchantApi\Logger\Logger;
use Egits\GoogleMerchantApi\Model\Rule;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Egits\GoogleMerchantApi\Model\FilterFactory;

/**
 * Class Filter
 * Filter base class
 */
abstract class Filter extends Action
{
    /**
     * @var bool|PageFactory
     */
    protected $resultPageFactory = false;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var FilterFactory
     */
    protected $filterFactory;

    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var FilterRepositoryInterface
     */
    protected $filterRepository;

    /**
     * Cont constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param LayoutFactory $resultLayoutFactory
     * @param Logger $logger
     * @param FilterInterfaceFactory $filterFactory
     * @param FilterRepositoryInterface $filterRepository
     * @param Rule $rule
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        LayoutFactory $resultLayoutFactory,
        Logger $logger,
        FilterInterfaceFactory $filterFactory,
        FilterRepositoryInterface $filterRepository,
        Rule $rule
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->logger = $logger;
        $this->filterFactory = $filterFactory;
        $this->filterRepository = $filterRepository;
        $this->rule = $rule;
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
