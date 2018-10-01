define([
    "jquery",  
    "jquery/ui",
    "nprogress",
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function($, $ui, nprogress, Component, customerData) {
	"use strict";
	var GuestWishlist = function(options){
		var object	= this;
		var defaults = {
			'elements':        {
				'add'			: '.towishlist',
				'remove'		: '#wishlist-sidebar .btn-remove, .btn-wishlist-remove',
				'cart'			: '#wishlist-view-form .tocart', 
				'update'		: '#form-wishlist-items .update'				
			},
			'showMsg'		: 0,			
			'useAjax'		: 1,
			'class_has_item': 'added',
			'keepItem'		: 0,
			'url'			: {
				'remove'		: '',
				'add'			: ''
			},
			'area'			: {				
				'wishlist'		: '#wishlist-view-form',				
			},
			'translate'		: {				
				'remove'	: 'Remove from Wishlist',
				'error'		: 'Sorry, We can\'t process your request. Please try again in next time.',
				'confirm'	: 'Are you sure you would like to remove this item from the wishlist?',
				'login'		: 'Please login before add to wishlist.'
			},
			
			'added'			: {},			
			'selector_add'	: 'a[href*="wishlist/index/add/product/{product_id}/"]',			
			
					
		};
		
		this.config = $.extend(defaults, options || {});
		
		if(!object.config.useAjax){			
			return false;
		}		
		
		// building function
		$.each(this.config.elements, function(method,class_name){			

			
			$(class_name).each(function(){				
								
				var dataPost 	= $(this).data('post');
				var dataRemove	= $(this).data('post-remove');
				
				if(dataPost){										
					$(this).data('wishlist', dataPost);					
				}
				
				if(dataRemove){
					$(this).data('wishlist-remove', dataRemove);
				}
				
				$(this)
					.unbind("click")
					.removeAttr("onclick")					
					.removeAttr("data-post")
					.removeAttr("data-role");
				
				switch(method){
					case 'add' :
						$(this).attr('title', object.config.translate.add);
						var AddedWishlist	= $(this).data('wishlist');
						
						if(AddedWishlist && object.config.added[AddedWishlist.data.product]){							
							$(this)
								.data('wishlist', object.config.added[AddedWishlist.data.product])
								.addClass(object.config.class_has_item)
								.attr('title', object.config.translate.remove)
								.html($('<span>').html(object.config.translate.remove));
						}	
						
						$(this).click(function(){
							
							$(this).attr('href', 'javascript:void(0);');
							
							object.add(this, "add");
							
							return false;
						});
						
						break;
					case 'remove' :
						
						$(this).click(function(){
							object.remove(this);
							return false;
							
						});
						break;
					case 'cart':
						$(this).click(function(){
							object.cart(this);						
							return false;
						});
						break;
					case 'update':
						
						$(this).click(function(){
							object.update(this);						
							return false;
						});
						break;
					case 'data-wishlist':
						
						$(this).click(function(){
							object.datawishlist(this);
							return false;
						});
						break;
				}
				
				
											
			});
			
		});
		
		// remove option type 
		this.add	= function(el, type){
			
			var dataWishlist	= $(el).data('wishlist');			
			var data			= dataWishlist ? dataWishlist.data : {};
			var url				= dataWishlist ? dataWishlist.action : '';
			
			
			
			object.ajax(url, data, function(data, textStatus, jqXHR){
				
				if(!data.error){
					data.type 				== "added" ? $(el).addClass(object.config.class_has_item) : $(el).removeClass(object.config.class_has_item) ;
					dataWishlist.action 	= data.url;
					dataWishlist.data.item	= data.item_id;
					
					$(el)	
						.data('wishlist', dataWishlist)
						.attr('title', data.title)
						.html($('<span>').html(data.title))
						;				
					
				}
				object.updateData(el, data);
				
				
			});
		};
		
		this.datawishlist = function(el){
			return ;
			var dataWishlist	= $(el).data('wishlist');			
			var data			= dataWishlist ? dataWishlist.data : {};
			var url				= dataWishlist ? dataWishlist.action : '';
			
			object.ajax(url, data, function(data, textStatus, jqXHR){
				
				if(!data.error){
					//data.type == "added" ? $(el).addClass(object.config.class_has_item) : $(el).removeClass(object.config.class_has_item) ;
					//dataWishlist.action = data.url;
					
				}
				object.updateData(el, data);
				
				
			});
		}
		
		
		
		this.remove	= function(el){
			
			if(confirm(object.config.translate.confirm)){
				var dataWishlist	= $(el).data('wishlist-remove');						
				
				object.ajax(dataWishlist.action, dataWishlist.data, function(data, textStatus, jqXHR){
					
					if(!data['error']){
						// refesh product
						$('.' + object.config.class_has_item).each(function(){
							var productData = $(this).data('wishlist');
							
							if(productData.data.item == dataWishlist.data.item){					 
								
								productData.action = data.url;
								
								$(this)
									.removeClass(object.config.class_has_item)
									.data('wishlist', productData)
									.attr('title', data.title);	
							}
						});
						
						$("li#item_" + dataWishlist.data.item, $(object.config.area.wishlist)).remove();
						
						if($(el).closest("li").length){
							$(el).closest("li").remove();
						}
					}
					
					
					object.updateData(el, data);
				});
			}
			
			
			
		};
		
		this.cart	= function(el){
			
			var dataWishlist 	= $(el).data('wishlist');
			
			
			object.ajax(dataWishlist.action, dataWishlist.data, function(data, textStatus, jqXHR){
				if(!data.redirect_to && !data['error']){
					
					if(!object.config.keepItem){
						if($(el).closest("li").length){
							$(el).closest("li").remove();
						}
					}
					
					
					$('.' + object.config.class_has_item).each(function(){
						var productData = $(this).data('wishlist');
						
						if(productData.data.item == dataWishlist.data.item){					 
							
							productData.action = data.url;
							
							$(this)
								.removeClass(object.config.class_has_item)
								.data('wishlist', productData)
								.attr('title', data.title);	
						}
					});
					if(!object.config.keepItem){
						$("li#item_" + dataWishlist.data.item, $(object.config.area.wishlist)).remove();
					}
					
					
					
				}
								
				object.updateData(el, data);
			});
		};
		
		
		
		
		
		
		this.updateData	= function(el, data){
			
			var config	= object.config;			
			
			if(data.msg && config.showMsg){
				object.addMsg(data.msg, data.error);
			}			
			
			if($(config.area.wishlist).length){
				if(data.emptyWL){
					$(config.area.wishlist).html(data.wishlist);
				}
			}	
			
			if(data.redirect_to){
				/*
				$.fancybox({
					'type' 	: 'ajax',
					'href'	: data.redirect							
				});	*/
				
				document.location.href = data.redirect_to;
			
				return false;
			}
			
		};
		
		this.ajax	= function(url, data, callback){		
			
			if(!url){
				object.addMsg(object.config.translate.error, 1);
				return;
			}
			
			data		= data || {};
			if($.type(data) == "object"){
				data.isAjax	= true;
			}else{
				data += "&isAjax=true";
			}
			
			
			
			$.ajax({
				'url'			: url,	
				'type'			: 'POST',
				'dataType'		: 'json',	
				'data'			: data,		
				'beforeSend'	: function(jqXHR, settings){
					nprogress.start();
					nprogress.set(0.5);	
				},				
				'success'		: callback,
				'complete'		: function(jqXHR, textStatus){
					nprogress.done();
				},			
				'error': function(jqXHR, textStatus, errorThrown){
					object.addMsg(object.config.translate.error, 1);
					nprogress.done();
				}
			});
			
		};
		
		this.addMsg	= function(msg, type){
			var objectMsg	= $('.wishlist-notify');			
			
			if(type){
				objectMsg.addClass("wishlist-notify-error");
			}else{
				objectMsg.removeClass("wishlist-notify-error");
			}
			
			objectMsg.slideUp(500);
			
			objectMsg
				.css('zIndex', 99999)
				.html(msg)
				.slideDown(500);
			
			setTimeout(function () {
				objectMsg
					.slideUp(500);
				}, 7000);
		};
		
	
		
		
		this.update	= function(el){			
			
			var form		= $(el).closest('form');			
			var data		= form.length ? form.serialize() : {};
			var url			= form.attr('action');
			
			object.ajax(url, data, function(data, textStatus, jqXHR){								
				object.updateData(el, data);
			});
			
		};		
		
	}
	
	/*return $.fn.GuestWishlist = function(options){
		return new GuestWishlist(options);
	};*/
	
	return Component.extend({
        initialize: function (options) {
           this._super();
           this.wishlist		 	= customerData.get('wishlist');  
           options					= options || {};
           options.added			= this.wishlist().added;
       	   window.guestwishlist 	= new GuestWishlist(options);
        }
    });
	
	
	
});