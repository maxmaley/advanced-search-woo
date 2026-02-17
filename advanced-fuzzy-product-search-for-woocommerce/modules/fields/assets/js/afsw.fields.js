(function ($, app) {
"use strict";
	function AfswFields() {
		this.$obj = this;
		return this.$obj;
	}
	
	AfswFields.prototype.init = (function () {
		var _this = this.$obj;
		_this.isPro = AFSW_DATA.isPro == '1';
		_this.isPreview = $('#afswAutocompletePreview').length>0;
		_this.autoPopups = {};
		_this.stopAutocomplete = false;
		_this.autoWaitResponse = false;
		_this.existsAutoWait = false;
		_this.defaultProductSelector = 'ul.products';
		app.afswNewUrl = '';
		_this.runCustomJs();
		_this.createJsHook('afswBeforeInit');

		_this.setCurrentLocation();
		_this.eventsFields();
		if (typeof(_this.initPro) == 'function') _this.initPro();
		_this.displayFields();
		
		_this.createJsHook('afswAfterInit');
	});
	AfswFields.prototype.displayFields = (function ($wrapper) {
		var _this = this.$obj;
		if (typeof(_this.setStylesPro) == 'function') _this.setStylesPro($wrapper);
		else _this.setStyles($wrapper);
	});
	
	AfswFields.prototype.createJsHook = (function (name) {
		// callexample: document.addEventListener('afswBeforeInit', function(event) {console.log('Custom js');});
		
		var customEvent = document.createEvent('Event');
		customEvent.initEvent(name, false, true); 
		document.dispatchEvent(customEvent);
	});
	
	AfswFields.prototype.runCustomJs = (function () {
		var _this = this.$obj;
		$('.afsw-search-wrapper').each(function () {
			var jsCodeStr = $(this).attr('data-custom-js');
			if (jsCodeStr && jsCodeStr.length > 0){
				try {
					eval(jsCodeStr);
				}catch(e) {
					console.log(e);
				}

			}
		});
	});
	
	AfswFields.prototype.setCurrentLocation = (function() {
		app.afswOldUrl = window.location.href;
		app.afswNewUrl = app.afswOldUrl;
	});
	
	AfswFields.prototype.setStyles = (function ($wrapper) {
		var _this = this.$obj;
		(typeof($wrapper) == 'undefined' ? $('.afsw-search-wrapper') : $wrapper).each(function() {
			var $this = $(this);
			$this.show();
			var sButton = $this.find('.afsw-search-button'),
				wButton = $this.find('.afsw-search-where'),
				bRemove = $this.find('.afsw-search-remove'),
				iWidth = $this.outerWidth(),
				right = sButton.length ? Math.round(iWidth - sButton.position().left) : 0,
				pl = wButton.length ? Math.round(wButton.outerWidth(true) + wButton.position().left + 5) : 10;
			if (pl + right + 25 > iWidth) right = 0;
			$this.find('.afsw-search-input').css({
				'padding-left': pl + 'px', 
				'padding-right': right + 25 + 'px'
			});
			bRemove.css({
				'right': right + 'px'
			});
			$this.find('.afsw-preloader').css({
				'right': right + Math.round(bRemove.outerWidth(true) - 10) + 'px'
			});
			//if (typeof(_this.setStylesPro) == 'function') _this.setStylesPro($this);
		});
	});
	
	AfswFields.prototype.initFloatingField = function($wrapper, $button, top, fixed) {
		var _this = this.$obj;
		$wrapper.parent().attr('data-afsw-base', $wrapper.attr('id'));
		//$wrapper.appendTo('body');
		$wrapper.appendTo('.afsw-global-block');
		$wrapper.addClass('afsw-search-floating');
		$button.attr('data-viewid', $wrapper.attr('data-viewid'));
		if (top) $wrapper.attr('data-auto-top', 1);
		if (fixed) $wrapper.attr('data-auto-fixed', 1);
		$button.off('click').on('click', function(){
			var $this = $(this),
				viewId = $this.attr('data-viewid'),
				$wrapper = $('#afswFieldWrapper-' + viewId);
			if ($this.hasClass('afsw-viewas-active')) {
				$wrapper.hide();
				_this.closeAllPopups();
					$this.removeClass('afsw-viewas-active');
			} else {
				$this.addClass('afsw-viewas-active');
				var position = $this.offset();
				var css = {
					'position': fixed ? 'fixed' : 'absolute',
					'z-index': 10000,
					'left': position.left + 'px',
				};
				
				if (top && fixed) {
					css['bottom'] = (Math.round($this.outerHeight()) + 3) + 'px';
					$wrapper.attr('data-auto-fixed-bottom', (Math.round($this.outerHeight()) + $wrapper.outerHeight() + 3) + 'px');
				}
				else css['top'] = (top ? position.top - $wrapper.outerHeight() - 3 : Math.round($this.outerHeight()) + position.top + 3) + 'px';
					
				$wrapper.css(css);
				_this.setStyles($wrapper);
				var fPosition = $wrapper.offset(),
					fWidth = $wrapper.outerWidth(),
					wWidth = $(window).width();
				if ((fPosition.left + fWidth) > wWidth) {
					$wrapper.css({
						'left': (wWidth - fWidth) + 'px'
					});
				}
			}
		});
	}
	
	AfswFields.prototype.eventsFields = (function () {
		var _this = this.$obj;
		_this.timerAutocomplete = 0;
		//_this.delayAutocomplete = 1000;
		$('<div class="afsw-global-block"><div>').appendTo('body');
		_this.keyCodes = {};
		
		$('.afsw-search-wrapper .afsw-search-input').off('keyup').on('keyup', function(e) {
			if (e.keyCode != undefined && e.key != undefined) _this.keyCodes[e.key] = String.fromCharCode(e.keyCode);
			if (e.keyCode === 27 || e.keyCode === 38 || e.keyCode === 40 || e.keyCode === 13) return;
			var $this = $(this),
				$wrapper = $this.closest('.afsw-search-wrapper'),
				auto = $wrapper.attr('data-autocomplete') == '1',
				len = $this.val().length;
			$wrapper.find('.afsw-search-remove').css({
				'opacity': len ? 1 : 0
			});
			if (!auto) return false;
			if (len && parseInt($wrapper.attr('data-min-chars')) <= len) {
				app.clearTimeout(_this.timerAutocomplete);
				_this.timerAutocomplete = window.setTimeout(function(){
					//$wrapper.find('.afsw-preloader').css({'opacity': 0.5});
					_this.getAutocomplete($wrapper);
				}, $wrapper.attr('data-auto-delay'));
			}
		});
		$('.afsw-search-wrapper .afsw-search-remove').off('click').on('click', function(e) {
			var $this = $(this);
			$(this).closest('.afsw-search-wrapper').find('.afsw-search-input').val('');
			$this.css({'opacity': 0});
			_this.closeAllPopups();
		});
		$('.afsw-search-wrapper .afsw-search-button').off('click').on('click', function(e) {
			var $this = $(this);
			if (_this.isPreview) return false;
			_this.closeAllPopups();
			_this.doSearch($this.closest('.afsw-search-wrapper'));
			
		});
		
		$(document).on('keydown', function(e){
			var autoPopup = $('.afsw-autocomplete-popup.afsw-popup-show');
			if (e.keyCode === 38 || e.keyCode === 40) {
				if (autoPopup.length) {
					var terms = autoPopup.find('.afsw-section-term');
					if (terms.length) {
							var active = terms.filter('.active'),
							index = active.length ? terms.index(active) : -1,
							lastIndex = terms.length - 1;
						index = e.keyCode === 38 ? (index > 0 ? index - 1 : lastIndex) : (index == lastIndex ? 0 : index + 1)
						terms.removeClass('active');
						terms.eq(index).addClass('active');
					}
					e.preventDefault();
					return false;
				}
			} else _this.closeAllPopups();
			
			if (e.keyCode === 13) {
				if (autoPopup.find('.afsw-section-term.active').length) {
					_this.autocompleteSelect(autoPopup.find('.afsw-section-term.active'));
				}
				if ($('.afsw-search-input:focus').length) {
					var $wrapper = $('.afsw-search-input:focus').closest('.afsw-search-wrapper');
					if ($wrapper.attr('data-by-enter') == '1') _this.doSearch($wrapper);
					return false;
				}
			}
		});
		var width = $( window ).width();
		$(window).on('resize', function() {
			if (width !== $(window).width()) {
				_this.displayFields();
			}
		});
		//for woocommerce-blocks (All products and others)
		if (typeof window.wpfFetchHookCreated == 'undefined' || window.wpfFetchHookCreated != 1) {
			window.fetch = new Proxy(window.fetch, {
				apply(fetch, that, args) {
					var url = args.length ? args[0] : '';
					if (typeof url === 'string' && url.length) {
						if (url.indexOf('wp-json/wc/store/') != -1 && url.indexOf('/products?') != -1 && url.indexOf('per_page=') != -1) {
							var s = window.location.search;
							if (s.length) args[0] += s.replace('?','&');
						}
					}
					const result = fetch.apply(that, args);
					return result;
				}
			});
			window.wpfFetchHookCreated = 1;
		}
		
		if (typeof elementor !== 'undefined') {
			if (elementor.$preview.length) {
				elementor.$preview.on('load', function(){
					_this.addElementorPreviewAction();
				});
			}
		}
		
	});
	AfswFields.prototype.getKeyCodes = (function(s) {
		var _this = this.$obj,
			keys = '',
			chars = s.split(''),
			len = s.length,
			c;
		for(var k = 0; k < len; k++) {
			c = chars[k];
			if (_this.keyCodes[c]) {
				keys += _this.keyCodes[c];
			}
		}
		return keys.toLowerCase();
	});
	AfswFields.prototype.getAutocomplete = (function($wrapper, wait) {
		var _this = this.$obj;
		if (_this.isPreview || _this.stopAutocomplete) return false;
		if (!wait && _this.existsAutoWait) return;

		if (_this.autoWaitResponse) {
			_this.existsAutoWait = true;
			setTimeout(function() {	_this.getAutocomplete($wrapper, true); }, 2000);
			return;
		}

		var s = $wrapper.find('.afsw-search-input').val(),
			len = s.length;
		if (s && len && parseInt($wrapper.attr('data-min-chars')) <= len) {
			_this.createJsHook('afswBeforeAutocomplete');
			_this.autoWaitResponse = true;
			var data = {
					mod: 'fields',
					action: 'getAutocomplete',
					search: s,
					codes: _this.getKeyCodes(s),
					id: $wrapper.data('field'),
				};
			if (typeof(_this.getAutocompletePro) == 'function') data = _this.getAutocompletePro(data, $wrapper);
		
			$wrapper.find('.afsw-preloader').css({'opacity': 0.5});
			$.sendFormAfsw({
				data: data,
				onSuccess: function(res) {
					if (!res.error) {
						_this.showAutocompletePopup($wrapper, res.html);
					}
					_this.autoWaitResponse = false;
					_this.existsAutoWait = false;
					_this.createJsHook('afswAutocompleteSuccess');
				},
			});
			return;
		}
		return;
	});
	AfswFields.prototype.showAutocompletePopup = (function ($wrapper, $html) {
		var _this = this.$obj,
			viewId = $wrapper.attr('data-viewid'),
			popup, overlay;
				
		if (_this.autoPopups[viewId]) {
			popup = _this.autoPopups[viewId];
		} else {
			popup = $wrapper.find('.afsw-autocomplete-popup');
			if (popup.length == 0) return;
			//popup.appendTo('body');
			popup.appendTo('.afsw-global-block');
			popup.addClass('afsw-autocomplete-floating');
			_this.autoPopups[viewId] = popup;
			_this.initAutocomplete(popup);
		}
		if (!$html.length && $wrapper.find('.afsw-auto-noresults').length) {
			$html = $wrapper.find('.afsw-auto-noresults').html();
		}
		popup.find('.afsw-autocomplete-content').html($html);
		
		//if (popup.hasClass('afsw-popup-show')) return;
		_this.closeAllPopups();
		$wrapper.find('.afsw-preloader').css({'opacity': 0});
		if (_this.stopAutocomplete) return;
		
		if ($html.length) {
			var position = $wrapper.offset(),
				top = $wrapper.attr('data-auto-top') == '1',
				fixed = $wrapper.attr('data-auto-fixed') == '1',
				css = {
					'left': position.left + 'px',
					'width': Math.round($wrapper.outerWidth()) + 'px'
				};
			if (top && fixed) {
				css['bottom'] = $wrapper.attr('data-auto-fixed-bottom');
				css['position'] = 'fixed!important';
			}
			else css['top'] = Math.round($wrapper.outerHeight()) + position.top + 'px';
			popup.css(css);
			popup.show();
			popup.addClass('afsw-popup-show');
		}
		
	});
	AfswFields.prototype.closeAllPopups = (function () {
		var _this = this.$obj;
		$('.afsw-popup-show').each(function() {
			var popup = $(this);
			if (popup.hasClass('afsw-autocomplete-popup')) _this.hideAutocompletePopup(popup);
		});
	});
	AfswFields.prototype.hideAutocompletePopup = (function (popup) {
		popup.hide();
		popup.removeClass('afsw-popup-show');
		popup.html();
	});
	AfswFields.prototype.initAutocomplete = (function (popup) {
		var _this = this.$obj;
		popup.on('click', '.afsw-term-wrap', function() {
			_this.closeAllPopups();
			_this.autocompleteSelect($(this));
		});
	});
	AfswFields.prototype.autocompleteSelect = (function ($term) {
		var _this = this.$obj,
			$name = $term.find('.afsw-term-name'),
			link = $name.attr('data-link');
		if (link && link.length) {
			$(location).attr('href', link);
		} else {
			var popup = $term.closest('.afsw-autocomplete-popup'),
				$wrapper = $('#afswFieldWrapper-'+popup.attr('data-viewid'));
			if ($wrapper.length) {
				if ($name.attr('data-enter') == '1') {
					_this.doSearch($wrapper);
				} else {
					$wrapper.find('.afsw-search-input').val($name.html());
					if ($wrapper.attr('data-by-enter') == '1') _this.doSearch($wrapper);
				}
			}
		}
	});
	
	AfswFields.prototype.doSearch = (function ($wrapper) {
		var _this = this.$obj,
			$input = $wrapper.find('.afsw-search-input'),
			value = $input.val().trim(),
			leer = !value.length;
		if (leer) {
			return;
		}
		_this.stopAutocomplete = true;
		
		var output = $wrapper.attr('data-output'),
			withAjax = $wrapper.attr('data-ajax') == '1',
			fieldId = $wrapper.attr('data-field'),
			$container = withAjax ? _this.getProductContainer($wrapper.attr('data-output-container')) : false,
			redirectUrl = $wrapper.data('redirect-page'),
			doAjax = false;
			
		_this.setCurrentLocation();
		_this.removePageQString();
		_this.changeUrl('afsw-id', leer ? '' : fieldId);
		_this.changeUrl($input.attr('data-name'), value);
		$wrapper.find('input.afsw-add-args').each(function() {
			var $this = $(this);
			_this.changeUrl($this.attr('data-name'), leer ? '' : $this.val());
		});
		if (typeof(_this.doSearchPro) == 'function') _this.doSearchPro($wrapper);
		
		if (output == 'same_page') {
			doAjax = withAjax;
			redirectUrl = false;
		} else {
			if (redirectUrl) {
				if (window.location.href.split('?')[0] == redirectUrl) doAjax = true;
			}
		}
		if (doAjax && (!withAjax || !$container)) doAjax = false;
		
		if (doAjax) {
			_this.saveHistory();
			_this.createJsHook('afswBeforeAjaxSearch');
			_this.doAjaxSearch($container, $wrapper);
			
		} else {
			var newUrl = app.afswNewUrl;
			if (redirectUrl) {
				var queryString = app.afswNewUrl.split('?')[1] || '';
				if (queryString !== '') {
					newUrl = redirectUrl + '?' + queryString;
				}
			}
			_this.createJsHook('afswBeforeRedirect');
			$(location).attr('href', newUrl);
		}

	});
	
	AfswFields.prototype.getProductContainer = (function(selector, source) {
		var _this = this.$obj,
			$container = false;
		if (typeof selector == 'undefined' || !selector.length) {
			selector = _this.defaultProductSelector;
			var $products = source ? source.find(selector) : $(selector);
			if ($products.length) $container = $products.parent();
		} else {
			selector = (selector.search(/\.|#/) === -1) ? '.' + selector.replace(/(\s+)(\w+)/g, ' .$2')	: selector;
			$container = source ? source.find(selector) : $(selector);
		}

		return $container && $container.length ? $container : false ;
	});
	
	AfswFields.prototype.showSearchLoader = (function($wrapper, $container){
		var $loader = $wrapper.find('.afsw-loader-wrapper');
		if ($loader.length) {
			$container.html($loader.first().clone());
		}
	});
	
	AfswFields.prototype.doAjaxSearch = (function ($container, $wrapper) {
		var _this = this.$obj,
			curUrl = window.location.href,
			container = $wrapper.attr('data-output-container');
		_this.closeAllPopups();
		_this.showSearchLoader($wrapper, $container);
		$.ajax({
			type: "GET",
			url: curUrl,
			cache: false,
			dataType: 'html',
			success: function(data) {
				_this.closeAllPopups();
				if (data && data.length) {
					var $source = _this.getProductContainer($wrapper.attr('data-output-container'), $(data));
					if ($source === false) {
						$source = $wrapper.find('.afsw-noresults');
					}
					
					if ($source.length) {
						$container.html($source.html());
					} else {
						location.reload();
						return;
					}
				}
				_this.stopAutocomplete = false;
				_this.createJsHook('afswSearchSuccess');
    		}
		});

		return false;
	});
	
	AfswFields.prototype.saveHistory = (function () {
		if (history.pushState && app.afswNewUrl != window.afswOldUrl) {
			history.pushState({state: 1, rand: Math.random(), afsw: true}, '', app.afswNewUrl);
			app.afswOldUrl = app.afswNewUrl;
		}
	});
	
	AfswFields.prototype.getUrlParameterByName = (function(slug, searchUrl) {
		slug = slug.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
		var regex = new RegExp("[\\?&]" + slug + "=([^&#]*)"),
			results = regex.exec(searchUrl);
		return results === null ? "" : decodeURIComponent(results[1]);
	});
	
	//Add or modify querystring
	AfswFields.prototype.changeUrl = (function(slug, value) {
		//Get query string filterValue
		var _this = this.$obj,
			parts = window.afswNewUrl.split('?'),
			s = (parts[1] || ''),
			curUrl = {href: window.afswNewUrl, path: parts[0].replace(/#.*$/, ''), search: (s.length ? '?' + s : '')},
			searchUrl = decodeURIComponent(curUrl.search);

		var urlValue = '';
		if (searchUrl.indexOf('?') == -1) {
			urlValue = curUrl.path+(value.length ? '?'+slug+'='+value : '');
		} else {
			//Check for filterSlug in query string, if not present
			if( searchUrl.indexOf('&'+slug+'=') == -1 && searchUrl.indexOf('?'+slug+'=') == -1) {
				urlValue=searchUrl+(value.length ? '&'+slug+'='+value : '');
			//If filterSlug present in query string
			} else {
				var oldValue = _this.getUrlParameterByName(slug, searchUrl);
				if (searchUrl.indexOf('?'+slug+'=')!=-1) {
					urlValue = searchUrl.replace('?'+slug+'='+oldValue,(value.length ? '?'+slug+'='+value : '?'));
				// add existing in url filter with another option
				} else {
					urlValue = searchUrl.replace('&'+slug+'='+oldValue,(value.length ? '&'+slug+'='+value : ''));
				}
			}
			urlValue = curUrl.path + urlValue;
		}
		app.afswNewUrl = encodeURI(urlValue).indexOf('%25') === -1 ? encodeURI(urlValue) : urlValue;
	});
	
	AfswFields.prototype.removePageQString = (function () {
		var _this = this.$obj,
			parts = window.afswNewUrl.split('?'),
			s = (parts[1] || ''),
			curUrl = {href: window.afswNewUrl, path: parts[0].replace(/#.*$/, ''), search: (s.length ? '?' + s : '')},
			path = curUrl.path,
			page = path.indexOf('/page/');
		if (page != -1) {
			window.afswNewUrl = path.substr(0, page + 1) + curUrl.search;
		}
		_this.changeUrl('product-page', '');
		_this.changeUrl('shopPage', '');
	});
	
	AfswFields.prototype.addElementorPreviewAction = (function () {
		var _this = this.$obj;
		if (typeof elementorFrontend !== 'undefined') {
			elementorFrontend.hooks.addAction('frontend/element_ready/woofilters.default', function ($scope) {
				if (elementorFrontend.elements.window && elementorFrontend.elements.window.afswFields) {
					var afswFields = elementorFrontend.elements.window.afswFields;
					//_this.init();
					setTimeout(function() {	afswFields.displayFields(); }, 200);
					/*if (typeof(wpfFrontend.eventsFrontendPro) == 'function') {
						wpfFrontend.eventsFrontendPro();
					}*/
				}
				//elementorFrontend.elements.window.hideFilterLoader($scope.find('.wpfMainWrapper'));
			});
		} else {
			setTimeout(function() {	_this.addElementorPreviewAction(); }, 200);
		}
	});
	
	app.afswFields = new AfswFields();

	$(document).ready(function () {
		app.afswFields.init();
	});
}(window.jQuery, window));
