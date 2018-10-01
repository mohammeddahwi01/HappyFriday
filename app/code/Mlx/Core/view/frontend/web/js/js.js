define([
    "jquery",  
    "jquery/ui"
], function($) {
	"use strict";
	var GuestWishlist = function(){
		var object	= this;
		var defaults = {
			'elements':        {
				'add'		: '.link-wishlist',
				'remove'	: '#wishlist-sidebar .btn-remove, .btn-wishlist-remove',
				'cart'		: '.btn-wishlist-cart',
				'update'	: '.btn-wishlist-update'
			},
			'translate'		: {
				'add'		: 'Add to Wishlist',
				'remove'	: 'Remove from Wishlist',
				'error'		: 'Sorry, We can\'t process your request. Please try again in next time.',
				'confirm'	: 'Are you sure you would like to remove this item from the wishlist?',
				'login'		: 'Please login before add to wishlist.'
			},
			'area'			: {
				'cart'			: $('.checkout-cart-index .cart'),
				'wishlist'		: $('.wishlist-index-index .my-wishlist'),
				'sidebar'		: $('.block-wishlist'),
			},
			'count'			: {
				'wishlist'		: $('[href$="wishlist/"]', $('.header .links')),
				'cart'			: $('.top-link-cart', $('.header .links'))
			},			
			'url'			: {
				'remove'		: '',
				'add'			: ''
			},
			'added'			: {},			
			'selector_add'	: 'a[href*="wishlist/index/add/product/{product_id}/"]',
			'class_has_item': 'added',
			'number_col'	: 3,
			'showMsg'		: 1,			
			'useAjax'		: 1,
		};
		
		this.config = $.extend(defaults, options || {});
		
		if(!object.config.useAjax){			
			return false;
		}
		
	}
	
	$.fn.GuestWishlist = function(options){
		return new GuestWishlist(options);
	};
});