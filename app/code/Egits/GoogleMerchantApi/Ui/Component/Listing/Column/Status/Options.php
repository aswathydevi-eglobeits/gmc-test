<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Ui\Component\Listing\Column\Status;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 * Filter status option provider
 */
class Options implements OptionSourceInterface
{
    /**
     * @var array
     */
    public array $filterStatus = ['0' => 'Disabled', '1' => 'Enabled'];

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->filterStatus as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return $options;
    }

    /**
     * Get Filter Status Array
     *
     * @return array
     */
    public function getFilterStatusArray()
    {
        return $this->filterStatus;
    }
}
