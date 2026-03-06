<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Helper;

use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Egits\GoogleMerchantApi\Api\LogRepositoryInterface;
use Egits\GoogleMerchantApi\Api\LogRepositoryInterfaceFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Egits\GoogleMerchantApi\Logger\Logger;

/**
 * Class GoogleHelper
 * Helper class google injected in all classes
 */
class GoogleHelper extends Data
{
    /**
     * Base attributes are required and auto-calculated.
     * They will be added to entry even without mapping.
     *
     * @var array
     */
    protected $baseAttributes
        = [
            'id',
            'title',
            'link',
            'content',
            'price',
            'image_link',
            'condition',
            'target_country',
            'content_language',
            'destinations',
            'availability',
            'google_product_category',
            'product_type',
            'product_uom',
            'is_bundle'
        ];

    /**
     * Groups are dependencies between attributes
     *
     * @var array
     */
    protected $attributeGroups
        = [
            'price'           => [
                'sale_price',
                'tax',
                'sale_price_effective_date',
                'sale_price_effective_date_from',
                'sale_price_effective_date_to'
            ],
            'shipping_weight' => ['weight'],
            'title'           => ['name'],
            'content'         => ['description']

        ];

    /**
     * @var GoogleConfig
     */
    protected $config;

    /**
     * @var LogRepositoryInterfaceFactory
     */
    protected $logRepositoryFactory;

    /**
     * @var LogRepositoryInterface
     */
    protected $apiLogger;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * GoogleHelper constructor.
     *
     * @param Context $context
     * @param GoogleConfig $config
     * @param LogRepositoryInterfaceFactory $logRepositoryFactory
     * @param DateTimeFactory $dateTimeFactory
     * @param TimezoneInterface $timezone
     * @param Logger $logger
     * @param StoreManagerInterface $storeManager
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        GoogleConfig $config,
        LogRepositoryInterfaceFactory $logRepositoryFactory,
        DateTimeFactory $dateTimeFactory,
        TimezoneInterface $timezone,
        Logger $logger,
        StoreManagerInterface $storeManager,
        Escaper $escaper
    ) {
        parent::__construct($context, $dateTimeFactory, $timezone);
        $this->config = $config;
        $this->logRepositoryFactory = $logRepositoryFactory;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->escaper = $escaper;
    }

    /**
     * Get Google Config object
     *
     * @return GoogleConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get Api logger object
     *
     * @return LogRepositoryInterface
     */
    public function getApiLogger()
    {
        if (!$this->apiLogger) {
            $this->apiLogger = $this->logRepositoryFactory->create();
        }

        return $this->apiLogger;
    }

    /**
     * Get File logger object
     *
     * @return Logger
     */
    public function getFileLogger()
    {
        return $this->logger;
    }

    /**
     * Get group attributes;
     *
     * @return array
     */
    public function getAttributeGroupsFlat()
    {
        $groupFlat = [];
        foreach ($this->attributeGroups as $group => $subAttributes) {
            foreach ($subAttributes as $subAttribute) {
                $groupFlat[$subAttribute] = $group;
            }
        }

        return $groupFlat;
    }

    /**
     * Get array of base attribute names
     *
     * @return array
     */
    public function getBaseAttributes()
    {
        return $this->baseAttributes;
    }

    /**
     *  Get current target country
     *
     * @return string
     */
    public function getCurrentTargetCountry()
    {
        return '';
    }

    /**
     * Get current date and time
     *
     * @return string
     */
    public function getCurrentDateAndTime()
    {
        return $this->dateTimeFactory->create()->gmtDate();
    }

    /**
     * Write Debug log to file
     *
     * @param  Exception|string $log
     * @param  int              $storeId
     */
    public function writeDebugLogFile($log, $storeId = null)
    {
        if ($this->getConfig()->isOnDebugMode($storeId)) {
            $this->getFileLogger()->debug($log);
        }
    }

    /**
     * Get default store View
     *
     * @return StoreInterface|null
     */
    public function getDefaultStoreView()
    {
        return $this->storeManager->getDefaultStoreView();
    }

    /**
     * Get Current Store
     *
     * @param int|null $storeId
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getCurrentStore($storeId = null)
    {
        if (!$storeId) {
            $storeId = (int)$this->_getRequest()->getParam('store', 0);
        }
        return $this->storeManager->getStore($storeId);
    }

    /**
     * Get Escaper
     *
     * @return Escaper
     */
    public function getEscaper()
    {
        return $this->escaper;
    }

    /**
     * Get Website
     *
     * @param int|null $websiteId
     * @return \Magento\Store\Api\Data\WebsiteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWebsite($websiteId = null)
    {
        return $this->storeManager->getWebsite($websiteId);
    }
}
