(function ($, app) {
"use strict";
	function SettingsPage() {
		this.$obj = this;
		return this.$obj;
	}
	
	SettingsPage.prototype.init = function () {
		var _this = this.$obj;
		_this.isPro = AFSW_DATA.isPro == '1';
		_this.langStrings = afswParseJSON($('#afswLangStringsJson').val());
		
		_this.eventsSettingsPage();
		if (typeof(_this.initPro) == 'function') _this.initPro();
	}
	
	SettingsPage.prototype.eventsSettingsPage = function () {
		var _this = this.$obj,
			dRecalc = $('#afswDialogRecalc'),
			dHistory = $('#afswDialogHistory');

		_this.dialogRecalc = dRecalc.dialog({
			position: {my: 'center', at: 'center', of: '.woobewoo-main'},
			maxHeight: 400,
			autoOpen: false,
			width: 600,
			height: 'auto',
			modal: true,
			dialogClass: 'woobewoo-plugin',
			classes: {
				'ui-dialog': 'woobewoo-plugin'
			},
			buttons: [
				{
					text: afswCheckSettings(_this.langStrings, 'btn-run'),
					class: 'button button-secondary',
					click: function() {
						var inCron = dRecalc.find('input[name="in_cron"]').is(':checked');
						$.sendFormAfsw({
							btn: $('#afswBtnRecalc'),
							data: {
								mod: 'indexing', 
								action: 'doProductsIndexing', 
								inCron: inCron ? 1 : 0
							},
						});
						$(this).dialog('close');
					}
				},
				{
					text: afswCheckSettings(_this.langStrings, 'btn-cancel'),
					class: 'button button-minor',
					click: function() {
						$(this).dialog('close');
					}
				}
			],
			create: function( event, ui ) {
				$(this).parent().css('maxWidth', $(window).width()+'px');
			}
		});

		$('#afswBtnRecalc').click(function(){
			_this.dialogRecalc.dialog('open');
			return false;
		});
		
		_this.dialogHistory = dHistory.dialog({
			position: {my: 'center', at: 'center', of: '.woobewoo-main'},
			maxHeight: 400,
			autoOpen: false,
			width: 600,
			height: 'auto',
			modal: true,
			dialogClass: 'woobewoo-plugin',
			classes: {
				'ui-dialog': 'woobewoo-plugin'
			},
			buttons: [
				{
					text: afswCheckSettings(_this.langStrings, 'btn-run'),
					class: 'button button-secondary',
					click: function() {
						var inCron = dRecalc.find('input[name="in_cron"]').is(':checked');
						$.sendFormAfsw({
							btn: $('#afswBtnHistory'),
							data: {
								mod: 'fields', 
								action: 'doClearHistory', 
								inCron: inCron ? 1 : 0
							},
						});
						$(this).dialog('close');
					}
				},
				{
					text: afswCheckSettings(_this.langStrings, 'btn-cancel'),
					class: 'button button-minor',
					click: function() {
						$(this).dialog('close');
					}
				}
			],
			create: function( event, ui ) {
				$(this).parent().css('maxWidth', $(window).width()+'px');
			}
		});
		
		$('#afswBtnHistory').click(function(){
			_this.dialogHistory.dialog('open');
			return false;
		});

		
		$('#afswBtnSave').click(function(){
			$('#afswSettingsForm').submit();
			return false;
		});
		$('#afswSettingsForm').submit(function(){
			$(this).sendFormAfsw({
				btn: $('#afswBtnSave')
			});
			return false;
		});
	}
	
	app.afswSettingsPage = new SettingsPage();

	$(document).ready(function () {
		app.afswSettingsPage.init();
	});

}(window.jQuery, window));
