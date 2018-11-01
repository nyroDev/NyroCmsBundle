jQuery(function ($) {

	$.extend({
		nmConfirm: function (options) {
			var opts = $.extend({
					class: 'nmConfirm',
					text: false,
					html: false,
					ok: 'OK',
					cancel: false,
					inputs: false,
					input: false,
					inputValue: false,
					inputPlaceholder: false,
					contentClb: false,
					clbOk: false,
					clbKo: false
				}, options),
				isConfirmed = false,
				contentHtml,
				content;

			contentHtml = '<div class="' + opts.class + '">';
			if (opts.html) {
				contentHtml += '<p>' + opts.html + '</p>';
			}
			if (opts.text) {
				contentHtml += '<p>' + opts.text + '</p>';
			}

			if (opts.inputs) {
				contentHtml += '<form action="#" method="get">';
				contentHtml += opts.inputs;
			} else if (opts.input) {
				contentHtml += '<form action="#" method="get">';
				if (opts.input == 'select') {
					contentHtml += '<select>';
					$.each(opts.values, function (k, v) {
						contentHtml += '<option value="' + v.val + '"' + (v.val == opts.inputValue ? ' selected="selected"' : '') + '>' + v.label + '</option>';
					});
					contentHtml += '</select>';
				} else {
					contentHtml += '<input type="' + opts.input + '" value="' + (opts.inputValue !== false ? opts.inputValue : '') + '" placeholder="' + (opts.inputPlaceholder !== false ? opts.inputPlaceholder : '') + '" />';
				}
			}
			if (opts.ok || opts.cancel) {
				contentHtml += '<div class="nmButtons">';
				if (opts.cancel) {
					contentHtml += '<button type="cancel" class="nyroModalClose button">' + opts.cancel + '</button>';
				}
				if (opts.ok) {
					contentHtml += '<button type="submit" class="nyroModalConfirm button">' + opts.ok + '</button>';
				}
				contentHtml += '</div>';
			}
			if (opts.inputs || opts.input) {
				contentHtml += '</form>';
			}
			contentHtml += '</div>';

			var content = $(contentHtml);

			if (opts.contentClb && $.isFunction(opts.contentClb)) {
				opts.contentClb(content);
			}

			if (opts.input || opts.inputs) {
				content.find('form').on('submit', function (e) {
					e.preventDefault();
					isConfirmed = true;
					if ($.isFunction(opts.clbOk)) {
						opts.clbOk(opts.inputs ? $(this).serializeArray() : $(this).find(':input').val());
					}
					$.nmTop().close();
				});
			} else {
				content.find('.nyroModalConfirm').on('click', function (e) {
					e.preventDefault();
					isConfirmed = true;
					if ($.isFunction(opts.clbOk)) {
						opts.clbOk();
					}
					$.nmTop().close();
				});
			}

			$.nmData(content, {
				anim: {
					def: 'basic'
				},
				showCloseButton: false,
				stack: true,
				callbacks: {
					initElts: function (nm) {
						nm.elts.all.addClass('nyroModalConfirmCont');
					},
					afterClose: function () {
						if (!isConfirmed && $.isFunction(opts.clbKo)) {
							opts.clbKo();
						}
					}
				}
			});
		}
	});

});