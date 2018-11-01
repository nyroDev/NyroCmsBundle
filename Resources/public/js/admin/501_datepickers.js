jQuery(function ($) {
	var dates = $('.datepicker');

	if (dates.length) {
		dates.each(function () {
			var me = $(this);
			var options = {
				showOtherMonths: true,
				selectOtherMonths: true,
				changeMonth: true,
				changeYear: true
			};

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