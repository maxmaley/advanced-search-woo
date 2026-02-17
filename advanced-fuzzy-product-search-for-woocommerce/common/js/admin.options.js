"use strict";
var afswAdminFormChanged = [];
window.onbeforeunload = function(){
	// If there are at lease one unsaved form - show message for confirnation for page leave
	if(afswAdminFormChanged.length)
		return 'Some changes were not-saved. Are you sure you want to leave?';
};
jQuery(document).ready(function(){
	if(typeof(afswActiveTab) != 'undefined' && afswActiveTab != 'main_page' && jQuery('#toplevel_page_afsw-comparison-slider').hasClass('wp-has-current-submenu')) {
		var subMenus = jQuery('#toplevel_page_afsw-comparison-slider').find('.wp-submenu li');
		subMenus.removeClass('current').each(function(){
			if(jQuery(this).find('a[href$="&tab='+ afswActiveTab+ '"]').size()) {
				jQuery(this).addClass('current');
			}
		});
	}

	afswInitSettingsParents();
		
	afswInitStickyItem();

	jQuery('.navigation-bar').on('click', function() {
		var navMenu = jQuery('.woobewoo-navigation');

		if (navMenu.hasClass('woobewoo-navigation-show')) navMenu.removeClass('woobewoo-navigation-show');
		else navMenu.addClass('woobewoo-navigation-show');		
	});
	
	afswInitTooltips();
	jQuery(document.body).on('changeTooltips', function (e) {
		afswInitTooltips(e.target);
	});
	afswInitColorPicker();
	jQuery('.woobewoo-panel').on('click focus', '.woobewoo-shortcode', function(e) {
		e.preventDefault();
		this.setSelectionRange(0, this.value.length);
	});
	jQuery('.woobewoo-namefile').disableSelection();
	jQuery('.woobewoo-inputfile input').on('change', function(e) {
		e.preventDefault();
		jQuery(this).parent('.woobewoo-inputfile').find('.woobewoo-namefile').html(this.files.length ? this.files[0].name : '');
	});

	jQuery('.woobewoo-plugin-loader').css('display', 'none');
	jQuery('.woobewoo-main').css('display', 'block');
	//afswInitMultySelects();
	
	jQuery('.woobewoo-plugin .tooltipstered').removeAttr("title");
});
function afswInitSettingsParents( selector ) {
	var settingsValues = selector ? selector : jQuery('.woobewoo-panel');

	settingsValues.on('change afsw-change', 'input[type="checkbox"]', function () {
		var elem = jQuery(this),
			valueWrapper = elem.closest('.options-value'),
			name = elem.attr('name'),
			block = settingsValues,
			childrens = block.find('.row-options-block[data-parent="' + name + '"], .options-value[data-parent="' + name + '"]');
		if(childrens.length > 0) {
			if(elem.is(':checked') && (valueWrapper.length == 0 || !valueWrapper.hasClass('woobewoo-hidden'))) {
				childrens.removeClass('woobewoo-hidden');
				childrens.find('select,input[type="checkbox"]').trigger('afsw-change');
			} else childrens.addClass('woobewoo-hidden');
		}
	});
	settingsValues.on('change afsw-change', 'select', function () {
		var elem = jQuery(this),
			value = elem.val(),
			hidden = elem.closest('.options-value').hasClass('woobewoo-hidden'),
			name = elem.attr('name'),
			block = settingsValues,
			subOptions = block.find('.row-options-block[data-select="' + name + '"], .options-value[data-select="' + name + '"]');
		if(subOptions.length) {
			subOptions.addClass('woobewoo-hidden');
			if(!hidden) subOptions.filter('[data-select-value*="'+value+'"]').removeClass('woobewoo-hidden');
		}
	});
}
function afswInitMultySelects( selector ) {
	var multySelects = jQuery(selector ? selector : '.woobewoo-panel').find('select.woobewoo-chosen:not(.no-chosen)');
	if (multySelects.length) {
		multySelects.chosen({width: "100%"});
		multySelects.on('change', function (e, info) {
			if (info.selected) {
				var allSelected = this.querySelectorAll('option[selected]'),
					lastSelected = allSelected[allSelected.length - 1],
					selected = this.querySelector(`option[value="${info.selected}"]`);
				selected.setAttribute('selected', '');
				if (lastSelected) lastSelected.insertAdjacentElement('afterEnd', selected);
				else this.insertAdjacentElement('afterbegin', selected);
			} else {
				var removed = this.querySelector(`option[value="${info.deselected}"]`);
				removed.setAttribute('selected', false); // this step is required for Edge
				removed.removeAttribute('selected');
			}
			jQuery(this).trigger('chosen:updated');
		});

	}
}
function afswInitToggleBlocks( selector ) {
	jQuery(selector).off('click', '.afsw-toggle').on('click', '.afsw-toggle', function(e){
		e.preventDefault();
		var el = jQuery(this),
			i = el.find('i'),
			options = el.closest('.afsw-table-row').find('.afsw-toggle-block');

		if (i.hasClass('fa-chevron-down')){
			i.removeClass('fa-chevron-down').addClass('fa-chevron-up');
			options.removeClass('woobewoo-hidden');
		} else {
			i.removeClass('fa-chevron-up').addClass('fa-chevron-down');
			options.addClass('woobewoo-hidden');
		}
	});
}
	
function afswInitTooltips( selector ) {
	var tooltipsterSettings = {
			contentAsHTML: true,
			interactive: true,
			speed: 0,
			delay: 200,
			maxWidth: 450
		},
		findPos = {
			'.woobewoo-tooltip': 'top-left',
			'.woobewoo-tooltip-bottom': 'bottom-left',
			'.woobewoo-tooltip-left': 'left',
			'.woobewoo-tooltip-right': 'right'
		},
		$findIn = selector ? jQuery( selector ) : false;
	for(var k in findPos) {
		if(typeof(k) === 'string') {
			var $tips = $findIn ? $findIn.find( k ) : jQuery( k ).not('.no-tooltip');
			if($tips && $tips.size()) {
				tooltipsterSettings.position = findPos[ k ];
				// Fallback for case if library was not loaded
				if(!$tips.tooltipster) continue;
				$tips.tooltipster( tooltipsterSettings );
			}
		}
	}
	if ($findIn) {
		$findIn.find('.tooltipstered').removeAttr('title');
	}
}
function afswInitColorPicker(selector) {
	var $findIn = selector ? jQuery(selector) : jQuery('.woobewoo-plugin');
	$findIn.find('.woobewoo-color-picker').each(function() {
		var $this = jQuery(this),
			colorArea = $this.find('.woobewoo-color-preview'),
			colorInput = $this.find('.woobewoo-color-input'),
			curColor = colorInput.val(),
			timeoutSet = false;

		colorArea.ColorPicker({
			flat: false,
			onShow: function (colpkr) {
				jQuery(this).ColorPickerSetColor(colorInput.val());
				jQuery(colpkr).fadeIn(500);
				return false;
			},
			onHide: function (colpkr) {
				jQuery(colpkr).fadeOut(500);
				return false;
			},
			onChange: function (hsb, hex, rgb) {
				var self = this;
				curColor = hex;
				if(!timeoutSet) {
					setTimeout(function(){
						timeoutSet = false;
						jQuery(self).find('.colorpicker_submit').trigger('click');
					}, 500);
					timeoutSet = true;
				}
			},
			onSubmit: function(hsb, hex, rgb, el) {
				setColorPickerPreview(colorArea, '#' + curColor);
				colorInput.val('#' + curColor).trigger('change');					
			}
		});
		setColorPickerPreview(colorArea, colorInput.val());
	});
	$findIn.find('.woobewoo-color-input').on('change', function() {
		setColorPickerPreview(jQuery(this).parent().find('.woobewoo-color-preview'), jQuery(this).val());
	});
	function setColorPickerPreview(area, col) {
		area.css({'backgroundColor': col, 'border-color': afswGetColorPickerBorder(col)});
	}
}
function afswInitCheckAll(elem, preName) {
	if (typeof preName == 'undefined') var preName = 'afswCheck';
	var main = elem.find('.' + preName + 'All');
	if (main.length) {
		main.on('change', function(e) {
			e.preventDefault();
			elem.find('.' + preName + 'One').prop('checked', jQuery(this).is(':checked'));
		});
		elem.on('change', '.' + preName + 'One', function(e){
			e.preventDefault();
			if (!jQuery(this).is(':checked')) {
				main.prop('checked', false);
			}
		});
	}
}
function changeAdminFormAfsw(formId) {
	if(jQuery.inArray(formId, afswAdminFormChanged) == -1)
		afswAdminFormChanged.push(formId);
}
function adminFormSavedAfsw(formId) {
	if(afswAdminFormChanged.length) {
		for(var i in afswAdminFormChanged) {
			if(afswAdminFormChanged[i] == formId) {
				afswAdminFormChanged.pop(i);
			}
		}
	}
}
function checkAdminFormSaved() {
	if(afswAdminFormChanged.length) {
		if(!confirm('Some changes were not-saved. Are you sure you want to leave?')) {
			return false;
		}
		afswAdminFormChanged = [];	// Clear unsaved forms array - if user wanted to do this
	}
	return true;
}
function isAdminFormChanged(formId) {
	if(afswAdminFormChanged.length) {
		for(var i in afswAdminFormChanged) {
			if(afswAdminFormChanged[i] == formId) {
				return true;
			}
		}
	}
	return false;
}
/*Some items should be always on users screen*/
function afswInitStickyItem() {
	jQuery(window).scroll(function(){
		var stickiItemsSelectors = ['.woobewoo-sticky']
		,	elementsUsePaddingNext = ['.woobewoo-bar']	// For example - if we stick row - then all other should not offest to top after we will place element as fixed
		,	wpTollbarHeight = 32
		,	wndScrollTop = jQuery(window).scrollTop() + wpTollbarHeight
		,	footer = jQuery('.afswAdminFooterShell')
		,	footerHeight = footer && footer.size() ? footer.height() : 0
		,	docHeight = jQuery(document).height()
		,	wasSticking = false
		,	wasUnSticking = false;
		for(var i = 0; i < stickiItemsSelectors.length; i++) {
			jQuery(stickiItemsSelectors[ i ]).each(function(){
				var element = jQuery(this);
				if(element && element.size() && !element.hasClass('sticky-ignore')) {
					var scrollMinPos = element.offset().top
					,	prevScrollMinPos = parseInt(element.data('scrollMinPos'))
					,	useNextElementPadding = toeInArrayAfsw(stickiItemsSelectors[ i ], elementsUsePaddingNext) || element.hasClass('sticky-padd-next')
					,	currentScrollTop = wndScrollTop
					,	calcPrevHeight = element.data('prev-height')
					,	currentBorderHeight = wpTollbarHeight
					,	usePrevHeight = 0;
					if(calcPrevHeight) {
						usePrevHeight = jQuery(calcPrevHeight).outerHeight();
						currentBorderHeight += usePrevHeight;
					}
					if(currentScrollTop > scrollMinPos && !element.hasClass('woobewoo-sticky-active')) {	// Start sticking
						if(element.hasClass('sticky-save-width')) {
							element.width( element.width() );
						}
						element.addClass('woobewoo-sticky-active').data('scrollMinPos', scrollMinPos).css({
							'top': currentBorderHeight
						});
						if(useNextElementPadding) {
							var nextElement = element.next();
							if(nextElement && nextElement.size()) {
								nextElement.data('prevPaddingTop', nextElement.css('padding-top'));
								var addToNextPadding = parseInt(element.data('next-padding-add'));
								addToNextPadding = addToNextPadding ? addToNextPadding : 0;
								nextElement.css({
									'padding-top': (element.hasClass('sticky-outer-height') ? element.outerHeight() : element.height()) + usePrevHeight + addToNextPadding
								});
							}
						}
						wasSticking = true;
						element.trigger('startSticky');
					} else if(!isNaN(prevScrollMinPos) && currentScrollTop <= prevScrollMinPos) {	// Stop sticking
						element.removeClass('woobewoo-sticky-active').data('scrollMinPos', 0).css({
							'top': 0
						});
						if(element.hasClass('sticky-save-width')) {
							if(element.hasClass('sticky-base-width-auto')) {
								element.css('width', 'auto');
							}
						}
						if(useNextElementPadding) {
							var nextElement = element.next();
							if(nextElement && nextElement.size()) {
								var nextPrevPaddingTop = parseInt(nextElement.data('prevPaddingTop'));
								if(isNaN(nextPrevPaddingTop))
									nextPrevPaddingTop = 0;
								nextElement.css({
									'padding-top': nextPrevPaddingTop
								});
							}
						}
						element.trigger('stopSticky');
						wasUnSticking = true;
					} else {	// Check new stick position
						if(element.hasClass('woobewoo-sticky-active')) {
							if(footerHeight) {
								var elementHeight = element.height()
								,	heightCorrection = 32
								,	topDiff = docHeight - footerHeight - (currentScrollTop + elementHeight + heightCorrection);
								if(topDiff < 0) {
									element.css({
										'top': currentBorderHeight + topDiff
									});
								} else {
									element.css({
										'top': currentBorderHeight
									});
								}
							}
							// If at least on element is still sticking - count it as all is working
							wasSticking = wasUnSticking = false;
						}
					}
				}
			});
		}
	});
}
function afswGetTxtEditorVal(id) {
	if(typeof(tinyMCE) !== 'undefined' 
		&& tinyMCE.get( id ) 
		&& !jQuery('#'+ id).is(':visible') 
		&& tinyMCE.get( id ).getDoc 
		&& typeof(tinyMCE.get( id ).getDoc) == 'function' 
		&& tinyMCE.get( id ).getDoc()
	)
		return tinyMCE.get( id ).getContent();
	else
		return jQuery('#'+ id).val();
}
function afswSetTxtEditorVal(id, content) {
	if(typeof(tinyMCE) !== 'undefined' 
		&& tinyMCE 
		&& tinyMCE.get( id ) 
		&& !jQuery('#'+ id).is(':visible')
		&& tinyMCE.get( id ).getDoc 
		&& typeof(tinyMCE.get( id ).getDoc) == 'function' 
		&& tinyMCE.get( id ).getDoc()
	)
		tinyMCE.get( id ).setContent(content);
	else
		jQuery('#'+ id).val( content );
}

function prepareToPlotDate(data) {
	if(typeof(data) === 'string') {
		if(data) {
			data = afswStrReplace(data, '/', '-');
			return (new Date(data)).getTime();
		}
	}
	return data;
}
function afswInitPlugNotices() {
	var $notices = jQuery('.woobewoo-admin-notice');
	if($notices && $notices.size()) {
		$notices.each(function(){
			jQuery(this).find('.notice-dismiss').click(function(){
				var $notice = jQuery(this).parents('.woobewoo-admin-notice');
				if(!$notice.data('stats-sent')) {
					// User closed this message - that is his choise, let's respect this and save it's saved status
					jQuery.sendFormAfsw({
						data: {mod: 'adminmenu', action: 'addNoticeAction', code: $notice.data('code'), choice: 'hide'}
					});
				}
			});
			jQuery(this).find('[data-statistic-code]').click(function(){
				var href = jQuery(this).attr('href')
				,	$notice = jQuery(this).parents('.woobewoo-admin-notice');
				jQuery.sendFormAfsw({
					data: {mod: 'adminmenu', action: 'addNoticeAction', code: $notice.data('code'), choice: jQuery(this).data('statistic-code')}
				});
				$notice.data('stats-sent', 1).find('.notice-dismiss').trigger('click');
				if(!href || href === '' || href === '#')
					return false;
			});
		});
	}
}
