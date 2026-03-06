<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Ui\Component\Listing\Column\ApiSyncType;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 * Api sync type option provider
 */
class Options implements OptionSourceInterface
{

    /**
     * Sync Types
     *
     * @var string[]
     */
    private array $syncType = ['0' => 'Single', '1' => 'Bulk'];

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->syncType as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return $options;
    }

    /**
     * Get Sync type array
     *
     * @return array
     */
    public function getSyncTypeArray()
    {
        return $this->syncType;
    }
}
