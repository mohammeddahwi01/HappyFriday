define([
    'jquery',
    'jquery/ui',
    'amnotification',
    'Amasty_Xnotif/js/category_subscribe'
], function ($, ui, amnotification) {

    $.widget('mage.amxnotifCategoryConfigurable', {
        options: {
            selectors: {
                alertBlock: '.amxnotif-container, .alert.stock.link-stock-alert'
            }
        },

        _create: function() {
            this._initialize();
        },

        _initialize: function() {
            var self = this;
            $.ajax({
                url: this.options.url,
                data: 'product=' + this.options.product,
                type: 'post',
                dataType: 'json',
                success: function(response) {
                    if(!$.isEmptyObject(response)) {
                        $.mage.amnotification({
                            'xnotif': response,
                            'is_category' : true,
                            'element' : self.element
                            });
                    }
                }
            });
        }
    });

    return $.mage.amxnotifCategoryConfigurable;
});
