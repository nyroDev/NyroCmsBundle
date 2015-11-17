jQuery(function($) {
	var slideshows = $('.block_slideshow');
	
	$.fn.extend({
		slideshow: function() {
			return this.each(function() {
				if ($(this).data('slidehowInited'))
					return;
				var me = $(this).data('slidehowInited', true),
					big = me.find('.block_slideshow_big'),
					bigImg = big.children('img'),
					bigSpan = big.children('span'),
					bigImgAnim = $('<img class="block_slideshow_big_anim"/>').appendTo(big),
					prev = $('<a href="#" class="block_slideshow_arrow block_slideshow_prev" />').appendTo(big),
					next = $('<a href="#" class="block_slideshow_arrow block_slideshow_next" />').appendTo(big),
					ul = me.find('ul'),
					excludeSelector = '.deleted, .ui-state-highlight',
					animating = false,
					timer,
					show = function(li) {
						if (animating)
							return;
						animating = true;
						var link = li.children('a'),
							img = link.children('img');
						$.preloadImage(link.attr('href'), function() {
							ul.children('.active').removeClass('active');
							bigImgAnim.attr('src', link.attr('href')).addClass('show');
							setTimeout(function() {
								li.addClass('active');
								bigImg.attr('src', link.attr('href'));
								bigSpan.text(img.attr('alt'));
								bigImgAnim.addClass('hide');
								bigImgAnim.removeClass('show');
								bigImgAnim.removeClass('hide');
								animating = false;
								startTimer();
							}, 350);
						});
					},
					showNext = function() {
						var active = ul.children('.active'),
							next = active.nextAll(':not('+excludeSelector+'):first');
						if (next.length == 0)
							next = ul.children('li:not('+excludeSelector+')').first();

						if (next.length && !next.is(active)) {
							show(next);
						} else {
							endTimer();
						}
					},
					showPrev = function() {
						var active = ul.children('.active'),
							prev = active.prevAll(':not('+excludeSelector+'):first');
						if (prev.length == 0)
							prev = ul.children('li:not('+excludeSelector+')').last();

						if (prev.length && !prev.is(active)) {
							show(prev);
						} else {
							endTimer();
						}
					},
					endTimer = function() {
						if (timer) {
							clearTimeout(timer);
							timer = false;
						}
					},
					startTimer = function() {
						endTimer();
						timer = setTimeout(showNext, 5000);
					};

				ul.on('click', '.block_slideshow_thumb', function(e) {
					if (!e.isDefaultPrevented()) {
						e.preventDefault();
						show($(this).closest('li'));
					}
				});

				next.on('click', function(e) {
					e.preventDefault();
					showNext();
				});
				prev.on('click', function(e) {
					e.preventDefault();
					showPrev();
				});
				big
					.on('swiperight', function() {
						prev.trigger('click');
					})
					.on('swipeleft', function() {
						next.trigger('click');
					});
				me
					.on('slideshowShow', function(e, li) {
						show(li);
					})
					.on('slideshowStartTimer', function() {
						if (!animating && !timer) {
							startTimer();
						}
					});

				startTimer();
			});
		}
	});
	
	if (slideshows.length)
		slideshows.slideshow();
});