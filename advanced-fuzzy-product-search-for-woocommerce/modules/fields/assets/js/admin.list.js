(function($) {
	"use strict";
	$(document).ready(function () {
		var afswFields = $('#afswFieldsList'),
			settings = afswParseJSON(afswFields.attr('data-settings')),
			isPro = AFSW_DATA.isPro == '1',
			url = typeof(ajaxurl) == 'undefined' || typeof(ajaxurl) !== 'string' ? AFSW_DATA.ajaxurl : ajaxurl;
		$.fn.dataTable.ext.classes.sPageButton = 'button button-small woobewoo-paginate';
		$.fn.dataTable.ext.classes.sLengthSelect = 'woobewoo-flat-input';
		
		var table = afswFields.DataTable({
			serverSide: true,
			processing: true,
			ajax: {
				'url': url + '?mod=fields&action=getListForTbl&pl=afsw&reqType=ajax',
				'type': 'POST',
			},
			lengthChange: true,
			lengthMenu: [ [10, 20, 40, -1], [10, 20, 40, "All"] ],
			paging: true,
			dom: 'B<"pull-right"fl>rtip',
			responsive: {details: {display: $.fn.dataTable.Responsive.display.childRowImmediate, type: ''}},
			autoWidth: false,
			buttons: [
				{
					text: '<i class="fa fa-fw fa-trash-o"></i>' + afswCheckSettings(settings, 'btn-delete'),
					className: 'button button-small button-secondary disabled afsw-group-delete',
					action: function (e, dt, node, config) {
						var ids = [];
						afswFields.find('.afswCheckOne:checked').each(function() {
							ids.push($(this).attr('data-id'));

						});
						if (ids.length && confirm(afswStrReplace(afswCheckSettings(settings, 'remove-confirm'), '%s', ids.length))) {
							$.sendFormAfsw({
							btn: this,
							data: {mod: 'fields', action: 'removeGroup', ids: ids},
								onSuccess: function(res) {
									if (!res.error) {
										table.ajax.reload();
									}
								}
							});
						}
					}
				},
				{
					text: '<i class="fa fa-fw fa-upload"></i>' + afswCheckSettings(settings, 'btn-export'),
					className: 'button button-small disabled button-secondary afsw-group-export' + (isPro ? '' : ' woobewoo-show-pro'),
					action: function (e, dt, node, config) {
						var ids = [];
						afswFields.find('.afswCheckOne:checked').each(function() {
							ids.push($(this).attr('data-id'));

						});
						if (ids.length) {
							$.sendFormAfsw({
							btn: this,
							data: {mod: 'fieldspro', action: 'generateExportUrl', ids: ids.join(',')},
								onSuccess: function(res) {
									if (!res.error && res.url) {
										window.location.href = res.url;
									}
								}
							});
						}
					}
				},
				{
					text: '<i class="fa fa-fw fa-download"></i>' + afswCheckSettings(settings, 'btn-import'),
					className: 'button button-small button-secondary afsw-group-import' + (isPro ? '' : ' woobewoo-show-pro disabled'),
					action: function (e, dt, node, config) {
						$importDialog.dialog('open');
					}	
				},
				{
					text: '<i class="fa fa-fw fa-plus-circle"></i>' + afswCheckSettings(settings, 'btn-add'),
					className: 'button button-small afsw-add-table',
					action: function (e, dt, node, config) {
						e.preventDefault();
						showAddDialog();
					}
				}
			],
			columnDefs: [
				{
					className: "dt-left",
					width: "20px",
					targets: 0
				},
				{
					width: "20px",
					targets: 1
				},
				{
					"orderable": false,
					targets: [0, 3, 4]
				}
			],
			order: [[ 1, 'desc' ]],
			language: {
				emptyTable: afswCheckSettings(settings, 'emptyTable'),
				paginate: {
					next: '<i class="fa fa-fw fa-angle-right">',
					previous: '<i class="fa fa-fw fa-angle-left">'  
				},
				lengthMenu: afswCheckSettings(settings, 'lengthMenu') + ' _MENU_',
				info: afswCheckSettings(settings, 'info') + ' _START_ to _END_ of _TOTAL_',
				search: '_INPUT_'
			},
			fnDrawCallback : function() {
				$('#afswFieldsList_wrapper .dataTables_paginate')[0].style.display = $('#afswFieldsList_wrapper .dataTables_paginate  span .woobewoo-paginate').size() > 1 ? 'block' : 'none';
				afswInitTooltips('#afswFieldsList');
				afswFields.find('.afswCheckAll').prop('checked', false);
			}
		});
		afswInitCheckAll(afswFields);
		var groupButtons = $('.afsw-group-delete, .afsw-group-export:not(.woobewoo-show-pro)');
		afswFields.on('change', '.afswCheckAll, .afswCheckOne', function(e) {
			if (afswFields.find('.afswCheckOne:checked').length) {
				groupButtons.removeClass('disabled');
			} else {
				groupButtons.addClass('disabled');
			}
		});

		$('#afswFieldsList').on('click', '.woobewoo-list-actions i', function() {
			var $this = $(this);
			if ($this.hasClass('afsw-edit')) {
				document.location.href = $this.closest('tr').find('.afsw-edit-link').attr('href') + '&block=field';
			} else if ($this.hasClass('afsw-options')) {
				document.location.href = $this.closest('tr').find('.afsw-edit-link').attr('href');
			}
			else if ($this.hasClass('afsw-clone')) {
				$.sendFormAfsw({
					icon: $this,
					data: {
						mod: 'fields',
						action: 'cloneField',
						fieldId: $this.closest('.woobewoo-list-actions').attr('data-id'),
					},
					onSuccess: function(res) {
						if (!res.error && res.data && res.data.link) {
							setTimeout(function() {
								window.open(res.data.link ,'_blank');
							}, 500);
						}
					}
				});
			} else if ($this.hasClass('afsw-statistics')) {
				$.sendFormAfsw({
					icon: $this,
					data: {
						mod: 'fieldspro',
						action: 'enableStatistics',
						stat: $this.hasClass('afsw-action-on') ? 0 : 1,
						fieldId: $this.closest('.woobewoo-list-actions').attr('data-id'),
					},
					onSuccess: function(res) {
						if (!res.error) {
							setTimeout(function() {
								table.ajax.reload();
							}, 500);
						}
					}
				});
			}
		});
		$('body').on('click', '.tooltipster-content button', function () {
			var $this = $(this),
				content = $this.closest('.tooltipster-content');
			if ($this.hasClass('afsw-delete')) {
				var id = content.find('.afswHidden').html();
				if (id.length) {
					$.sendFormAfsw({
						icon: $('#afswFieldsList').find('.woobewoo-list-actions[data-id="' + id +'"] i.afsw-delete'),
						data: {
							mod: 'fields',
							action: 'removeField',
							fieldId: id
						},
						onSuccess: function(res) {
							if (!res.error) {
								setTimeout(function() {
									table.ajax.reload();
								}, 500);
							}
						}
					});
				}
			}
			content.parent().removeClass('tooltipster-fade-show');
			
		});
		var dImport = $('#afswDialogMigration'),
			$importDialog = dImport.dialog({
				position: {my: 'center', at: 'center', of: '.woobewoo-main'},
				maxHeight: 700,
				resizable: false,
				height: 'auto',
				width: 600,
				modal: true,
				autoOpen: false,
				dialogClass: 'woobewoo-plugin',
				classes: {
					'ui-dialog': 'woobewoo-plugin'
				},
				buttons: [
					{
						text: dImport.data('import'),
						class: 'button button-secondary',
						click: function(e) {
							var form = new FormData(),
								$input = $(this).find('input[type="file"]');

							if ($input.val()) {
								form.append('sql_file', $input.get(0).files[0]);
								form.append('mod', 'fieldspro');
								form.append('action', 'importFieldsData');
								
								$.sendFormAfsw({
									btn: $('.afsw-group-import'),
									form: form,
									ajax: {
										cache: false,
										contentType: false,
										processData: false
									},
									onSuccess: function(res) {
										if (!res.error) {
											setTimeout(function() {
												table.ajax.reload();
											}, 500);
										}
									}
								});
							}
							$(this).dialog('close');
						}
					},
					{
						text: dImport.data('cancel'),
						class: 'button button-secondary',
						click: function() {
							$(this).dialog('close');
						}
					}
				],
				create: function( event, ui ) {
					$(this).parent().css('maxWidth', $(window).width()+'px');
				}
			});

		if( jQuery('#afswAddDialog').length ) {
			var $error = jQuery('#formError'),
				$input = jQuery('#addDialog_title'),
				saveText = '',
				$dialog = jQuery('#afswAddDialog').dialog({
					position: {my: 'center', at: 'center', of: '.woobewoo-main'},
					autoOpen: false,
					width: 480,
					height: 'auto',
					modal: true,
					dialogClass: 'woobewoo-plugin',
					open: function () {
						jQuery('#afswAddDialog').keypress(function(e) {
							if (e.keyCode == $.ui.keyCode.ENTER) {
								e.preventDefault();
								jQuery('.afswDialogSave').click();
							}
						});
					},
					close: function () {
						window.location.hash = '';
					},
					buttons: {
						Save: function (event) {
							$error.fadeOut();
							var $btn = jQuery(this).closest(".ui-dialog").find('.afswDialogSave');
							saveText = $btn.html();
							$btn.prop('disabled',true).attr('disabled',true);
							$btn.html('<i class="fa fa-refresh afswIconRotate360" aria-hidden="true"></i>');
							jQuery.sendFormAfsw({
								data: {
									mod: 'fields',
									action: 'createField',
									title: $input.val(),
									afswNonce: window.afswNonce
								},
								onSuccess: function(res) {
									if(!res.error) {
										var currentUrl = window.location.href;
										if (res.edit_link && currentUrl !== res.edit_link) {
											toeRedirect(res.edit_link);
										}
										$btn.prop('disabled',false).attr('disabled',false);
										$dialog.dialog('close');
										$btn.html(saveText);
									}else{
										$error.find('p').text(res.errors.title);
										$error.fadeIn();
									}
								}
							});
						}
					},
					create:function () {
						jQuery(this).closest(".ui-dialog").addClass('woobewoo-plugin').find(".ui-dialog-buttonset button").first().addClass("afswDialogSave");
					}
				});

			$input.on('focus', function () {
				$error.fadeOut();
			});

		}

		if (window.location.hash === '#afswadd') {
			// To prevent error if data not loaded completely
			showAddDialog();
		}

		jQuery('.woobewoo-table-list').on('click', 'a[href="#afswadd"]', function(){
			showAddDialog();
		});

		function showAddDialog() {
			setTimeout(function() {
				if(typeof $dialog !== 'undefined') {
					$dialog.dialog('open');
				}
			}, 200);
		}
	});
})(jQuery);
