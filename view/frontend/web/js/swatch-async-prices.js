/**
 * Mixin that loads prices information asynchronously so they work properly with base component.
 */
define(['jquery', 'underscore', 'mage/url'], function ($, _, url) {
    'use strict';

    return function (swatchRenderer) {
        $.widget('mage.SwatchRenderer', swatchRenderer, {
            _RenderControls: function () {
                var jsonConfig = this.options.jsonConfig;

                if (_.isEmpty(jsonConfig.optionPrices)) {
                    this._hasAsyncPrices = true;

                    $.each(jsonConfig.attributes, function () {
                        var item = this;

                        $.each(item.options, function () {
                            if (this.products.length > 0) {
                                jsonConfig.optionPrices[this.products[0]] = {
                                    finalPrice: { amount: 0 },
                                };
                            }
                        });
                    });
                }

                this._super();
            },
            _OnClick: function ($this, $widget) {
                var original = this._super.bind(this);

                if (!this._hasAsyncPrices) {
                    return original($this, $widget);
                }

                this._preparePrices($this, $widget).then(function () {
                    original($this, $widget);
                });
            },
            _OnChange: function ($this, $widget) {
                var original = this._super.bind(this);

                if (!this._hasAsyncPrices) {
                    return original($this, $widget);
                }

                this._preparePrices($this, $widget).then(function () {
                    original($this, $widget);
                });
            },
            _preparePrices: function ($this, $widget) {
                var jsonConfig = this.options.jsonConfig;

                if (!this._fetchPricesDeferred) {
                    this._fetchPricesDeferred = this._fetchPrices().then(
                        function (optionPrices) {
                            jsonConfig.optionPrices = optionPrices;

                            $.each(jsonConfig.attributes, function () {
                                var item = this;

                                $.each(item.options, function () {
                                    if (this.products.length > 0) {
                                        $widget.optionsMap[item.id][this.id] = {
                                            price: parseInt(
                                                jsonConfig.optionPrices[
                                                    this.products[0]
                                                ].finalPrice.amount,
                                                10
                                            ),
                                            products: this.products,
                                        };
                                    }
                                });
                            });
                        }
                    );
                }

                return this._fetchPricesDeferred;
            },
            _fetchPrices: function () {
                return $.get({
                    url: url.build(
                        'performance/swatches/prices/product_id/' +
                            this.options.jsonConfig.productId
                    ),

                    cache: true,
                });
            },
        });

        return $.mage.SwatchRenderer;
    };
});
