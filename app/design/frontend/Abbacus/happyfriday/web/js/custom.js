require(['jquery'],function($){
	jQuery(window).on('scroll', stickyheader);
	function stickyheader() {
		if(jQuery(window).scrollTop() > 50) {
			jQuery('.page-header').addClass('sticky');
			jQuery('.block-search + .header.links').insertAfter(jQuery('.panel.header.bottom .minicart-wrapper'));
		}
		else {
			jQuery('.page-header').removeClass('sticky');
			jQuery('.minicart-wrapper + .header.links').insertAfter(jQuery('.panel.header.top .block.block-search'));
		}
	}
	function stickymobileheader() {
		if(jQuery(window).scrollTop() > 30) {
			jQuery('.page-header').addClass('stickyonmobile ');
		}
			
		else {
			jQuery('.page-header').removeClass('stickyonmobile ');
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
});