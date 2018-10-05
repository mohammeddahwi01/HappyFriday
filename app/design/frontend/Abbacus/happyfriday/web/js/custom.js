require(['jquery'],function($){
	jQuery(document).ready(function(){
		jQuery('.product.info.detailed .product.data.items .data.item.title:first-child').click();
		jQuery(window).on('scroll', stickyheader);
		function stickyheader() {
			if(jQuery(window).scrollTop() > 50) {
				jQuery('.page-header').addClass('sticky');
				jQuery('.block-search + .header.links').insertAfter(jQuery('.panel.header.bottom .minicart-wrapper'));
				var height = jQuery('.page-header.sticky').innerHeight();
				jQuery('.page-main').css('padding-top', height);
			}
			else {
				jQuery('.page-header').removeClass('sticky');
				jQuery('.minicart-wrapper + .header.links').insertAfter(jQuery('.panel.header.top .block.block-search'));
				jQuery('.page-main').css('padding-top', 0);
			}
		}
		function stickymobileheader() {
			if(jQuery(window).scrollTop() > 30) {
				jQuery('.page-header').addClass('stickyonmobile ');
				var height = jQuery('.page-header.stickyonmobile').innerHeight();
				jQuery('.page-main').css('padding-top', height);
			}
			else {
				jQuery('.page-header').removeClass('stickyonmobile ');
				jQuery('.page-main').css('padding-top', 0);
			}
		}
		if(!(window.matchMedia("(min-width: 768px)").matches)) {
			jQuery(window).off('scroll', stickyheader);
			jQuery(window).on('scroll', stickymobileheader);
		}
		else {
			jQuery(window).on('scroll', stickyheader);
			jQuery(window).off('scroll', stickymobileheader);
		}
		jQuery('.panel.wrapper .block-search .label').on('click',function(){
			jQuery(this).next('.control').slideToggle();
		});
	});

	jQuery(window).load(function(){
		
		
		//product view page javascript
		var index = 0;
		setInterval(function(){
			jQuery('#amasty-gallery-images .amasty-gallery-thumb-link').eq(index).click();
			index++;
			if (index == jQuery('#amasty-gallery-images .amasty-gallery-thumb-link').length) {
				index = 0;
			}
		},5000);
	})


});