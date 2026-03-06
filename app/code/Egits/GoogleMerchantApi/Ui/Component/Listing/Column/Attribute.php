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

use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Attribute
 * Attribute data column modifier
 */
class Attribute extends Column
{
    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * Attribute constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param AttributeFactory $attributeFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        AttributeFactory $attributeFactory,
        array $components = [],
        array $data = []
    ) {
        $this->attributeFactory = $attributeFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare data for column
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item['orig_' . $this->getData('name')] = $item[$this->getData('name')];
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * Prepare items
     *
     * @param array $item
     * @return mixed
     */
    protected function prepareItem($item)
    {
        $attributeLabel = '';
        $model = $this->attributeFactory->create();
        $model->load($item[$this->getData('name')]);
        if ($model->getId()) {
            $attributeLabel = $model->getFrontendLabel();
        }

        return $attributeLabel;
    }
}
