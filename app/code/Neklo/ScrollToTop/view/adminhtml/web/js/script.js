define([
    "jquery",
    "prototype"
], function (jQuery) {
    "use strict";

    var NekloScrollToTop = function (config) {

        var self = this;

        self.prototype.initialize(config);

        return self;
    }

    Validation.addAllThese([
        ['validate-hex', 'Please enter a valid hex color code.', function (v) {
            if (v.length)
                return /^#(?:[0-9a-fA-F]{3}){1,2}$/.test(v);
            else
                return true;
        }]
    ]);

    NekloScrollToTop.prototype = Class.create({
        initialize: function (config) {
            this.initConfig(config);
            this.initElements();
            this.initObservers();
        },

        initConfig: function (config) {
            this.config = config;
            this.colorAttributeName = this.config.colorAttributeName;
        },

        initElements: function () {
            this.colorInput = $(this.config.colorInputId) || null;
            this.colorList = $$(this.config.colorListSelector) || null;
            this.previewContainer = $(this.config.previewContainerId) || null;
            this.hintContainer = $$(this.config.hintContainerSelector).last() || null;

            this._preparePreviewContainer();
        },

        initObservers: function () {
            if (this.colorList) {
                var me = this;
                this.colorList.each(
                    function (colorItem) {
                        colorItem.observe('click', me._onColorClick.bind(me, colorItem));
                    }
                );
            }

            if (this.colorInput) {
                this.colorInput.observe('input', this._processHexColor.bind(this));
            }
        },

        _onColorClick: function (colorItem) {
            if (this.colorInput) {
                this.colorInput.setValue('#' + colorItem.readAttribute(this.colorAttributeName));
            }
            this._updatePreviewColor(this.colorInput.getValue());
        },

        _updatePreviewColor: function (color) {
            if (this.previewContainer) {
                this.previewContainer.setStyle({'color': color});
            }
        },

        _preparePreviewContainer: function () {
            if (this.hintContainer && this.previewContainer) {
                this.hintContainer.appendChild(this.previewContainer);
                this.previewContainer.show();
            }
        },

        _processHexColor: function () {
            var colorValue = this.colorInput.getValue();
            colorValue = colorValue.replace(/[^0-9a-fA-F]/g, '').substr(0, 6);
            this.colorInput.setValue('#' + colorValue);
            if (/^#(?:[0-9a-fA-F]{3}){1,2}$/.test(this.colorInput.getValue())) {
                this._updatePreviewColor(this.colorInput.getValue());
            }
        }
    });

    return NekloScrollToTop;
});