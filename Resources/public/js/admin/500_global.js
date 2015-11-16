jQuery(function($) {
	var contentTree = $('#contentTree'),
		form_contactEnabled = $('#form_contactEnabled'),
		deletes = $('.delete'),
		switcher = $('.switcher');
	
	if (contentTree.length) {
		var updateLevels = function(elt) {
			var par = elt.parent();
			elt.children('input[name^="treeLevel"]').val(par.parents('ul').length + 1)
			par.children('.node').children('input[name^="treeChanged"]').val(1);
			elt.children('ul').children('.node').each(function() {
				updateLevels($(this));
			});
		};
		contentTree
			.on('click', '.expandAll', function(e) {
				e.preventDefault();
				contentTree.find('.node').addClass('expanded');
			})
			.on('click', '.reduceAll', function(e) {
				e.preventDefault();
				contentTree.find('.node').removeClass('expanded');
			})
			.find('.tree').sortable({
				items: 'li',
				handle: '.move',
				connectWith: '.treeEditable',
				placeholder: 'ui-state-highlight',
				update: function(e, ui) {
					updateLevels(ui.item);
				}
			}).disableSelection()
			.find('.reduce, .expand')
				.on('click', function(e) {
					e.preventDefault();
					$(this).closest('li').toggleClass('expanded');
				});
	}
	
	if (form_contactEnabled.length) {
		form_contactEnabled.each(function() {
			var me = $(this),
				contactFields = me.closest('form').find('.contactField').closest('.form_row');
			
			me.on('change', function() {
				if (me.is(':checked')) {
					contactFields.slideDown();
				} else {
					contactFields.slideUp();
				}
			}).trigger('change');
		});
	}
	
	if (deletes.length) {
		deletes.on('click', function(e) {
			e.preventDefault();
			var me = $(this);
			$.nmConfirm({
				text: me.data('deletetxt') || 'Êtes-vous sûr de vouloir supprimer cet élément ?',
				cancel: 'Annuler',
				clbOk: function() {
					document.location.href = me.attr('href');
				}
			});
		});
	}
	
	if (switcher.length) {
		switcher
			.on('click', function(e) {
				e.preventDefault();
				$($(this).attr('href')).slideToggle();
			}).filter('.filterSwitcher').each(function() {
				var me = $(this),
					filter = $(me.attr('href')),
					hasData = false;
				filter.find(':input').not('.row_form_transformer :input, button').each(function() {
					if ($(this).val().length)
						hasData = true;
				});
				if (hasData)
					filter.show();
			});
	}
});