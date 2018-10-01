define(
    [
        'jquery',
        'Magento_Catalog/js/price-utils',
        'Magento_Catalog/js/price-box',
        'magento-swatch.renderer'
    ],
function ($ ,utils) {
    'use strict';

    $.widget('amasty_conf.SwatchRenderer', $.mage.SwatchRenderer, {
        selectors: {
            'qty-block' : '.field.qty'
        },
        defaultContents: [],

        _init: function () {
            if (_.isEmpty(this.options.jsonConfig.images)) {
                this.options.useAjax = true;
                // creates debounced variant of _LoadProductMedia()
                // to use it in events handlers instead of _LoadProductMedia()
                this._debouncedLoadProductMedia = _.debounce(this._LoadProductMedia.bind(this), 500);
            }
            if (this.options.jsonConfig !== '' && this.options.jsonSwatchConfig !== '') {
                this.options.jsonConfig.mappedAttributes = _.clone(this.options.jsonConfig.attributes);
                this._sortAttributes();
                this._RenderControls();
                this._saveLastRowContent();

                var isProductViewExist = $('body.catalog-product-view').size() > 0;
                if (isProductViewExist) {
                    this._RenderPricesForControls();
                    this._RenderProductMatrix();
                }
                this._addOutOfStockLabels();

                //Compatibility with 2.2.0
                if (typeof this._setPreSelectedGallery === "function") {
                    this._setPreSelectedGallery();
                }

                $(this.element).trigger('swatch.initialized');
            } else {
                console.log('SwatchRenderer: No input data received');
            }
        },

        isMobileAndTablet: function () {
            return /Android|webOS|iPhone|iPod|BlackBerry|BB|PlayBook|IEMobile|Windows Phone|Kindle|Silk|Opera Mini/i.test(navigator.userAgent);
        },

        _EventListener: function () {
            this.amasty_conf_config = window.amasty_conf_config;
            var $widget = this;

            $widget.element.on('click', '.' + this.options.classes.optionClass, function () {
                return $widget._AmOnClick($(this), $widget);
            });

            if(this.amasty_conf_config && this.amasty_conf_config.share.enable == '1') {
                this._createShareBlock();
            }

            $widget.element.on('change', '.' + this.options.classes.selectClass, function () {
                return $widget._AmOnChange($(this), $widget);
            });

            $widget.element.on('click', '.' + this.options.classes.moreButton, function (e) {
                e.preventDefault();

                return $widget._OnMoreClick($(this));
            });

            if (!this.isMobileAndTablet() && parseInt($widget.options.jsonConfig.change_mouseover)) {
                $widget.element.on('mouseover', '.' + this.options.classes.optionClass, function () {
                    return $widget.onMouseOver($(this), $widget);
                });
                $widget.element.on('mouseleave', '.' + this.options.classes.optionClass, function () {
                    return $widget.onMouseLeave($(this), $widget);
                });
            }
        },

        _createShareBlock: function () {
            var parent = $('.product-social-links');
            var link = jQuery('<a/>', {
                class: 'action share amasty_conf mailto friend',
                title: this.amasty_conf_config.share.title,
                text: this.amasty_conf_config.share.title
            }).appendTo(parent);

            link.on('click', function () {
                $('.amconf-share-container').toggle();
            });

            var container = jQuery('<div/>', {
                class: 'amconf-share-container'
            }).appendTo(parent);

            var input = jQuery('<input/>', {
                class: 'amconf-share-input',
                type: 'text'
            }).appendTo(container);

            var button = jQuery('<button/>', {
                class: 'amconf-share-buton action primary',
                html: '<span>' + this.amasty_conf_config.share.link + '</span>'
            }).appendTo(container);

            button.on('click', function () {
                $('.amconf-share-input').select();
                var status = document.execCommand('copy');
                if(!status){
                    console.error("Can't copy text");
                }
            });
        },

        _AmOnClick: function ($this, $widget) {
            $widget._OnClick($this, $widget);

            if(this.amasty_conf_config && this.amasty_conf_config.share.enable == '1') {
                $widget._addHashToUrl($this, $widget);
            }

            $widget._reloadProductInformation($this, $widget);

            var isProductViewExist = $('body.catalog-product-view').size() > 0;
            if (isProductViewExist) {
                $widget._RenderPricesForControls();
                $widget._RenderProductMatrix();
            }
            $widget._addOutOfStockLabels();
        },

        _AmOnChange: function ($this, $widget) {
            $widget._OnChange($this, $widget);
            $widget._reloadProductInformation($this, $widget);

            var isProductViewExist = $('body.catalog-product-view').size() > 0;
            if (isProductViewExist) {
                $widget._RenderPricesForControls();
                $widget._RenderProductMatrix();
            }
        },

        _addHashToUrl: function ($this, $widget) {
            var addParamsToHash = 1,
                isProductViewExist = $('body.catalog-product-view').size() > 0,
                attributeCode = $this.parents('.' + this.options.classes.attributeClass).attr('attribute-code'),
                optionId = $this.attr('option-id');

            if (addParamsToHash && isProductViewExist){
                var hash = window.location.hash;
                if (hash.indexOf(attributeCode + '=') >= 0) {
                    var replaceText = new RegExp(attributeCode + '=' + '.*');
                    if(optionId) {
                        hash = hash.replace(replaceText, attributeCode + '=' + optionId);
                    }
                    else{
                        hash = hash.replace(replaceText, "");
                    }
                }
                else {
                    if (hash.indexOf('#') >= 0) {
                        hash = hash + '&' + attributeCode + '=' + optionId;
                    }
                    else {
                        hash = hash + '#' + attributeCode + '=' + optionId;
                    }
                }

                window.location.replace(window.location.href.split('#')[0] + hash);
                $('.amconf-share-input').prop('value', window.location);
            }

            if(!isProductViewExist) {
                var parent = $widget.element.parents('.item');
                if (parent.length > 0) {
                    var productLinks = parent.find('a:not([href^="#"]):not([data-post*="action"]):not([href*="#reviews"])');
                    $.each(productLinks, function(i, link ) {
                        link = $(link);
                        var href = link.prop('href');
                        if (href.indexOf(attributeCode + '=') >= 0) {
                            var replaceText = new RegExp(attributeCode + '=' + '\\d+');
                            href = href.replace(replaceText, attributeCode + '=' + optionId)
                            link.prop('href', href);
                        }
                        else {
                            if (href.indexOf('#') >= 0) {
                                link.prop('href', href + '&' + attributeCode + '=' + optionId);
                            }
                            else {
                                link.prop('href', href + '#' + attributeCode + '=' + optionId);
                            }
                        }
                    });
                }
            }
        },

        _reloadProductInformation: function ($this, $widget) {
            var $widget = this,
                options = _.object(_.keys($widget.optionsMap), {});

            if(!$widget.options.jsonConfig.product_information) {
                return;
            }

            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var attributeId = $(this).attr('attribute-id');
                options[attributeId] = $(this).attr('option-selected');
            });

            var result = $widget.options.jsonConfig.product_information[_.findKey($widget.options.jsonConfig.index, options)],
                defaultResult = $widget.options.jsonConfig.product_information['default'];

            if(result) {
                for (var component in defaultResult) {
                    if (result[component] == null) {
                        result[component] = defaultResult[component];
                    }
                }
                for(var component in result) {
                    this._updateSimpleData(result[component]);
                }
            }
            else {
                for(var component in defaultResult) {
                    this._updateSimpleData(defaultResult[component]);
                }
            }
        },

        _updateSimpleData: function (data) {
            if (data && data.selector && data.value) {
                $(data.selector).html(data.value);
            }
        },

        _RenderSwatchSelect: function (config, chooseText) {
            var $widget = this,
                html,
                attrConfig = config;

            if (this.options.jsonSwatchConfig.hasOwnProperty(attrConfig.id)) {
                return '';
            }

            html =
                '<select class="' + this.options.classes.selectClass + ' ' + attrConfig.code + '">' +
                '<option value="0" option-id="0">' + chooseText + '</option>';

            $widget.defaultContents[attrConfig.id] = [];
            $.each(attrConfig.options, function () {
                var label = this.label,
                    attr = ' value="' + this.id + '" option-id="' + this.id + '"';

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' option-empty="true"';
                } else {
                    var showPrice = parseInt($widget.options.jsonConfig.show_dropdown_prices);
                    if (typeof $widget.defaultContents[attrConfig.id][this.id] === 'undefined') {
                        $widget.defaultContents[attrConfig.id][this.id] = [];
                    }
                    $widget.defaultContents[attrConfig.id][this.id]['label'] = label;
                    if (showPrice > 0 && this.products.length == 1) { // setting show price is enabled
                        var price = $widget.options.jsonConfig.optionPrices[this.products[0]].finalPrice.amount,
                            priceBoxSelector = '[data-role=priceBox][data-product-id="' + $widget.options.jsonConfig.productId + '"]',
                            priceConfig = $(priceBoxSelector).priceBox('option').priceConfig,
                            parentPrice = priceConfig.prices.finalPrice.amount,
                            priceFormat = (priceConfig && priceConfig.priceFormat) || {};
                        if (showPrice === 1) { // show price difference
                            price = price - parentPrice;
                        }
                        if (price) {
                            var formatted = utils.formatPrice(price, priceFormat);
                            if (formatted.indexOf('-') === -1 && showPrice === 1) {
                                formatted = '+' + formatted;
                            }
                            label += '  ' + formatted;
                            $widget.defaultContents[attrConfig.id][this.id]['label_price'] = label;
                        }
                    }
                }

                html += '<option ' + attr + '>' + label + '</option>';
            });

            html += '</select>';

            return html;
        },

        onMouseOver: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass);
            if ($this.attr('option-id') > 0) {
                $parent.attr('option-selected-old', $parent.attr('option-selected'));
                $parent.attr('option-selected', $this.attr('option-id'));
                $widget._LoadProductMedia();
            }

        },

        onMouseLeave: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                selectedOption = $parent.find('.' + $widget.options.classes.optionClass + '.selected');
            if (selectedOption.length > 0) {
                $parent.attr('option-selected', selectedOption.attr('option-id'));
                $widget._LoadProductMedia();
            } else {
                $parent.attr('option-selected', $parent.attr('option-selected-old'));
            }
        },

        /**
         * Emulate mouse click on all swatches that should be selected
         * @private
         */
        _EmulateSelected: function () {
            var gallery = $('[data-gallery-role=gallery-placeholder]', '.column.main');
            if (this.amasty_conf_config) {
                if ((gallery.data('gallery') || gallery.data('amasty_gallery') || this.inProductList)
                    && !this.options.jsonConfig.preselected
                ) {
                    var selectedAttributes = this.getPreselectedAttributes();
                    $.each(selectedAttributes, $.proxy(function (attributeCode, optionId) {
                        var select = this.element.find('.' + this.options.classes.attributeClass +
                            '[attribute-code="' + attributeCode + '"] .swatch-select');
                        if (select.length > 0) {
                            select.val(optionId);
                            select.trigger('change');
                        } else {
                            this.element.find('.' + this.options.classes.attributeClass +
                                '[attribute-code="' + attributeCode + '"] [option-id="' + optionId + '"]').trigger('click');
                        }
                    }, this));
                    this.options.jsonConfig.preselected = true;
                } else {
                    if (!this.amasty_conf_config.bindGallery) {
                        gallery.on('gallery:loaded', this._onGalleryLoadedFRunEmulation.bind(this, gallery));
                        gallery.on('amasty_gallery:loaded', this._onGalleryLoadedFRunEmulation.bind(this, gallery));
                    }
                }
                this.amasty_conf_config.bindGallery = true;
            }
            this.options.jsonConfig.blockedImage = false;
        },

        /*fix issue when gallery data not loaded during option click*/
        _onGalleryLoadedFRunEmulation: function (element) {
            this._EmulateSelected();
        },
        
        getPreselectedAttributes: function() {
            var selectedAttributes = this._getSelectedAttributes();
            if (_.isEmpty(selectedAttributes) && this.options.jsonConfig.preselect) {
                selectedAttributes = this.options.jsonConfig.preselect.attributes;
            }
            
            return selectedAttributes;
        },

        /**
         * Load media gallery using ajax or json config.
         *
         * @private
         */
        _loadMedia: function () {
            if (!this.options.jsonConfig.blockedImage) {
                var amastyZoomEnabled = $('[data-role="amasty-gallery"]').length > 0;

                if (amastyZoomEnabled && !this.inProductList) {
                    this._reloadAmastyImageBlock();
                } else {
                    this._super();
                }
            }
        },
        
        /**
         * Compatibility with m2.1.5
         *
         * @private
         */
        _LoadProductMedia: function() {
            if (!this.options.jsonConfig.blockedImage) {
                var amastyZoomEnabled = $('[data-role="amasty-gallery"]').length > 0;

                if (amastyZoomEnabled && !this.inProductList) {
                    this._reloadAmastyImageBlock();
                } else {
                    this._super();
                }
            }
        },

        /**
         * Get chosen product. Compatibility with m2.1.5
         *
         * @returns int|null
         */
        getProduct: function () {
            var products = this._CalcProducts();

            return _.isArray(products) ? products[0] : null;
        },

        _reloadAmastyImageBlock: function () {
            var images = this.options.jsonConfig.images[this.getProduct()];

            if (!images) {
                images = this.options.mediaGalleryInitial;
            }

            var element = $('[data-role=amasty-gallery]').first();
            var zoomObject = element.data('zoom_object');
            if (zoomObject) {
                zoomObject.reload(images, this.options.gallerySwitchStrategy);
            }
        },

        _addOutOfStockLabels: function () {
            var $widget = this;
            if(this.options.jsonConfig.show_out_of_stock != 1) {
                return;
            }

            var attributeJson = this.options.jsonConfig.attributes[this.options.jsonConfig.attributes.length-1];
            if (!attributeJson || !attributeJson.options) {
                return;
            }


            var options = _.object(_.keys($widget.optionsMap), {});
            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var attributeId = $(this).attr('attribute-id');
                options[attributeId] = $(this).attr('option-selected');
            });

            var productInformation = $widget.options.jsonConfig.product_information;
            $.each(attributeJson.options, function () {
                options[attributeJson.id] = this.id;
                var product = _.findKey($widget.options.jsonConfig.index, options),
                    option = $('.swatch-option[option-id="' + this.id + '"]');
                if (product && option.length) {
                    if (productInformation[product] && !productInformation[product].is_in_stock) {
                        option.addClass('out-of-stock')
                            .addClass('disabled')
                            .removeClass('selected')
                            .attr('disabled', 'disabled');
                    } else {
                        option.removeClass('out-of-stock')
                            .removeClass('disabled')
                            .removeAttrs('disabled');
                    }
                }
            });
        },

        _RenderPricesForControls: function () {
            var $widget = this;
            if(this.options.jsonConfig.show_prices != 1) {
                return;
            }

            var attributeJson = this.options.jsonConfig.attributes[this.options.jsonConfig.attributes.length-1];
            if (!attributeJson || !attributeJson.options) {
                return;
            }

            $('[attribute-id="' + attributeJson.id + '"] .swatch-option-price').remove();

            var options = _.object(_.keys($widget.optionsMap), {});
            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var attributeId = $(this).attr('attribute-id');
                options[attributeId] = $(this).attr('option-selected');
            });

            $.each(attributeJson.options, function () {
                options[attributeJson.id] = this.id;
                var product = _.findKey($widget.options.jsonConfig.index, options);
                if (product) {
                    var price = $widget.options.jsonConfig.optionPrices[product].finalPrice.amount,
                        priceConfig = $('[data-role=priceBox]').priceBox('option').priceConfig,
                        priceFormat = (priceConfig && priceConfig.priceFormat) || {},
                        formatted = utils.formatPrice(price, priceFormat),
                        option = $('.swatch-option[option-id="' + this.id + '"]');
                    if (option.length && formatted) {
                        if (option.parents('.swatch-option-container').length === 0) {
                            option.wrap( "<div class='swatch-option-container'></div>" );
                        }

                        option.after( '<span class="swatch-option-price">' + formatted + '</span>' );
                        option.css('float', 'none');
                        option.parent().css('float', 'left');
                    }
                }
            });
        },

        _Rebuild: function () {
            var $widget = this,
                controls = $widget.element.find('.' + $widget.options.classes.attributeClass + '[attribute-id]'),
                selected = controls.filter('[option-selected]');

            // Enable all options
            $widget._Rewind(controls);

            // done if nothing selected
            if (selected.size() <= 0) {
                return;
            }

            // Disable not available options
            controls.each(function () {
                var $this = $(this),
                    id = $this.attr('attribute-id'),
                    products = $widget._CalcProducts(id);

                if (selected.size() === 1 && selected.first().attr('attribute-id') === id) {
                    return;
                }

                $this.find('[option-id]').each(function () {
                    var $element = $(this),
                        option = $element.attr('option-id');

                    if (!$widget.optionsMap.hasOwnProperty(id) || !$widget.optionsMap[id].hasOwnProperty(option) ||
                        $element.hasClass('selected') ||
                        $element.is(':selected')) {
                        return;
                    }

                    if (_.intersection(products, $widget.optionsMap[id][option].products).length <= 0) {
                        $element.attr('disabled', true).addClass('disabled');
                        if (typeof $widget.defaultContents[id] !== 'undefined') {
                            $element[0].textContent = $widget.defaultContents[id][option]['label'];
                        }
                    } else if (_.intersection(products, $widget.optionsMap[id][option].products).length == 1
                        && typeof $widget.defaultContents[id] !== 'undefined'
                        && typeof $widget.defaultContents[id][option]['label_price'] !== 'undefined'
                    ) {
                        $element[0].textContent = $widget.defaultContents[id][option]['label_price'];
                    }
                });
            });
        },

        _RenderProductMatrix: function () {
            var $widget = this,
                optionProduct = {},
                isProductViewExist = $('body.catalog-product-view').size() > 0,
                useMatrix = isProductViewExist && this.options.jsonConfig.matrix;

            if(!useMatrix) {
                return;
            }

            var attributeJson = this.options.jsonConfig.attributes[this.options.jsonConfig.attributes.length-1];
            if (!attributeJson || !attributeJson.options) {
                return;
            }

            var options = _.object(_.keys($widget.optionsMap), {});
            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var attributeId = $(this).attr('attribute-id');
                options[attributeId] = $(this).attr('option-selected');
            });


            $.each(attributeJson.options, function (i) {
                options[attributeJson.id] = this.id;
                var product = _.findKey($widget.options.jsonConfig.index, options);
                if (product) {
                    optionProduct[i] = {'product' : product, 'id' : this.id };
                }
            });

            if (Object.keys(optionProduct).length) {
                this._replaceOptionToMatrix(optionProduct);
                this._hideDefaultQty();
            } else {
                var matrixElement = $('.amconf-matrix-observed');
                if (this.originalAttributeContent && matrixElement.length) {
                    matrixElement.html(this.originalAttributeContent);
                }
                this._showDefaultQty();
            }
        },

        _replaceOptionToMatrix: function (optionProduct) {
            var attributeJson = this.options.jsonConfig.attributes[this.options.jsonConfig.attributes.length-1],
                attributeContainer = $('[attribute-code="' + attributeJson.code + '"]'),
                $widget = this;

            if (!attributeContainer.length) {
                return;
            }

            var newContent = this._generateMatrixContent(optionProduct, attributeContainer);
            attributeContainer.addClass('amconf-matrix-observed');
            attributeContainer.html('');
            attributeContainer.append(newContent);

            attributeContainer.find('.amconf-matrix-button.plus').on('click', $widget._plusQtyClick);
            attributeContainer.find('.amconf-matrix-button.minus').on('click', $widget._minusQtyClick);
            attributeContainer.find('.amconf-matrix-qty-unput').on('change', $widget._changeOptionQty);
        },

        _saveLastRowContent: function () {
            var attributeJson = this.options.jsonConfig.attributes[this.options.jsonConfig.attributes.length-1],
                attributeContainer = $('[attribute-code="' + attributeJson.code + '"]');

            if (!attributeContainer.length) {
                return;
            }

            if (!attributeContainer.hasClass('amconf-matrix-observed')) {
                this.originalAttributeContent = attributeContainer.html();
            }
        },

        _generateMatrixContent: function (optionProduct, attributeContainer) {
            var $widget = this,
                attribute = this.options.jsonConfig.attributes[this.options.jsonConfig.attributes.length-1],
                attributeId = attributeContainer.attr('attribute-id'),
                table,
                tr,
                td;

            table = $('<table>', {
                'class': 'amconf-matrix'
            });

            tr = $('<tr>', {
                'class': 'amconf-matrix-title'
            }).appendTo(table);

            if (attributeContainer.find('.swatch-attribute-label').length) {
                $widget.options.jsonConfig.titles['attribute'] =
                    attributeContainer.find('.swatch-attribute-label').text();
            }

            $.each($widget.options.jsonConfig.titles, function (index, value) {
                $('<td>').text(value).appendTo(tr);
            });

            $.each(optionProduct, function (i, data) {
                var option = data.id,
                    product = data.product;
                tr = $('<tr>', {
                    'class': 'amconf-matrix-row'
                }).appendTo(table);

                $.each($widget.options.jsonConfig.titles, function (index) {
                    td = $('<td>', {
                        'class': 'amconf-matrix-td-' + index
                    });

                    var value = '';
                    switch (index) {
                        case 'attribute':
                            var selector = '#option-label-color-' + attributeId + '-item-' + option,
                                element = attributeContainer.find(selector);
                            if (element.length) {
                                value = element.first().clone(true);
                            } else {
                                var controlLabelId = 'option-label-' + attribute.code + '-' + attribute.id,
                                    optionLabel = '',
                                    tmp = {};

                                tmp.id = attribute.id;
                                tmp.options = [];
                                $.each(attribute.options, function (key, opt) {
                                    if (opt.id === option) {
                                        tmp.options = [opt];
                                        optionLabel = opt.label
                                    }
                                });

                                value = $($widget._RenderSwatchOptions(tmp, controlLabelId));
                                if (!value.length) {
                                    value = $('<div>').text(optionLabel);
                                }
                            }
                            break;
                        case 'price':
                            var priceObject = $widget.options.jsonConfig.optionPrices[product],
                                price = priceObject.finalPrice.amount,
                                oldPrice = priceObject.oldPrice.amount,
                                priceConfig = $('[data-role=priceBox]').priceBox('option').priceConfig,
                                priceFormat = (priceConfig && priceConfig.priceFormat) || {},
                                priceDiv = $('<div>').text(utils.formatPrice(price, priceFormat)),
                                resultDiv = $('<div>');


                            resultDiv.append(priceDiv);
                            if (price !== oldPrice) {
                                resultDiv.append($('<div>', {
                                    'class': 'amconf-matrix-old-price'
                                }).text(utils.formatPrice(oldPrice, priceFormat)));
                            }

                            value = resultDiv;
                            break;
                        case 'qty':
                            value = $widget._getInputBlockByOption(attributeId, option, product);
                            break;
                        case 'subtotal':
                            var priceConfig = $('[data-role=priceBox]').priceBox('option').priceConfig,
                                priceFormat = (priceConfig && priceConfig.priceFormat) || {};

                            value = $('<div>', {
                                'class' : 'amconf-matrix-subtotal',
                                'data-price' : $widget.options.jsonConfig.optionPrices[product].finalPrice.amount
                            }).text(utils.formatPrice(0, priceFormat));
                            break;
                        case 'qty_available':
                            if ($widget.options.jsonConfig.product_information
                                && $widget.options.jsonConfig.product_information[product].qty
                            ) {
                                value = $widget.options.jsonConfig.product_information[product].qty
                            }
                            break;
                    }

                    td.append(value);
                    td.appendTo(tr);
                });
            });

            return table;
        },

        _getInputBlockByOption: function (attribute, id, product) {
            attribute = attribute.replace(/[^\d]/gi, '');
            var div = $('<div>', {
                'class': 'amconf-matrix-qty'
            });

            var span = $('<span>', {
                'class': 'amconf-matrix-button minus'
            }).html('&ndash;');
            div.append(span);

            var input = $('<input>', {
                'class': 'amconf-matrix-qty-unput',
                'name' : 'configurable-option[' + attribute + '][' + id + ']',
                'type': 'number',
                'min': '0',
                'step': 1,
                'value': '0'
            });

            if (this.options.jsonConfig.product_information
                && this.options.jsonConfig.product_information[product].qty
            ) {
                input.attr('max', this.options.jsonConfig.product_information[product].qty);
            }
            div.append(input);

            span = $('<span>', {
                'class': 'amconf-matrix-button plus'
            }).html('+');
            div.append(span);

            return div;
        },

        _changeOptionQty: function (e) {
            try {
                var $widget = this,
                    qtyElement = $(e.target),
                    subtotal = qtyElement.parents('tr').find('.amconf-matrix-subtotal').first();
            } catch(ex) {
                subtotal = null;
            }

            if (subtotal.length && qtyElement) {
                var qty = parseInt(qtyElement.val()),
                    price = subtotal.data('price');

                if (price) {
                    var priceConfig = $('[data-role=priceBox]').priceBox('option').priceConfig,
                        priceFormat = (priceConfig && priceConfig.priceFormat) || {};

                    var subtotalValue = price * qty;
                    subtotal.text(utils.formatPrice(subtotalValue, priceFormat));
                }
            }
        },

        _minusQtyClick: function (e) {
            try {
                var qtyElement = $(e.target).parent().find('.amconf-matrix-qty-unput').first();
            } catch(ex) {
                qtyElement = null;
            }

            if (qtyElement) {
                var qty = parseInt(qtyElement.val()),
                    decrement = 1;

                if (qty >= decrement) {
                    qty -= decrement;
                    qtyElement.val(qty);
                    qtyElement.trigger('change');
                }
            }
        },

        _plusQtyClick: function (e) {
            try {
                var qtyElement = $(e.target).parent().find('.amconf-matrix-qty-unput').first();
            } catch(ex) {
                qtyElement = null;
            }
            if (qtyElement) {
                var qty = parseInt(qtyElement.val()),
                    increment = 1,
                    availableQty = qtyElement.attr('max');

                qty += increment;
                if (!availableQty || availableQty >= qty) {
                    qtyElement.val(qty);
                    qtyElement.trigger('change');
                }
            }
        },

        _showDefaultQty: function () {
            var qtyElement = $(this.selectors['qty-block']);
            qtyElement.show();
        },

        _hideDefaultQty: function () {
            var qtyElement = $(this.selectors['qty-block']);
            qtyElement.hide();
        }
    });

    return $.amasty_conf.SwatchRenderer;
});
