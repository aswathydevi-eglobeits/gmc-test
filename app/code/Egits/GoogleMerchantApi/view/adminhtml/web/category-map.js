/*
 *
 *  Eglobe IT Solutions (P)Ltd.
 *
 *  @category    Egits
 *  @package    Egits_GoogleMerchantApi
 *  @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 *  @author      Eglobe Magento Team <info@eglobeits.com>
 */

define([
    'underscore',
    'jquery',
    'jquery/ui',
    'mage/template',
    "prototype",
    "mage/adminhtml/form"

], function (_,JQuery) {
    return mappingControl = {
        init: function (googleCategory) {
            var nullmapp = new Array();
            var self = this;
            var cat = googleCategory;
            $$('.mapping-input-field').each(
                function (item) {
                    JQuery(item).autocomplete(
                        {
                            source:cat.googleCategory,
                            minLength:3,
                            focus: function (event, ui) {
                                return false;
                            },
                            select: function (event,suggestion) {
                                JQuery(event.target).parent().find('.mapping-input').val(suggestion.item.data);
                            }}
                    )
                    JQuery("<li>").autocomplete().data("uiAutocomplete")._renderItem = function (ul, item) {
                        return JQuery("<li>")
                            .addClass('ui-menu-item')
                            .append(JQuery("<a>").text(item.label))
                            .appendTo(ul);
                    };
                    Event.observe(
                        item, 'change', function (e) {
                            var input = e.currentTarget;
                            self.applyPlaceholder(input);
                        }
                    )
                    JQuery(item).focusout(function(){
                        var value  = JQuery( this ).val();
                        if (value === '')
                        {
                            JQuery(this).prev().val("");
                        }
                    })
                }
            );
            $$('.category-mapping .toggle').each(
                function (item) {
                    Event.observe(
                        item, 'click', function (e) {
                            self.toggleCategories(e.currentTarget);
                        }
                    );
                }
            );
            self.closeAll();
            self.applyPlaceholders();
        },
        toggleCategories: function (item, type) {
            var self = this;
            if (type === undefined) {
                if (item.hasClassName('open')) {
                    type = 'show';
                } else {
                    type = 'hide';
                }
            }
            var input = item.parentElement.select('.mapping-input-field').first();
            var id = input.readAttribute('data-id');
            var childs = $$('[data-parent="' + id + '"]');
            childs.each(
                function (child) {
                    if (type == 'hide') {
                        var toggle = child.parentElement.parentElement.select('.toggle').first();
                        if (toggle.hasClassName('close')) {
                            self.toggleCategories(toggle, type);
                        }
                        child.parentElement.parentElement.hide();
                    }
                    else {
                        child.parentElement.parentElement.show();
                    }
                }
            );
            if (type == 'show') {
                item.addClassName('close').removeClassName('open');
                self.applyPlaceholder(input);
            } else {
                item.addClassName('open').removeClassName('close');
            }
        },
        applyPlaceholders: function () {
            var self = this;
        },
        applyPlaceholder: function (input) {
            var self = this;
            if (input.value === '') {
                var value = self.getParentValue(input);
                if (value !== '') {
                    input.writeAttribute('placeholder', value);
                } else {
                    input.removeAttribute('placeholder');
                }
            }
            var id = input.readAttribute('data-id');
            var childs = $$('[data-parent="' + id + '"]');
            childs.each(
                function (child) {
                    if (child.parentElement.parentElement.visible()) {
                        self.applyPlaceholder(child);
                    }
                }
            );
        },
        getParentValue: function (input) {
            var self = this;
            var parentId = input.readAttribute('data-parent');
            var parentInput = $$('[data-id="' + parentId + '"]').first();
            if (parentInput === undefined) {
                return '';
            }
            var val = parentInput.value;
            if (val) {
                return val;
            } else {
                return parentInput.readAttribute('placeholder');
            }
        },
        closeAll: function () {
            var self = this;
            var elements = $$('.category-mapping .mapping');
            elements.each(
                function (element) {
                    var level = element.readAttribute('data-level');
                    if (level > 0) {
                        element.hide();
                    }
                }
            );
        },
        getGoogleCategories: function () {
            return _.toArray(this.googleCategories);
        }
    };
}
);
