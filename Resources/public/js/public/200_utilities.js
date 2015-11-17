jQuery(function($, undefined) {;
	
	var detectsCss = $('.detectCss');
	
	$.extend({
		preloadImage: function(src, clb) {
			$('<img />')
				.on('load', function() {
					if ($.isFunction(clb))
						clb();
				})
				.attr('src', src);
		},
		detectCss: function() {
			return detectsCss.filter(':visible').data('type');
		}
	});

});