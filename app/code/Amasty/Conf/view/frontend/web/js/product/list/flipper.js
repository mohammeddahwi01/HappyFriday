define([
    "jquery",
    "jquery/ui",
    'uiClass'
], function ($, ui, Class) {

    return Class.extend({
        flipper_image_class: 'amconf-flipper-img',
        default_ducation: 400,
        defaults: {
            data: {}
        },

        initialize: function (config) {
            this.data = config.data;
            this._super();
            this.renderFlipperImages();
        },

        renderFlipperImages: function () {
            var self = this;

            $.each( this.data, function ( key, value ) {
                var selector = 'img[src="' + value.img_src  + '"]';
                $(selector).each( function  (i, img) {
                    self.generateFlipper($(img), value);
                });
            });
        },

        generateFlipper: function (img, imgConfig) {
            var self = this,
                parent = img.parent();

            $('<img/>', {
                id: 'flipper-image-' + imgConfig.product_id,
                class: self.flipper_image_class,
                src: imgConfig.flipper,
                alt: img.attr('alt')
            }).hide().appendTo(parent);

            $(parent).mouseenter( function () {
                $(this).find('img:not(.' + self.flipper_image_class + ')')
                    .stop()
                    .fadeOut(self.default_ducation);
                $(this).find('img.' + self.flipper_image_class)
                    .stop()
                    .fadeIn(self.default_ducation);

            } ).mouseleave( function () {
                $(this).find('img:not(.' + self.flipper_image_class + ')')
                    .stop()
                    .fadeIn(self.default_ducation);
                $(this).find('img.' + self.flipper_image_class)
                    .stop()
                    .fadeOut(self.default_ducation);
            } );
        }
    })
});
