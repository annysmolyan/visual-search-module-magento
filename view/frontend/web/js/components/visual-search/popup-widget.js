define(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
        'ko',
        'uiRegistry',
        'domReady!'
    ],
    function ($, modal, ko, uiRegistry) {
        "use strict";

        $.widget('BelSmol.VisualSearch', {

            POPUP_COMPONENT_NAME: 'visualSearchPopup', // component's name in visual_search_popup.phtml

            visualSearchForm: null,

            options: {
                modalButton: '.visual-search-popup-open',
                modalForm: '.visual-search-popup',
                modalOption: {
                    type: 'popup',
                    responsive: true,
                    clickableOverlay: false,
                    title: $.mage.__('Search by image'),
                    buttons: [],
                    innerScroll: true,
                    focus: 'none',
                }
            },

            /**
             * Create popup widget
             * Set trigger actions here
             * @private
             */
            _create: function () {
                $(this.options.modalButton).off().click(function(event){
                    this.setVisualSearchPopupComponent();
                    this.bindVisualSearchPopupEvents();
                    this.triggerPopup();
                }.bind(this));
            },

            /**
             * Need to use registry here
             * because when the component is used in the define section
             * it creates a new instance instead of getting existing one
             */
            setVisualSearchPopupComponent: function () {
                this.visualSearchForm = uiRegistry.get(this.POPUP_COMPONENT_NAME);
            },

            /**
             * bind event function here.
             * e.g. onClose and so on
             */
            bindVisualSearchPopupEvents: function () {
                this.options.modalOption.closed = function (e) {
                    this.visualSearchForm.reset();
                }.bind(this);
            },

            /**
             * Trigger modal form
             */
            triggerPopup: function () {
                $(this.options.modalForm).modal(this.options.modalOption);
                $(this.options.modalForm).trigger('openModal');
            }
        });

        return $.BelSmol.VisualSearch;
    }
);
