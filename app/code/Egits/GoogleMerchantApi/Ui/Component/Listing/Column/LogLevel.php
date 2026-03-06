<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 *
 */

namespace Egits\GoogleMerchantApi\Ui\Component\Listing\Column;

use Egits\GoogleMerchantApi\Model\ResourceModel\LogsRepository;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class LogLevel
 * Log level data modifier grid
 */
class LogLevel extends Column
{
    /**
     * @var LogsRepository
     */
    private LogsRepository $logsRepository;

    /**
     * LogLevel constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param LogsRepository $logsRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        LogsRepository $logsRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->logsRepository = $logsRepository;
    }

    /**
     * Prepare Data for Grid column
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item['orig_' . $this->getData('name')] = $item[$this->getData('name')];
                $item[$this->getData('name')] = ucfirst(
                    $this->logsRepository->getLevelName($item[$this->getData('name')])
                );
            }
        }

        return $dataSource;
    }
}
