define([
        'jquery',
        'uiComponent',
        'ko',
        'BelSmol_VisualSearch/lib/select2/js/select2.min',
        'BelSmol_VisualSearch/lib/cropperjs/js/cropper.min',
        'mage/translate'
    ], function ($, Component, ko, select2, Cropper) {
        'use strict';

        return Component.extend({

            defaults: {
                template: 'BelSmol_VisualSearch/visual-search-pop-up'
            },

            CATEGORY_SELECTOR_ID: "#visual-search-categories",
            SEARCH_IMAGE_INPUT_SELECTOR_ID: "#search-image-input",
            SEARCH_IMAGE_SELECTOR_ID: "#search-image",
            SEARCH_FORM_ID: "#visual-search-form-request",

            ALLOWED_SEARCH_IMAGE_EXT: ['.jpg', '.jpeg', '.png'],

            VISUAL_SEARCH_RESULT_ACTION: '/visual_search/search/result',
            VISUAL_SEARCH_REQUEST_ACTION: '/visual_search/ajax/preparerequest',

            cropper: null,
            canShowCategorySelector: false,
            isAllCategoriesSearch: ko.observable(true),
            selectedCategoryIds: ko.observable([]),
            selectedImageData: ko.observable(null),
            selectedImage: ko.observable(null),

            /**
             * Init component, add event binding.
             * PopUp content handling here
             */
            initialize: function (config) {
                this._super();
                this.canShowCategorySelector = config.isCategorySelectorVisible;

                this.isAllCategoriesSearch.subscribe(function (isEnabled) {
                    if (isEnabled) {
                        this.resetCategorySelector();
                    }
                    this.isAllCategoriesSearch(isEnabled);
                }.bind(this));
            },

            /**
             * Reset the form values
             * when the pop-up is closed
             */
            reset: function () {
                this.destroyCropper();
                this.selectedCategoryIds([]);
                this.selectedImageData(null);
                this.selectedImage(null);
                this.isAllCategoriesSearch(true);
                $(this.SEARCH_IMAGE_INPUT_SELECTOR_ID).val('');
            },

            /**
             * Reset selection for categories
             */
            resetCategorySelector: function () {
                $(this.CATEGORY_SELECTOR_ID).val([]).trigger("change");
            },

            /**
             * This function is bound to the categoryIds selector
             * This function is triggered and saves selected ids to observable variable on select
             * @param element
             */
            selectCategories: function (element) {
                let selectedOptions = $(this.CATEGORY_SELECTOR_ID).val();
                this.selectedCategoryIds(selectedOptions);
            },

            /**
             * When the template is rendered
             * then bind js to html element here.
             * This function is triggered on html element
             */
            afterTemplateRender: function () {
                if (this.canShowCategorySelector) {
                    this.bindCategorySelectorJs();
                }
            },

            /**
             * Js to be bound to html elements
             */
            bindCategorySelectorJs: function () {
                $(document).ready(function() {
                    $(this.CATEGORY_SELECTOR_ID).select2({
                        width: '100%',
                        ajax: {
                            url: "/visual_search/ajax/searchcategorylist",
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    term: params.term, // search term
                                };
                            },
                            processResults: function (data, params) {
                                // parse the results into the format expected by Select2
                                // since we are using custom formatting functions we do not need to
                                // alter the remote JSON data, except to indicate that infinite
                                // scrolling can be used
                                params.page = params.page || 1;

                                return {
                                    results: data.results
                                };
                            },
                            cache: true
                        },
                        placeholder: $.mage.__('Start typing for search ...'),
                        minimumInputLength: 3
                    });
                }.bind(this));
            },

            /**
             * If search can be performed
             * then display search button
             */
            canShowSearchButton: function () {
                let isVisible = this.selectedImage();

                if (this.canShowCategorySelector) {
                    isVisible = isVisible && (this.isAllCategoriesSearch() || this.selectedCategoryIds().length > 0)
                }

                return isVisible;
            },

            /**
             * Open file input
             * when click "Upload Image" button
             */
            openFileInput: function () {
               $(this.SEARCH_IMAGE_INPUT_SELECTOR_ID).click();
            },

            /**
             * Track if an image was selected by a user
             * @param data
             * @param event
             */
            onFileSelect: function (data, event) {
                let fileInput = event.target;
                let file = fileInput.files[0];

                if (file) {
                    let fileExtension = file.name.split('.').pop().toLowerCase();

                    if (this.ALLOWED_SEARCH_IMAGE_EXT.includes('.' + fileExtension)) {
                        this.selectedImage(file);

                        // Read the file data and convert to base64
                        let reader = new FileReader();

                        reader.onload = function (e) {
                            this.selectedImageData(e.target.result);
                            this.destroyCropper();
                            this.createCropper();
                        }.bind(this);

                        reader.readAsDataURL(file);
                    } else {
                        fileInput.value = '';
                        let msg = $.mage.__('Only the following extensions are allowed:');
                        let allowedExtNames = this.ALLOWED_SEARCH_IMAGE_EXT.map(ext => ext.slice(1).toUpperCase()).join(', ');
                        alert(msg + " " + allowedExtNames);
                    }
                } else {
                    this.selectedImage(null);
                    this.selectedImageData(null);
                }
            },

            /**
             * Destroy cropper object
             */
            destroyCropper: function () {
                if (this.cropper) {
                    this.cropper.destroy();
                }
            },

            /**
             * Create cropper js object
             */
            createCropper: function () {
                const image = $(this.SEARCH_IMAGE_SELECTOR_ID);

                this.cropper = new Cropper(image[0], {
                    aspectRatio: 0,
                    minCropBoxWidth: 400,
                    minCropBoxHeight: 300,
                    rotatable: true,
                    crop(event) {
                        console.log(event.detail.x);
                        console.log(event.detail.y);
                        console.log(event.detail.width);
                        console.log(event.detail.height);
                        console.log(event.detail.rotate);
                        console.log(event.detail.scaleX);
                        console.log(event.detail.scaleY);
                    },
                });
            },

            /**
             * Make a search
             */
            search: function () {
                if (this.cropper) {
                    const form = $(this.SEARCH_FORM_ID);
                    const originalFormat = this.selectedImage().type;
                    const croppedData = this.cropper.getCroppedCanvas().toDataURL(originalFormat);

                    const formData = new FormData(form[0]);
                    formData.append('image', croppedData);
                    formData.append('categories', this.selectedCategoryIds());

                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method'),
                        data: formData,
                        processData: false,
                        contentType: false,
                        showLoader: true,
                        success: function(response) {
                            window.location.href = this.VISUAL_SEARCH_RESULT_ACTION + '?search=' + response.search_request_param;

                        }.bind(this),
                        error: function(jqXHR, textStatus, errorThrown) {
                            alert('VisualSearchRequestError: ' + errorThrown);
                        }
                    });
                } else {
                    alert($.mage.__('Please, fill in all the necessary data'));
                }
            }
        });
    }
);
