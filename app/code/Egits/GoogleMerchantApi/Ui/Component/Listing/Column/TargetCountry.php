<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Ui\Component\Listing\Column;

use Egits\GoogleMerchantApi\Ui\Component\Listing\Column\TargetCountry\Options;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class TargetCountry
 * Target country data modifier
 */
class TargetCountry extends Column
{
    /**
     * @var Options
     */
    protected $targetCountries;

    /**
     * TargetCountry constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Options $options
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Options $options,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->targetCountries = $options->toOptionArray();
    }

    /**
     * Prepare data source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item['orig_' . $this->getData('name')] = $item[$this->getData('name')];
                $item[$this->getData('name')] = $this->getCountryLabel($item);
            }
        }

        return $dataSource;
    }

    /**
     * Get country label
     *
     * @param  array  $item
     * @return mixed|string
     */
    private function getCountryLabel($item)
    {
        foreach ($this->targetCountries as $targetCountry) {
            if ($targetCountry['value'] == $item[$this->getData('name')]) {
                return $targetCountry['label'];
            }
        }
        return '';
    }
}
