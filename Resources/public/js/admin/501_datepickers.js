jQuery(function ($) {

	$.fn.extend({
		myDatepickerDataSearch: function (opts, tKey) {
			if (!opts) {
				opts = {};
			}
			tKey = tKey || 'datepicker_';
			var tKeyLn = tKey.length;
			$.each($(this).first().data(), function (i, e) {
				if (i.indexOf(tKey) == 0) {
					opts[i.substring(tKeyLn)] = e;
				}
			});

			return opts;
		}
	});

	var dates = $('.datepicker');

	if (dates.length) {
		dates.each(function () {
			var me = $(this);
			var options = me.myDatepickerDataSearch({
				showOtherMonths: true,
				selectOtherMonths: true,
				changeMonth: true,
				changeYear: true
			});

			if (me.data('future')) {
				options.minDate = '+1D';
			}
			if (me.data('past')) {
				options.maxDate = '-1D';
			}

			me.prop('type', 'text');
			if (me.is('.datetimepicker')) {
				options.timeText = 'Heure';
				options.hourText = 'Heure';
				options.minuteText = 'Minute';
				options.closeText = 'OK';
				options.stepMinute = me.data('stepminute');
				me.datetimepicker(options);
			} else {
				me.datepicker(options);
			}
		});
	}
});