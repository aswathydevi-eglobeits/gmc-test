<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Ui\Component\Listing\Column\LogLevel;

use Egits\GoogleMerchantApi\Model\ResourceModel\LogsRepository;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 * Log level option provider
 */
class Options implements OptionSourceInterface
{
    /**
     * @var LogsRepository
     */
    private LogsRepository $logsRepository;

    /**
     * Options constructor.
     * @param LogsRepository $logsRepository
     */
    public function __construct(
        LogsRepository $logsRepository
    ) {
        $this->logsRepository = $logsRepository;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $levelName = $this->logsRepository->getLevelNames();
        $options = [];
        foreach ($levelName as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return $options;
    }
}
