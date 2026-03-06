<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category  Egits
 * @package   Egits_GoogleMerchantApi
 * @copyright Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author    Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Block\Adminhtml\Filter\Tabs;

use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Egits\GoogleMerchantApi\Model\Rule;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Rule\Block\Conditions as AbstractConditions;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Backend\Block\Widget\Form\Generic;

/**
 * Class Conditions
 * Filter tab  class for ui component
 */
class Conditions extends Generic
{
    public const FILTER_FORM_NAME = 'google_merchant_filter_form';
    public const FORM_FIELD_SET = 'rule_conditions_fieldset';

    /**
     * Core registry
     *
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $conditions;

    /**
     * @param Context            $context
     * @param Registry           $registry
     * @param FormFactory        $formFactory
     * @param AbstractConditions $conditions
     * @param Fieldset           $rendererFieldset
     * @param array              $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        AbstractConditions $conditions,
        Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->rendererFieldset = $rendererFieldset;
        $this->conditions = $conditions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /**
         * @var \Egits\GoogleMerchantApi\Model\Rule $model
         */
        $model = $this->_coreRegistry->registry(\Egits\GoogleMerchantApi\Model\Rule::CURRENT_RULE_DATA_KEY);
        $form = $this->addTabToForm($model);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Handles addition of conditions tab to supplied form.
     *
     * @param Rule   $model
     * @param string $fieldsetId
     * @param string $formName
     *
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addTabToForm(
        $model,
        $fieldsetId = 'conditions_fieldset',
        $formName = 'google_merchant_filter_form'
    ) {
        $conditionsFieldSetId = $model->getConditionsFieldSetId($formName);
        $newChildUrl = $this->getUrl(
            'googlemerchant/filter/newConditionHtml/form/' . $conditionsFieldSetId,
            ['form_namespace' => $formName]
        );
        /**
         * @var \Magento\Framework\Data\Form $form
        */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $renderer = $this->getLayout()->createBlock(Fieldset::class);
        $renderer->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $newChildUrl
        )->setFieldSetId(
            $conditionsFieldSetId
        );
        $fieldset = $form->addFieldset(
            $fieldsetId,
            [
                'legend' => __(
                    'Conditions (don\'t add conditions if need export all products).'
                )
            ]
        )->setRenderer(
            $renderer
        );
        $fieldset->addField(
            'conditions',
            'text',
            [
                'name'           => 'conditions',
                'label'          => __('Conditions'),
                'title'          => __('Conditions'),
                'required'       => true,
                'data-form-part' => $formName
            ]
        )->setRule(
            $model
        )->setRenderer(
            $this->conditions
        );
        $form->setValues($model->getData());
        $this->_setConditionFormName($model->getConditions(), $formName);
        return $form;
    }
    /**
     * Handles addition of form name to condition and its conditions.
     *
     * @param \Magento\Rule\Model\Condition\AbstractCondition $conditions
     * @param string                                          $formName
     *
     * @return void
     */
    private function _setConditionFormName(\Magento\Rule\Model\Condition\AbstractCondition $conditions, $formName)
    {
        $conditions->setFormName($formName);
        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->_setConditionFormName($condition, $formName);
            }
        }
    }
}
