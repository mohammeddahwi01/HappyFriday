define([
    "jquery",
    "mage/backend/validation",
    "prototype"
], function (jQuery, Validation) {
    "use strict";

    var NekloScrollToTop = function (config) {

        var self = this;

        self.prototype.initialize(config);

        return self;
    }

    NekloScrollToTop.prototype = Class.create({
        initialize: function (config) {
            this.initConfig(config);
            this.initElements();
            this.initObservers();
        },

        initConfig: function (config) {
            this.config = config;
            this.threshold = this.config.threshold;
            this.scrollOptions = this.config.scrollOptions || {};
        },

        initElements: function () {
            this.arrow = $(this.config.arrowId) || null;
        },

        initObservers: function () {
            if (this.arrow) {
                Event.observe(this.arrow, 'click', this._onArrowClick.bind(this));
                Event.observe(window, 'scroll', this._onWindowScroll.bind(this));
            }
        },

        _onArrowClick: function () {
            new Effect.ScrollTo($$('body').first(), this.scrollOptions);
        },

        _onWindowScroll: function () {
            var scrollOffset = document.viewport.getScrollOffsets()[1];
            var scrollThreshold = document.viewport.getHeight() * this.threshold;
            if (scrollOffset > scrollThreshold) {
                this.showArrow();
            } else {
                this.hideArrow()
            }
        },

        showArrow: function () {
            if (this.arrow) {
                this.arrow.show();
            }
        },

        hideArrow: function () {
            if (this.arrow) {
                this.arrow.hide();
            }
        }
    });

    return NekloScrollToTop;
});