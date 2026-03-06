<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package    Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model;

/**
 * Class Rule
 * Rule model
 */
class Rule extends \Magento\CatalogRule\Model\Rule
{
    /**
     * current rule key
     */
    public const CURRENT_RULE_DATA_KEY = 'current_googlemerchant_rule';

    /**
     * @var int
     */
    protected $storeId;

    /**
     * Get feed matching products
     *
     * @return array|null
     */
    public function getFeedMatchingProductIds()
    {
        if ($this->_productIds === null) {
            $this->_productIds = [];
            $this->setCollectedAttributes([]);

            /** @var $magentoProductCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
            $magentoProductCollection = $this->_productCollectionFactory->create();
            $magentoProductCollection->addStoreFilter($this->getStoreId());
            if ($this->_productsFilter) {
                $magentoProductCollection->addIdFilter($this->_productsFilter);
            }

            $this->getConditions()->collectValidatedAttributes($magentoProductCollection);
            $this->_registry->register('filter_matching_product_ids', $magentoProductCollection->getAllIds());
            $this->_resourceIterator->walk(
                $magentoProductCollection->getSelect(),
                [[$this, 'callbackValidateProduct']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product' => $this->_productFactory->create()
                ]
            );
        }

        return $this->_productIds;
    }

    /**
     * Call back validate product
     *
     * @param array $args
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);
        $results = [];
        $product->setStoreId($this->getStoreId());
        $validate = $this->getConditions()->validate($product);

        if ($validate) {
            $results[$this->getStoreId()] = $validate;
            $this->_productIds[$product->getId()] = $results;
        }
    }

    /**
     * Get store id
     *
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * Set Store Id
     *
     * @param   int   $storeId
     * @return  $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }
}
