(function ($, app) {
"use strict";
	function FieldPage() {
		this.$obj = this;
		return this.$obj;
	}
	
	FieldPage.prototype.init = function () {
		var _this = this.$obj;
		_this.fieldWaitLoad = true;
		_this.fieldWaitResponse = false;
		_this.fieldNeedPreview = false;
		_this.isElementorEditMode = typeof isElementorEditMode !== 'undefined' ? isElementorEditMode : 0;
		
		_this.autoWaitLoad = true;
		_this.autoWaitResponse = false;
		_this.autoNeedPreview = false;
		
		_this.formObj = $('#afswEditFieldForm');
		_this.autocompleteList = $('#afswSectionsList');
		_this.scopesList = $('#afswScopesList');
		_this.fieldId = _this.formObj.find('input[name="id"]').val();
		_this.isPro = AFSW_DATA.isPro == '1';
		_this.langStrings = afswParseJSON($('#afswLangStringsJson').val());
		
		_this.eventsFieldPage();
		
		_this.fieldWaitLoad = false;
		_this.autoWaitLoad = false;
		if (typeof(_this.initPro) == 'function') _this.initPro();
		setTimeout(function() {_this.getFieldPreviewAjax(); _this.getAutocompletePreviewAjax();}, 500);
	}
	
	FieldPage.prototype.eventsFieldPage = function () {
		var _this = this.$obj,
			$mainTabs = $('.afsw-main-tabs .button'),
			$mainTabsContent = $('.afsw-main-tab-content > .block-tab'),
			$curTab = $mainTabs.filter('.current'),
			$controls = $('.woobewoo-control-buttons .group-button');
		$mainTabsContent.filter($curTab.attr('href')).addClass('active');

		$mainTabs.on('click', function (e) {
			e.preventDefault();
			var $this = $(this),
				$curTab = $this.attr('href');

			$mainTabsContent.removeClass('active');
			$mainTabs.filter('.current').removeClass('current');
			$this.addClass('current');
			$this.blur();

			var $curTabContent = $mainTabsContent.filter($curTab),
				model = $this.data('model');
			$curTabContent.addClass('active');
			
			if (model == 'field') {
				_this.refreshTabField();
			} else if (model == 'autocomplete') {
				_this.refreshTabAutocomplete();
			}
			app.afswFields.closeAllPopups();
			$mainTabsContent.find('.afsw-viewas-active').each(function(){
				$(this).trigger('click');
			});
		});
		
		//-- Work with title --//
		var titleShell = $('#afswTitleShell'),
			titleText = titleShell.find('.afsw-title-text'),
			titleInput = titleShell.find('input'),
			titleIcon = titleShell.find('i');
		titleShell.on('click', function() {
			titleText.addClass('afswHidden');
			titleInput.removeClass('afswHidden');
			titleIcon.addClass('afswHidden');
		});
		titleInput.on('focusout keypress', function(e) {
			if (e.type == 'focusout' || e.keyCode == 13) {

				var title = $(this).val();
				titleInput.addClass('afswHidden');
				titleText.text(title).removeClass('afswHidden');
				titleIcon.removeClass('afswHidden');
				$.sendFormAfsw({
					data: {
						mod: 'fields',
						action: 'saveFieldTitle',
						id: _this.fieldId,
						title: title
					}
				});
			}
		});
		//-- Work with title --//
		
		//show / hide sections options
		$('.afsw-sections-list .afsw-section-toggle').on('click', function(e){
			e.preventDefault();
			var el = $(this),
				i = el.find('i'),
				wrapper = el.closest('.afsw-sections-list'),
				options = el.closest('.afsw-section').find('.afsw-section-options');

			if (i.hasClass('fa-chevron-down')){
				wrapper.find('.afsw-section-toggle i.fa-chevron-up').each(function() {
					var $this = $(this);
					$this.removeClass('fa-chevron-up').addClass('fa-chevron-down');
					$this.closest('.afsw-section').find('.afsw-section-options').addClass('woobewoo-hidden');
				});
				i.removeClass('fa-chevron-down').addClass('fa-chevron-up');
				options.removeClass('woobewoo-hidden');
			} else {
				i.removeClass('fa-chevron-up').addClass('fa-chevron-down');
				options.addClass('woobewoo-hidden');
			}
		});
		_this.autocompleteList.find('.afsw-section .afsw-section-header input.toggle').on('change', function(){
			_this.saveSectionsOrder();
		});

		//make sections sortable
		var startSectionPosition = null;
		_this.autocompleteList.sortable({
			cursor: 'move',
			axis: 'y',
			placeholder: 'sortable-placeholder',
			stop: function (e, ui) {
				if(ui.item.index() != startSectionPosition) {
					_this.saveSectionsOrder();
					//_this.getAutocompletePreviewAjax();
				}
			},
			start: function (e, ui) {
				startSectionPosition = ui.item.index();
			},
		});
		//make scopes sortable
		var startScopesPosition = null;
		_this.scopesList.sortable({
			cursor: 'move',
			axis: 'y',
			placeholder: 'sortable-placeholder',
			stop: function (e, ui) {
				if(ui.item.index() != startScopesPosition) {
					_this.saveScopesOrder();
					//_this.getAutocompletePreviewAjax();
				}
			},
			start: function (e, ui) {
				startScopesPosition = ui.item.index();
			},
		});

		var windowHeight = $(window).width() > 810 ? $(window).height() * 0.7 : $(window).height() * 0.9;
		_this.formObj.find('#block-tab-field .afsw-scroll-wrapper').slimscroll({height: windowHeight +'px', opacity: 0.2, width: '100%', axis: 'y'});
		_this.formObj.find('#block-tab-autocomplete .afsw-scroll-wrapper').slimscroll({height: windowHeight +'px', opacity: 0.2, width: '100%', axis: 'y'});
		_this.formObj.find('#block-tab-search .afsw-scroll-wrapper').slimscroll({height: windowHeight +'px', opacity: 0.2, width: '100%', axis: 'y'});
		
		_this.formObj.find('#afswFieldOptions').off('change afsw-change', 'select, input').on('change afsw-change', 'select, input', function(e) {
			if($(this).closest('div[data-no-preview="1"]').length == 0) {
			   _this.getFieldPreviewAjax();
			}
		});
		
		_this.formObj.find('#afswAutocompleteOptions').off('change afsw-change', 'select, input').on('change afsw-change', 'select, input', function(e) {
			if($(this).closest('div[data-no-preview="1"]').length == 0) {
			   _this.getAutocompletePreviewAjax();
			}
		});
		
		afswInitMultySelects();
		
		/*$(document).keydown(function(e) {
			if (e.keyCode == 65 && e.ctrlKey) {
				var $multiBlock = $('.woobewoo-chosen').parent();
				if ($multiBlock.length == 1 && $multiBlock.find('.chosen-container-multi').hasClass('chosen-container-active')) {
					var ctrlAttr = $multiBlock.attr('data-ctrl-a') != '1',
						$select = $multiBlock.find('select');
					$select.find('option').prop('selected', ctrlAttr);
					$select.trigger('chosen:updated');
					$multiBlock.attr('data-ctrl-a', ctrlAttr ? '1' : '0');
				}
			}
		});*/
		
		$('#afswBtnSave').click(function(){
			$('#afswEditFieldForm').submit();
				return false;
		});
		_this.formObj.submit(function(){
			_this.beforeSave();
			$(this).sendFormAfsw({
				btn: $('#afswBtnSave')
			});
			return false;
		});
		
		_this.formObj.on('keyup keypress', function(e) {
			var keyCode = e.keyCode || e.which;
			if (keyCode === 13 && !jQuery(e.target).is('textarea')) { 
				e.preventDefault();
				return false;
			}
			if (e.keyCode == 65 && e.ctrlKey) {
				var $multiBlock = $(e.target).closest('.options-value');
				if ($multiBlock.length == 1 && $multiBlock.find('.chosen-container-multi').hasClass('chosen-container-active')) {
					var ctrlAttr = $multiBlock.attr('data-ctrl-a') != '1',
						$select = $multiBlock.find('select');
					$select.find('option').prop('selected', ctrlAttr);
					$select.trigger('chosen:updated');
					$multiBlock.attr('data-ctrl-a', ctrlAttr ? '1' : '0');
				}
			}
		});
		
	}
	FieldPage.prototype.getFieldPreviewAjax = (function (wait) {
		var _this = this.$obj;
		if(_this.fieldWaitLoad) return;
		if(_this.isElementorEditMode) return;

		if(_this.fieldWaitResponse) {
			if(!_this.fieldNeedPreview || wait) {
				_this.fieldNeedPreview = true;
				setTimeout(function() {	_this.getFieldPreviewAjax(true); }, 2000);
			}
			return;
		}
		_this.fieldWaitResponse = true;
		_this.fieldNeedPreview = false;

		$.sendFormAfsw({
			data: {
				mod: 'fields',
				action: 'drawFieldPreview',
				settings: jsonInputsAfsw('#afswFieldOptions'),
				id: _this.fieldId,
			},
			onSuccess: function(res) {
				if (!res.error) {
					var container = $('#afswFieldPreviewWrap');
					container.html(res.html);
					container.find('input').attr('name','');
					$('.afsw-global-block').remove();
					_this.refreshTabField(true);
				}
				_this.fieldWaitResponse = false;
			},
		});

	});
	FieldPage.prototype.saveSectionsOrder = (function (wait) {
		var _this = this.$obj,
			order = [];
		
		_this.autocompleteList.find('.afsw-section').each(function() {
			order.push($(this).attr('data-type'));
		});
		_this.formObj.find('#afswAutocompleteOrder').val(order.join(','));
		
		_this.getAutocompletePreviewAjax();
	});
	FieldPage.prototype.saveScopesOrder = (function (wait) {
		var _this = this.$obj,
			order = [];
		
		_this.scopesList.find('.afsw-section').each(function() {
			order.push($(this).attr('data-type'));
		});
		_this.formObj.find('#afswScopesOrder').val(order.join(','));
	});
	
	FieldPage.prototype.getAutocompletePreviewAjax = (function (wait) {
		var _this = this.$obj;
		if(_this.autoWaitLoad) return;
		if(_this.isElementorEditMode) return;

		if(_this.autoWaitResponse) {
			if(!_this.autoNeedPreview || wait) {
				_this.autoNeedPreview = true;
				setTimeout(function() {	_this.getAutocompletePreviewAjax(true); }, 2000);
			}
			return;
		}
		_this.autoWaitResponse = true;
		_this.autoNeedPreview = false;

		$.sendFormAfsw({
			data: {
				mod: 'fields',
				action: 'drawAutocompletePreview',
				//field: jsonInputsAfsw('#afswFieldOptions'),
				settings: jsonInputsAfsw('#afswAutocompleteOptions'),
				id: _this.fieldId,
			},
			onSuccess: function(res) {
				if (!res.error) {
					var container = $('#afswAutocompletePreviewWrap');
					container.html(res.html);
					container.find('input').attr('name','');
					var width = _this.formObj.find('#afswFieldWidth').val();
					if (width.length) {
						var units = _this.formObj.find('#afswFieldWidthUnits').val();
						if (units != '%') units = 'px';
						width += units;
						container.find('.afsw-autocomplete-preview').css({'width': width, 'max-width': width});
					}
					_this.refreshTabAutocomplete(container);
				}
				_this.autoWaitResponse = false;
			},
		});
	});
	
	FieldPage.prototype.refreshTabField = function (needInit) {
		var _this = this.$obj;
		_this.formObj.find('#afswFieldPreviewWrap').slimscroll({
			height: '150px', 
			width: Math.round(_this.formObj.find('#afswFieldPreview').width()-5)+'px', 
			opacity: 0.2, 
			axis: 'x'
		});
		if (needInit) app.afswFields.init();
		else {
			//app.afswFields.setStyles();
			if (typeof(app.afswFields.initPro) == 'function') app.afswFields.initPro();
			app.afswFields.displayFields();
		}
	}
	FieldPage.prototype.refreshTabAutocomplete = function (container) {
		var _this = this.$obj,
			$preview = _this.formObj.find('#afswAutocompletePreview'),
			windowHeight = $(window).width() > 810 ? $(window).height() * 0.65 : $(window).height() * 0.9;

		_this.formObj.find('#afswAutocompletePreviewWrap').slimscroll({
			height: windowHeight+'px', 
			width: 'auto',//Math.round($preview.outerWidth(true)-15)+'px', 
			opacity: 0.2, 
			axis: 'both'
		});
	}
	
	FieldPage.prototype.beforeSave = function () {
		var _this = this.$obj;
		_this.saveSectionsOrder();
		_this.saveScopesOrder();
		if (typeof(_this.beforeSavePro) == 'function') _this.beforeSavePro();
	}
	
	app.afswEditFieldPage = new FieldPage();

	$(document).ready(function () {
		app.afswEditFieldPage.init();
	});

}(window.jQuery, window));
