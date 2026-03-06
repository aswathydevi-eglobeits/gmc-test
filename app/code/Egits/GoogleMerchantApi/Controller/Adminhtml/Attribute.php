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

use Egits\GoogleMerchantApi\Api\AttributeMapTypeRepositoryInterface;
use Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterface;
use Egits\GoogleMerchantApi\Api\Data\AttributeMapTypeInterfaceFactory;
use Egits\GoogleMerchantApi\Logger\Logger;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Attribute
 * Attribute Map base class
 */
abstract class Attribute extends Action
{
    /**
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory = null;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var AttributeMapTypeRepositoryInterface
     */
    protected $attributeMapTypeRepository;

    /**
     * @var bool|PageFactory
     */
    protected $resultPageFactory = false;

    /**
     * @var AttributeMapTypeInterface
     */
    protected $attributeMapTypeFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Cont constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param LayoutFactory $resultLayoutFactory
     * @param Logger $logger
     * @param AttributeMapTypeRepositoryInterface $attributeMapTypeRepository
     * @param AttributeMapTypeInterfaceFactory $attributeMapTypeFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        LayoutFactory $resultLayoutFactory,
        Logger $logger,
        AttributeMapTypeRepositoryInterface $attributeMapTypeRepository,
        AttributeMapTypeInterfaceFactory $attributeMapTypeFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->attributeMapTypeRepository = $attributeMapTypeRepository;
        $this->coreRegistry = $coreRegistry;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->logger = $logger;
        $this->attributeMapTypeFactory = $attributeMapTypeFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
