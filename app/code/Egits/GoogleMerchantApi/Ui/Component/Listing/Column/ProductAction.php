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

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class Action
 * Column action for grid
 */
class ProductAction extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var ProductMetadataInterface
     */
    private ProductMetadataInterface $productMetadata;

    /**
     * Action constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param ProductMetadataInterface $productMetadata
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        ProductMetadataInterface $productMetadata,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $isEnterprise = ("Community" != $this->productMetadata->getEdition());
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('config/indexField')])) {
                    $viewConfigUrlPath = $this->getData('config/viewUrlPath') ?: '#';
                    $urlConfigEntityParamName = $this->getData('config/urlEntityParamName')
                        ?: $this->getData(
                            'config/indexField'
                        );
                    $idField = $isEnterprise ? 'product_entity_id' : $this->getData('config/indexField');
                    $item[$this->getData('name')] = [
                        'view' => [
                            'href'  => $this->urlBuilder->getUrl(
                                $viewConfigUrlPath,
                                [
                                    $urlConfigEntityParamName => $item[
                                        $idField
                                    ]
                                ]
                            ),
                            'label' => __('View')
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
