jQuery(function ($) {

	var composer = $('#composer');

	if (composer.length) {
		var $b = $('body'),
			main = composer.find('#composerContents'),
			txtConfirm = composer.data('confirm'),
			txtCancel = composer.data('cancel'),
			hasChanged = false,
			getIcon = function (name) {
				return composer.data('icon').replace(/TPL/g, name);
			},
			avlBlocksInput = composer.find('#avlBlocksInput'),
			themeDemo = composer.find('#themeDemo'),
			saveButton = composer.children('button').attr('disabled', 'disabled').prop('disabled', 'disabled'),
			cont = main.children('#composerCont'),
			tinymceurl = composer.data('tinymceurl'),
			htmlOptions = composer.myTinymceDataSearch(),
			simpleOptions = composer.myTinymceDataSearch('tinymcesimple_'),
			pluploadOptions = composer.nyroPluploadDataSearch({
				showCancelAll: false,
				addFormVars: false,
				multi_selection: false,
				events: {
					PostInit: function (up) {
						var imgBut = $(up.settings.container).prev('.composableImg');
						if (!imgBut.closest('.composableImgCont').is('.composableImgBig')) {
							imgBut.show();
						}
					},
					BeforeUpload: function (up, file) {
						var compImg = $(up.settings.drop_element).closest('.composableImgCont');
						up.settings.file_data_name = 'image';
						up.settings.multipart_params = {
							imageUpload: 1,
							cfg: compImg.data('cfg')
						};
						if (compImg.data('more')) {
							up.settings.multipart_params.more = compImg.data('more');
						}
					},
					FileUploaded: function (up, file, data) {
						var $data = $.parseJSON(data.response),
							compImg = $(up.settings.drop_element).closest('.composableImgCont'),
							textarea = compImg.find('textarea#'+compImg.data('name')),
							block = compImg.closest('.composerBlock');
						if (compImg.is('.composableImgBig')) {
							block.css('background-image', 'url(' + $data.resized + ')');
						} else if (!compImg.is('.composableImgMobile')) {
							compImg.find('img').attr('src', $data.resized);
						}
						if ($data.datas) {
							$.each($data.datas, function (k, v) {
								block.data(k, v);
							});
							block.trigger('composerImgChange');
						}
						compImg.addClass('composableImgExists');
						textarea.val(textarea.val() + "\n" + $data.file);
						changed();
					}
				},
				onAllComplete: false
			}),
			pluploadFileOptions = composer.nyroPluploadDataSearch({
				showCancelAll: false,
				addFormVars: false,
				multi_selection: false,
				events: {
					BeforeUpload: function (up, file) {
						var compFile = $(up.settings.drop_element).closest('.composableFileCont');
						up.settings.file_data_name = 'file';
						up.settings.multipart_params = {
							fileUpload: 1,
							cfg: compFile.data('cfg')
						};
						if (compFile.data('more')) {
							up.settings.multipart_params.more = compFile.data('more');
						}
					},
					FileUploaded: function (up, file, data) {
						var $data = $.parseJSON(data.response),
							compFile = $(up.settings.drop_element).closest('.composableFileCont'),
							textarea = compFile.find('textarea'),
							block = compFile.closest('.composerBlock');
						if ($data.datas) {
							$.each($data.datas, function (k, v) {
								block.data(k, v);
							});
							block.trigger('composerFileChange');
						}
						compFile.addClass('composableFileExists');
						textarea.val(textarea.val() + "\n" + $data.file);
						changed();
					}
				},
				onAllComplete: false
			}),
			changed = function () {
				if (!hasChanged) {
					hasChanged = true;
					saveButton.removeAttr('disabled').removeProp('disabled');
				}
			},
			initComposable = function (parent) {
				parent
					.find('.block_handler [required]').removeAttr('required').removeProp('required').end()
					.find('.composableSimple').myTinymce(simpleOptions, tinymceurl).end()
					.find('.composableHtml').myTinymce(htmlOptions, tinymceurl).end()
					.find('.composableImg').nyroPlupload(pluploadOptions).end()
					.find('.composableImgDelete').each(function () {
						var me = $(this),
							compImg = me.prev('.composableImgCont'),
							textarea = compImg.find('textarea');
						me.on('click', function (e) {
							e.preventDefault();
							$.nmConfirm({
								text: me.data('confirm'),
								ok: txtConfirm,
								cancel: txtCancel,
								clbOk: function () {
									compImg.removeClass('composableImgExists');
									textarea.val(textarea.val() + "\nDELETE");
									if (compImg.is('.composableImgBig')) {
										compImg.closest('.composerBlock').css('background-image', 'none');
									}
									changed();
								}
							});
						});
					}).end()
					.find('.composableFile')
						.each(function() {
							var me = $(this),
								compFile = me.closest('.composableFileCont'),
								opts = $.extend({}, pluploadFileOptions);

							if (compFile.data('cfgUpload')) {
								opts = $.extend(opts, compFile.data('cfgUpload'));
							}

							me.nyroPlupload(opts);
						}).end()
					.find('.composableFileDelete').each(function () {
						var me = $(this),
							compFile = me.prev('.composableFileCont'),
							textarea = compFile.find('textarea');
						me.on('click', function (e) {
							e.preventDefault();
							$.nmConfirm({
								text: me.data('confirm'),
								ok: txtConfirm,
								cancel: txtCancel,
								clbOk: function () {
									compImg.removeClass('composableFileExists');
									textarea.val(textarea.val() + "\nDELETE");
									changed();
								}
							});
						});
					}).end()
					.find('.composableUrl').each(function () {
						var me = $(this),
							name = me.data('name'),
							inputUrl = me.closest('.composerBlock').find('#' + name),
							handler = $('<a href="#" class="composableUrlHandler" title="' + composer.data('linkurl') + '">' + composer.data('linkurl') + '</a>').insertAfter(me);
						handler.on('click', function (e) {
							e.preventDefault();
							$.nmConfirm({
								text: composer.data('linkurl'),
								ok: txtConfirm,
								cancel: txtCancel,
								input: 'text',
								inputPlaceholder: '',
								inputValue: inputUrl.val(),
								clbOk: function (newUrl) {
									if (newUrl != inputUrl.val()) {
										inputUrl.val(newUrl);
										changed();
									}
								}
							});
						});
					}).end()
					.find('.composableSel').each(function () {
						var me = $(this),
							isObject = me.is('.composableObjectId'),
							classPrefix = me.data('classprefix'),
							name = me.data('name'),
							spans = me.children('span'),
							composerBlock = me.closest('.composerBlock'),
							applyTo = composerBlock,
							inputVal = composerBlock.find('#' + name);
						if (me.data('applyto')) {
							applyTo = composerBlock.find(me.data('applyto'));
						}
						me.on('click', function (e) {
							e.preventDefault();
							var sels = [],
								classes = [];
							spans.each(function () {
								var sp = $(this);
								sels.push({
									val: sp.data('val'),
									label: sp.text()
								});
								if (!isObject) {
									classes.push(classPrefix + sp.data('val'));
								}
							});
							$.nmConfirm({
								text: me.attr('title'),
								ok: txtConfirm,
								cancel: txtCancel,
								input: 'select',
								values: sels,
								inputValue: inputVal.val(),
								clbOk: function (newVal) {
									if (newVal != inputVal.val()) {
										if (isObject) {
											composerBlock
												.children('#' + classPrefix + inputVal.val()).hide().end()
												.children('#' + classPrefix + newVal).show().end()
												.trigger('composerSelChange', [name, newVal]);
										} else {
											applyTo.removeClass(classes.join(' ')).addClass(classPrefix + newVal);
											spans
												.filter('.active').removeClass('active').end()
												.filter('[data-val="' + newVal + '"]').addClass('active');
											composerBlock.trigger('composerSelChange', [name, newVal]);
										}
										inputVal.val(newVal);
										changed();
									}
								}
							});
						});
					}).end()
					.find('.composableVideo').each(function () {
						var video = $(this),
							link = video.children('a'),
							iframe = video.children('iframe'),
							inputUrl = video.children('textarea[name*="url"]'),
							inputEmbed = video.children('textarea[name*="embed"]'),
							inputAutoplay = video.children('textarea[name*="autoplay"]'),
							autoplayChk,
							askUrl = function () {
								$.nmConfirm({
									text: link.text(),
									ok: txtConfirm,
									cancel: txtCancel,
									input: 'url',
									inputValue: inputUrl.val(),
									inputPlaceholder: 'https://www.youtube.com/watch?v=vN1FJPQG9co',
									contentClb: function (content) {
										var url = content.find('input[type="url"]');
										url.after('<label for="autoplayChk">' + inputAutoplay.data('label') + '</label>');
										autoplayChk = $(
											'<input type="checkbox" value="1" name="autoplayChk" id="autoplayChk" ' +
											(inputAutoplay.val() ? 'checked="checked" ' : '') +
											' />'
										).insertAfter(url);
										url.after('<br />');
									},
									clbOk: function (newUrl) {
										if (newUrl && newUrl != inputUrl.val()) {
											$.ajax({
												url: video.closest('form').attr('action'),
												'type': 'post',
												dataType: 'json',
												data: {
													video: 1,
													url: newUrl,
													autoplay: autoplayChk.is(':checked') ? 1 : 0
												}
											}).done(function (data) {
												if (!data.err) {
													iframe.attr('src', data.embed);
													inputUrl.val(data.url);
													inputEmbed.val(data.embed);
													inputAutoplay.val(autoplayChk.is(':checked') ? 1 : '');
													changed();
												} else {
													$.nmConfirm({
														text: data.err
													});
												}
											});
										}
									}
								});
							};
						link.on('click', function (e) {
							e.preventDefault();
							askUrl();
						});
					}).end()
					.find('.composableSlideshow').each(function () {
						var me = $(this),
							big = me.find('.nyroCmsSlideshow_big'),
							myPluploadOptions = $.extend(true, {}, pluploadOptions),
							nb = me.data('nb'),
							nav = me.children('ul'),
							nbLi = nav.children().length,
							multipleFields = me.data('multiplefields') ? me.data('multiplefields').split(',') : false,
							sizebig = me.data('sizebig'),
							sizebigCfg = me.data('sizebigcfg'),
							sizethumbCfg = me.data('sizethumbcfg'),
							placehold = me.data('placehold');

						myPluploadOptions.events.BeforeUpload = function (up, file) {
							up.settings.file_data_name = 'image',
								up.settings.multipart_params = {
									imageUpload: 1,
									cfg: sizebigCfg,
									cfg2: sizethumbCfg
								};
						};
						myPluploadOptions.events.FileUploaded = function (up, file, data) {
							var $data = $.parseJSON(data.response),
								$li = $(up.settings.drop_element).closest('li'),
								textarea = $li.find('textarea[name*="images"]');
							$li
								.children('.nyroCmsSlideshow_thumb').attr('href', $data.resized)
								.children('img').attr('src', $data.resized2);
							textarea.val(textarea.val() + "\n" + $data.file);
							changed();
						};

						me
							.on('click', '.nyroCmsSlideshow_thumb', function (e) {
								e.preventDefault();
							})
							.on('click', '.composableSlideshowDelete', function (e) {
								e.preventDefault();
								var me = $(this);
								$.nmConfirm({
									text: composer.data('slideshowdelete'),
									ok: txtConfirm,
									cancel: txtCancel,
									clbOk: function () {
										me.closest('li').addClass('deleted').fadeOut()
											.find('textarea[name*="deletes"]').val(1);
										changed();
									}
								});
							})
							.on('click', '.composableSlideshowEdit', function (e) {
								e.preventDefault();
								var $li = $(this).closest('li');
								if (multipleFields && multipleFields.length) {
									var textareas = $li.find('textarea'),
										inputsHtml = '';

									// Start by adding title
									inputsHtml += '<input name="title" type="text" value="' + textareas.filter('[name*="titles"]').val() + '" /><br />';

									$.each(multipleFields, function () {
										inputsHtml += '<p>' + this + '</p>';
										if (this.indexOf('text') === 0) {
											inputsHtml += '<textarea name="' + this + '">' + textareas.filter('[name*="' + this + 's"]').val() + '</textarea><br />';
										} else {
											inputsHtml += '<input name="' + this + '" type="text" value="' + textareas.filter('[name*="' + this + 's"]').val() + '" / ><br />';
										}
									});

									$.nmConfirm({
										text: composer.data('slideshowtitle'),
										ok: txtConfirm,
										cancel: txtCancel,
										inputs: inputsHtml,
										clbOk: function (vals) {
											$.each(vals, function () {
												textareas.filter('[name*="' + this.name + 's"]').val(this.value);
											});
											changed();
										}
									});
								} else {
									var input = $li.find('textarea[name*="titles"]');
									$.nmConfirm({
										text: composer.data('slideshowtitle'),
										ok: txtConfirm,
										cancel: txtCancel,
										input: 'text',
										inputValue: input.val(),
										clbOk: function (val) {
											input.val(val);
											$li.find('.nyroCmsSlideshow_thumb').children('img').attr('alt', val);
											changed();
										}
									});
								}
							})
							.find('.composableSlideshowUpload').nyroPlupload(myPluploadOptions);

						if (!$b.is('.noChangeMedia')) {
							var addButton = $('<a href="#" class="composableSlideshowUpload">' + composer.data('addphoto') + '</a>').appendTo(big),
								myPluploadOptionsAdd = $.extend(true, {}, myPluploadOptions);

							myPluploadOptionsAdd.texts.browse = composer.data('addphoto');
							myPluploadOptionsAdd.multi_selection = true;

							myPluploadOptionsAdd.events.FileUploaded = function (up, file, data) {
								var $data = $.parseJSON(data.response),
									htmlNew = '<li>';
								htmlNew += '<a href="' + $data.resized + '" class="nyroCmsSlideshow_thumb"><img src="' + $data.resized2 + '" alt="" /></a>';
								htmlNew += '<a href="#" class="composableSlideshowUpload">Upload</a><a href="#" class="composableSlideshowDrag">' + getIcon('drag') + '</a><a href="#" class="composableSlideshowEdit">' + getIcon('pencil') + '</a><a href="#" class="composableSlideshowDelete">' + getIcon('delete') + '</a>';
								htmlNew += '<textarea name="contents[' + nb + '][ids][]">img-' + Date.now() + '</textarea>';
								htmlNew += '<textarea name="contents[' + nb + '][images][]">' + $data.file + '</textarea>';
								htmlNew += '<textarea name="contents[' + nb + '][titles][]"></textarea>';
								if (multipleFields && multipleFields.length) {
									$.each(multipleFields, function () {
										htmlNew += '<textarea name="contents[' + nb + '][' + this + 's][]"></textarea>';
									});
								}
								htmlNew += '<textarea name="contents[' + nb + '][deletes][]"></textarea>';
								htmlNew += '</li>';
								var $li = $(htmlNew).appendTo(nav).find('.composableSlideshowUpload').nyroPlupload(myPluploadOptions).end();
								if (nbLi == 0) {
									me.closest('.nyroCmsSlideshow').trigger('slideshowShow', [$li]);
									nbLi = 1;
								} else {
									me.closest('.nyroCmsSlideshow').trigger('slideshowStartTimer');
								}
								me.trigger('composableSlideshowAdded', [$li]);
								changed();
							};
							addButton.nyroPlupload(myPluploadOptionsAdd);

							nav
								.sortable({
									items: 'li:not(.composableSlideshowAdd)',
									handle: '.composableSlideshowDrag',
									placeholder: 'ui-state-highlight',
									stop: function () {
										changed();
									}
								});
						}

						if (nbLi == 0) {
							big.find('img').attr('src', placehold + sizebig);
						}

						if (!parent.is('#composer') && $.fn.extend.slideshow) {
							me.closest('.nyroCmsSlideshow').slideshow();
						}
						me
							.trigger('composableSlideshowInited')
							.addClass('composableSlideshowInited');
					});
				if (window.svg4everybody) {
					window.svg4everybody();
				}
			},
			curAdd = 0,
			cacheBlock = {},
			addBlock = function (url, inserter) {
				if (!cacheBlock[url]) {
					$.ajax({
						url: url
					}).done(function (data) {
						cacheBlock[url] = data;
						addBlock(url, inserter);
					});
				} else {
					var ident = 'new-' + curAdd,
						html = $(
							cacheBlock[url]
							.replace(/--NEW--/g, ident)
							.replace(/--ID--/g, Date.now())
						);

					if (inserter) {
						inserter(html);
					} else {
						cont.append(html);
					}

					initComposable(html);
					html.trigger('composableAddedBlock');
					changed();

					curAdd++;
					avlBlocksInput.prop('checked', false);
				}
			};

		simpleOptions.setup = function (ed) {
			ed.on('change', changed);
		};
		htmlOptions.setup = function (ed) {
			ed.on('change', changed);
		};

		composer
			.find('input[name="theme"]').on('change', function () {
				var me = $(this);
				if (me.is(':checked')) {
					var val = me.val();
					if (val.length == 0) {
						val = me.data('parent');
					}
					cont.attr('class', 'composer composer_' + val);
					themeDemo.attr('class', 'bg_theme bg_' + val);
					changed();
					me.closest('.select').toggleClass('opened');
				}
			}).end()
			.on('click', '.composerNavConfirm', function (e) {
				if (hasChanged) {
					e.preventDefault();
					var me = $(this);
					$.nmConfirm({
						text: me.data('confirm'),
						ok: txtConfirm,
						cancel: txtCancel,
						clbOk: function () {
							document.location.href = me.attr('href');
						}
					});
				}
			})
			.on('composerChanged', function () {
				changed();
			})
			.on('composerInit', function (e, parent) {
				initComposable(parent);
			})
			.on('submit', function () {
				main.find('.composableSimple, .composableHtml').each(function () {
					var me = $(this);
					main.find('textarea#' + me.data('name')).val(me.html());
				});
			})
			.on('click', '.composerDelete', function (e) {
				e.preventDefault();
				var me = $(this);
				$.nmConfirm({
					text: composer.data('deleteblock'),
					ok: txtConfirm,
					cancel: txtCancel,
					clbOk: function () {
						var block = me.closest('.composerBlock');
						block.fadeOut(function () {
							block.append('<input type="hidden" name="contentsDel[' + block.data('nb') + ']" value="1" />');
							composer.trigger('blockRemoved', [block]);
						});
						changed();
					}
				});
			})
			.on('click', '.cancel', function (e) {
				if (hasChanged) {
					e.preventDefault();
					var me = $(this);
					$.nmConfirm({
						text: me.data('confirm'),
						ok: txtConfirm,
						cancel: txtCancel,
						clbOk: function () {
							document.location.href = me.attr('href');
						}
					});
				}
			});

		if (!composer.is('.composerNoDrag') && !$b.is('.noChangeStructure')) {
			cont.sortable({
				items: '.composerBlock',
				handle: '.composerDrag',
				placeholder: 'ui-state-highlight',
				stop: function (e, ui) {
					if (ui.item.is('a')) {
						addBlock(ui.item.attr('href'), function (html) {
							html.insertAfter(ui.item);
							ui.item.remove();
						});
					} else {
						changed();
					}
				}
			});
		}

		composer
			.find('#availableBlocks a.availableBlock')
			.on('click', function (e) {
				e.preventDefault();
				addBlock($(this).attr('href'));
			})
			.draggable({
				revert: true,
				connectToSortable: cont,
				helper: function () {
					return $(this).clone();
				}
			});

		initComposable(main);
	}

});