<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class FieldsViewAfsw extends ViewAfsw {
	protected static $fieldCss = [];
	protected static $fieldFonts = [];
	protected static $excludeFonts = false;
	protected static $showFonts = false;
	protected static $groupSection = false;
	protected static $firstGroup = false;
	protected static $termSeparate = false;
	public $shortcodeArgs = array('post_type');
	
	public function addFieldCss( $selector, $style, $value, $important = false ) {
		if ('font-family' == $style) {
			if (self::$showFonts) {
				$this->addFieldFont($value);
			}
			$value = '"' . $value . '"';
		}
		self::$fieldCss[$selector][$style] = $value . ( '' != $value && $important ? '!important' : '' );
	}
	public function addFieldFont( $font ) {
		if (!in_array($font, self::$fieldFonts) && !in_array($font, self::$excludeFonts)) {
			self::$fieldFonts[] = $font;
		}
	}
	public function resetFieldCss() {
		self::$fieldCss = [];
		self::$fieldFonts = [];
		self::$excludeFonts = DispatcherAfsw::applyFilters('getFontsList', array(), 'standart');
		self::$showFonts = is_array(self::$excludeFonts);
	}
	
	public function renderFieldStyleHtml() {
		$html = '';
		foreach (self::$fieldFonts as $font) {
			$html .= '@import url("//fonts.googleapis.com/css?family=' . str_replace(' ', '+', $font) . '");';
		}
		foreach (self::$fieldCss as $selector => $rules) {
			$html .= $selector . '{';
			foreach ($rules as $name => $value) {
				$html .= $name . ':' . $value . ';';
			}
			$html .= '}';
		}
		return $html;
	}
	
	public function generateCss( $fieldId, $settings ) {
		$this->resetFieldCss();
		$field = UtilsAfsw::getArrayValue($settings, 'field', array(), 2);
		if (!empty($field)) {
			$selector = '.afsw-field-' . $fieldId;
			$width = UtilsAfsw::getArrayValue($field, 'width', '', 1);
			if (!empty($width)) {
				$units = UtilsAfsw::getArrayValue($field, 'width_units');
				if ('%' != $units) {
					$units = 'px';
				}
				$this->addFieldCss($selector, 'width', $width . $units, true);
				$this->addFieldCss($selector, 'max-width', $width . $units, true);
			}
		}
		$autocomplete = UtilsAfsw::getArrayValue($settings, 'autocomplete', array(), 2);
		if (!empty($autocomplete)) {
			$selector = '.afsw-autocomplete-' . $fieldId;
		}
		DispatcherAfsw::doAction('generateCustomCss', $fieldId, $settings);
		return $this->renderFieldStyleHtml() . UtilsAfsw::getArrayValue($settings, 'add_css');
	}
	
	public function getAllPages() {
		global $wpdb;
		$allPages = dbAfsw::get('SELECT ID, post_title FROM ' . $wpdb->posts . " WHERE post_type='page' AND post_status IN ('publish','draft') ORDER BY post_title");
		$pages = array('' => __('Select page for redirect', 'advanced-fuzzy-search'));
		if (!empty($allPages)) {
			foreach ($allPages as $p) {
				$pages[$p['ID']] = $p['post_title'];
			}
		}

		return $pages;
	}
		
	public function showFieldsListAdmin() {
		$assets = AssetsAfsw::_();
		$assets->loadCoreJs();
		$assets->loadDataTables(array('buttons', 'responsive'));
		$assets->loadAdminEndCss();

		$frame = FrameAfsw::_();
		$path = $this->getModule()->getModPath() . 'assets/';
		$frame->addScript('afsw-fields-list', $path . 'js/admin.list.js');
		$frame->addStyle('afsw-fields-list', $path . 'css/admin.list.css');

		$settings = array(
			'emptyTable' => esc_html__('You have no Fields for now.', 'advanced-fuzzy-search') . ' <a href="#afswadd">' . esc_html__('Create', 'publish-your-table') . '</a> ' . esc_html__('your first Field', 'publish-your-table') . '!',
			'lengthMenu' => esc_html__('Show', 'advanced-fuzzy-search'),
			'info' => esc_html__('Showing', 'advanced-fuzzy-search'),
			'btn-delete' => esc_html__('Delete selected', 'advanced-fuzzy-search'),
			'btn-export' => esc_html__('Export', 'advanced-fuzzy-search'),
			'btn-import' => esc_html__('Import', 'advanced-fuzzy-search'),
			'btn-add' => esc_html__('Add field', 'advanced-fuzzy-search'),
			'remove-confirm' => esc_html__('Are you sure want to remove', 'advanced-fuzzy-search') . ' %s ' . esc_html__('field(s)', 'advanced-fuzzy-search') . '?',
		);
		$this->assign('settings', $settings);
		$this->assign('is_pro', $frame->isPro());

		return parent::getContent('adminAllFields');
	}
	
	public function showEditFieldAdmin( $id ) {
		if (!$this->getModule()->isWooCommercePluginActivated()) {
			return;
		}
		
		$assets = AssetsAfsw::_();
		$assets->loadCoreJs();

		if (empty($id)) {
			return parent::getContent('adminEditFieldNotFound');
		}

		$field = $this->getModel()->getFieldData($id);
		if (!$field) {
			return parent::getContent('adminEditFieldNotFound');
		}
		$module = $this->getModule('fields');
		$path = $module->getModPath() . 'assets/';
		$frame = FrameAfsw::_();

		$frame->addStyle('afsw-admin-fields', $path . 'css/admin.fields.css');
		$frame->addScript('afsw-admin-fields', $path . 'js/admin.fields.js');
		$frame->addStyle('afsw-front-fields', $path . 'css/afsw.fields.css');
		$frame->addScript('afsw-front-fields', $path . 'js/afsw.fields.js');
		
		$assets->loadSlimscroll();
		$assets->loadChosenSelects();
		
		$assets->loadColorPicker();
		$assets->loadAdminEndCss();
		DispatcherAfsw::doAction('adminEditFieldAssets');
		
		$trStrings = array(
			'confirm-delete' => esc_html__('Are you sure want to remove search Field?', 'advanced-fuzzy-search'),
			'btn-save' => esc_html__('Save', 'advanced-fuzzy-search'),
			'btn-cancel' => esc_html__('Cancel', 'advanced-fuzzy-search'),
		);

		$this->assign('field_id', $id);
		$this->assign('main_tabs', $module->getEditFieldTabsList(ReqAfsw::getVar('block')));
		$this->assign('settings', $field);
		$this->assign('is_pro', $frame->isPro());
		$this->assign('pro_url', $frame->getProUrl());
		$this->assign('current_theme', FrameAfsw::_()->getModule('integrations')->getCurrentTheme());
		$this->assign('pages', $this->getAllPages());
		
		$this->assign('tr_strings', DispatcherAfsw::applyFilters('addLangStrings', $trStrings, 'fields'));
				
		return parent::getContent('adminEditField');
	}
	
	public function renderFieldAssets( $settings = array() ) {
		$module = $this->getModule('fields');
		$path = $module->getModPath() . 'assets/';
		$frame = FrameAfsw::_();
		AssetsAfsw::_()->loadCoreJs(false);
		
		$frame->addStyle('afsw-front-fields', $path . 'css/afsw.fields.css');
		$frame->addScript('afsw-front-fields', $path . 'js/afsw.fields.js');
		
		DispatcherAfsw::doAction('renderFieldAssets', $settings);
	}
		
	public function renderFieldHtml( $params, $preview = false ) {
		if (!$this->getModule()->isWooCommercePluginActivated()) {
			return;
		}

		$id = isset($params['id']) ? (int) $params['id'] : 0;
		if (!$id) {
			return false;
		}

		// preview case
		if ( isset( $params['field'] ) ) {
			$settings = $params;
		} else {
			$settings = $this->getModel()->getFieldData($id);
		}

		if (!$settings || empty($settings['field'])) {
			return false;
		}
		
		$addArgs = array();
		if (!$preview) {
			$options = UtilsAfsw::getArrayValue($settings, 'options', array(), 2);
			$displayOn = UtilsAfsw::getArrayValue($options, 'display_on');
			if (!empty($displayOn)) {
				$isMobile = UtilsAfsw::isMobile();
				if ( ( $isMobile && 'desktop' == $displayOn ) || ( !$isMobile && 'mobile' == $displayOn ) ) {
					return false;
				}
			}
			$shortcodeArgs = $this->shortcodeArgs;
			foreach($params as $k => $v) {
				if (in_array($k, $shortcodeArgs)) {
					$addArgs[$k] = $v;
				}
			}
		}

		//$isWidget = $this->getFilterSetting($params, 'mode', '') == 'widget';

		$module = $this->getModule();
		
		if (!$preview) {
			$this->renderFieldAssets($settings);
		}

		$viewId = $id . '_' . ( $preview ? 'preview' : mt_rand(0, 999999) );
		$html = $this->generateFieldHtml($id, $settings, $viewId, $preview, $addArgs);
		$css = $preview ? $this->generateCss($id, $settings) : UtilsAfsw::getArrayValue($settings, 'css');

		$this->assign('fieldId', $id);
		$this->assign('viewId', $viewId);
		$this->assign('html', $html);
		$this->assign('css', $css);

		return parent::getContent('fieldHtml');
	}
	
	public function generateFieldHtml( $fieldId, $settings, $viewId, $preview = false, $addArgs = array() ) {
		$isPro = FrameAfsw::_()->isPro();
		
		$s = ReqAfsw::getVar('afsw-s', 'get', '');
		if (empty($s)) {
			$s = ReqAfsw::getVar('s', 'get', '');
		}
		$searchId = ReqAfsw::getVar('afsw-id');

		$module = $this->getModule();
		$field = UtilsAfsw::getArrayValue($settings, 'field', array(), 2);
		$placeholder = UtilsAfsw::getArrayValue($field, 'placeholder');
		
		$options = UtilsAfsw::getArrayValue($settings, 'options', array(), 2);
		$search = UtilsAfsw::getArrayValue($settings, 'search', array(), 2);
		$withAjax = UtilsAfsw::getArrayValue($options, 'enable_ajax', false, 1);
		$output = UtilsAfsw::getArrayValue($options, 'output', 'search_page');
		$isSearchPage = 'search_page' == $output;
		
		$needAuto = $preview;
		
		$autocomplete = $withAjax ? UtilsAfsw::getArrayValue($settings, 'autocomplete', array(), 2) : array();
		$order = UtilsAfsw::getArrayValue($autocomplete, 'order');
		if (!empty($order)) {
			$order = explode(',', $order);
			foreach ($order as $section) {
				if (UtilsAfsw::getArrayValue($autocomplete, 'show_' . $section, false, 1)) {
					$needAuto = true;
					break;
				}
			}
		}
		
		
		$minChars = 0;
		if ($needAuto) {
			$minChars = UtilsAfsw::getArrayValue($field, 'pre_min_chars', 3, 1);
			if ($minChars < 1) {
				$needAuto = false;
				$minChars = 0;
			}
			$autoDelay = UtilsAfsw::getArrayValue($field, 'pre_delay', 500, 1);
		}
		$html = '';
		//if (!$preview) {
			$html = DispatcherAfsw::applyFilters('beforeGenerateFieldHtml', $html, $settings, $viewId);
		//}
		
		$html .= '<div class="afsw-search-wrapper afsw-field-' . $fieldId . '" id="afswFieldWrapper-' . $viewId .
			'" data-viewid="' . $viewId .
			'" data-field="' . $fieldId .
			'" data-ajax="' . ( $withAjax ? 1 : 0 ) .
			'" data-by-enter="' . ( UtilsAfsw::getArrayValue($field, 'search_by_enter', false, 1) ? 1 : 0 ) .
			'" data-output="' . $output .
			'" data-output-container="' . esc_attr(UtilsAfsw::getArrayValue($options, 'output_container')) .
			'" data-redirect-page="' . esc_attr($isSearchPage ? home_url() : get_permalink(UtilsAfsw::getArrayValue($options, 'redirect_page', ''))) .
			'" data-autocomplete="' . ( $needAuto ? 1 : 0 ) .
			'" data-auto-delay="' . esc_attr($needAuto ? $autoDelay : 0) .
			'" data-min-chars="' . esc_attr($minChars) .
			( $preview ? '' : '" data-custom-js="' . esc_attr(UtilsAfsw::getArrayValue($settings, 'add_js')) ) .
			DispatcherAfsw::applyFilters('getWrapperAtributes', '', $settings) .
			'" style="display:none;">';
		
		$html .= '<div class="afsw-field-wrapper">';
		foreach($addArgs as $k => $v) {
			$html .= '<input type="hidden" class="afsw-add-args" data-name="' . esc_attr($k) . '" value="' . esc_attr($v) . '">';
		}
		$html = DispatcherAfsw::applyFilters('beforeGenerateFieldInputHtml', $html, $settings);
		$html .= '<input type="text" class="afsw-search-input" data-name="' . ( $isSearchPage ? 's' : 'afsw-s' ) . '" value="' . ( $searchId == $fieldId ? esc_attr($s) : '' ) .
			( empty($placeholder) ? '' : '" placeholder="' . esc_attr($placeholder) ) . '" autocomplete="off">';
		$html .= '<i class="fa fa-times afsw-search-remove" aria-hidden="true"></i>';
		
		$preloader = UtilsAfsw::getArrayValue($field, 'show_preloader', false, 1);
		if ($needAuto && $preloader) {
			$html .= '<div class="afsw-preloader">';
			
			$preType = UtilsAfsw::getArrayValue($field, 'pre_type', 'spinner');
			if ('custom' == $preType) {
				$html .= '<div class="afsw-custom-preloader" style="' . esc_attr(UtilsAfsw::getArrayValue($field, 'pre_icon', DispatcherAfsw::applyFilters('getDefaultIcon', '', 'preloader'))) . '"></div>';
			} else {
				$html .= '<i class="fa fa-' . $preType . ' fa-spin"></i>';
			}
			$html .= '</div>';
		}

		if (UtilsAfsw::getArrayValue($field, 'show_submit', 0, 1, false, true)) {
			$html .= '<button aria-label="' . esc_attr__('Search', 'advanced-fuzzy-search') . '" class="afsw-search-button">';
			$buttonAs = UtilsAfsw::getArrayValue($field, 'button_as', 'text', 0, $isPro ? false : array('text', 'search'));
			
			if ('text' == $buttonAs) {
				$t = UtilsAfsw::getArrayValue($field, 'button_text', __('Search',  'advanced-fuzzy-search'));
				//$html .= esc_html__(UtilsAfsw::getArrayValue($field, 'button_text', 'Search'), 'advanced-fuzzy-search');
				$html .= esc_html($t);
			} else if ('search' == $buttonAs) {
				$html .= '<i class="fa fa-search" aria-hidden="true"></i>';
			} else {
				$html .= '<div class="afsw-custom-button" style="' . esc_attr(UtilsAfsw::getArrayValue($field, 'button_icon', DispatcherAfsw::applyFilters('getDefaultIcon', '', 'button'))) . '"></div>';
			}
			$html .= '</button>';
		}
		$html .= '</div><div style="display:none">';
		$noResults = UtilsAfsw::getArrayValue($search, 'no_results');
		if (!empty($noResults)) {
			$html .= '<div class="afsw-noresults"><div class="afsw-noresults-text">' . esc_html($noResults) . '</div></div>';
		}
		
		if ($needAuto) {
			$html .= '<div class="afsw-autocomplete-popup afsw-auto-popup-' . $fieldId . '" data-viewid="' . $viewId . '"><div class="afsw-autocomplete-content"></div></div>';
			$autoNoResults = UtilsAfsw::getArrayValue($autocomplete, 'show_noresults') ? UtilsAfsw::getArrayValue($autocomplete, 'noresults_text', __('No results', 'advanced-fuzzy-search')) : '';
			if (!empty($autoNoResults)) {
				$html .= '<div class="afsw-auto-noresults"><div class="afsw-auto-noresults-text">' . esc_html($autoNoResults) . '</div></div>';
			}
		}
		$loader = UtilsAfsw::getArrayValue($options, 'loader_type', 'none');
		if (!empty($loader) && 'none' != $loader) {
			$loaderHtml = '';
			if ('woo' == $loader) {
				$loaderHtml = '<div class="afsw-search-loader afsw-default-loader"></div>';
			} else {
				$loaderHtml = DispatcherAfsw::applyFilters('getSearchLoaderHtml', '', $options);
			}
			if (!empty($loaderHtml)) {
				$html .= '<div class="afsw-loader-wrapper">' . $loaderHtml . '</div>';
			}
		}
		$html .= '</div>';
		$html = DispatcherAfsw::applyFilters('afterGenerateFieldHtml', $html, $settings, $viewId);
		$html .= '</div>';

		return $html;
	}
	
	public function renderAutocomplete( $searchParams, $fieldId ) {
		$autocomplete = $this->getModel()->getFieldData($fieldId, 'autocomplete');
		if (!$autocomplete) {
			return false;
		}
		
		$useCaching = UtilsAfsw::getArrayValue($autocomplete, 'use_cache') == 1;
		$onlyHistory = false;
		$disCacheHistory = false;
		if ($useCaching) {
			$cacheId = 0;
			$cacheCount = UtilsAfsw::getArrayValue($autocomplete, 'keep_cache_count', 0, 1);
			if (empty($cacheCount)) {
				$useCaching = false;
			}
			if ($useCaching) {
				$disCacheHistory = UtilsAfsw::getArrayValue($autocomplete, 'disable_cache_history', 0, 1) == 1;
				$cache = $this->getModel('cache')->getCache($fieldId, $searchParams);
				if (!empty($cache)) {
					$period = UtilsAfsw::getArrayValue($autocomplete, 'keep_cache_period');
					$k = ( 'hours' == $period ? 60 : ( 'days' == $period ? 1440 : 1 ) );
					if ($cache['minutes'] < $cacheCount * $k) {
						if ($disCacheHistory) {
							$html = html_entity_decode(htmlspecialchars_decode($cache['html']));
							$onlyHistory = true;
						} else {
							return html_entity_decode(htmlspecialchars_decode($cache['html']));
						}
					}
					$cacheId = $cache['id'];
				}
			}
		}
		
		$search = $searchParams['s'];
		$order = UtilsAfsw::getArrayValue($autocomplete, 'order');
		$results = '';
		$resultsForCache = '';
		if (!empty($order)) {
			self::$groupSection = UtilsAfsw::getArrayValue($autocomplete, 'group_sections', false, 1) == 1;
			self::$termSeparate = UtilsAfsw::getArrayValue($autocomplete, 'separate_terms', false, 1) == 1;
			self::$firstGroup = true;
			$order = explode(',', $order);
			$excludeHistory = array();
			foreach ($order as $section) {
				if (UtilsAfsw::getArrayValue($autocomplete, 'show_' . $section, false, 1)) {
					$options = UtilsAfsw::getArrayValue($autocomplete, $section, array(), 2);
					$limit = UtilsAfsw::getArrayValue($options, 'limit', 1, 1);
					if ($limit < 1) {
						$limit = 1;
					}
					$terms = array();
					switch ($section) {
						case 'user': 
							$userId = get_current_user_id();
							if ($userId) {
								$params = array('user_id' => $userId);
								if (UtilsAfsw::getArrayValue($options, 'this_field', 0, 1) == 1) {
									$params['field_id'] = $fieldId;
								}
								$terms = $this->getModule()->getModel('history')->getHistory($searchParams, $params, $excludeHistory, $limit);
								//$terms = array_fill(0, $limit, __('Sample user history', 'advanced-fuzzy-search'));
								$res = $this->renderAutocompleteUsers($terms, $options);
								if ($onlyHistory) {
									$html = str_replace('###USERHISTORY###', $res, $html);
								} else {
									$results .= $res;
									if ($disCacheHistory) {
										$resultsForCache .= '###USERHISTORY###';
									}
								}
								$excludeHistory = $terms;
							}
							break;
						case 'global': 
							$params = array();
							if (UtilsAfsw::getArrayValue($options, 'this_field', 0, 1) == 1) {
								$params['field_id'] = $fieldId;
							}
							$terms = $this->getModule()->getModel('history')->getHistory($searchParams, $params, $excludeHistory, $limit);
							$res = $this->renderAutocompleteGlobals($terms, $options);
							if ($onlyHistory) {
								$html = str_replace('###GLOBALHISTORY###', $res, $html);
							} else {
								$results .= $res;
								if ($disCacheHistory) {
									$resultsForCache .= '###GLOBALHISTORY###';
								}
							}
							$excludeHistory = $terms;
							break;
						default:
							if (!$onlyHistory) {
								$searchParams['id'] = $fieldId;
								$terms = DispatcherAfsw::applyFilters('getAutocompleteTerms', array(), $section, $searchParams, $options);
								$res = DispatcherAfsw::applyFilters('renderAutocomplete', '', $section, $terms, $options);
								$results .= $res;
								if ($disCacheHistory) {
									$resultsForCache .= $res;
								}
							}
					}
					if (!empty($terms)) {
						self::$firstGroup = false;
					}
				}
			}
		}
		if ($onlyHistory) {
			return $html;
		}
		$html = '';
		$htmlForCache = '';
		if (!empty($results)) {
			$html .= '<ul class="afsw-auto-list">' . $results . '</ul>';
			if ($disCacheHistory) {
				$htmlForCache = '<ul class="afsw-auto-list">' . $resultsForCache . '</ul>';
			}
		}
		if ($useCaching) {
			$this->getModel('cache')->saveCache($cacheId, $fieldId, $searchParams, ( $disCacheHistory ? $htmlForCache : $html ));
		}
		return $html;
	}
	
	public function renderPreviewAutocomplete( $settings ) {
		
		if (!$settings || empty($settings['autocomplete'])) {
			return '';
		}
		$autocomplete = UtilsAfsw::getArrayValue($settings, 'autocomplete', array(), 2);
		$order = UtilsAfsw::getArrayValue($autocomplete, 'order');
		$samples = '';

		if (!empty($order)) {
			self::$groupSection = UtilsAfsw::getArrayValue($autocomplete, 'group_sections', false, 1) == 1;
			self::$termSeparate = UtilsAfsw::getArrayValue($autocomplete, 'separate_terms', false, 1) == 1;
			self::$firstGroup = true;
			$order = explode(',', $order);
			foreach ($order as $section) {
				if (UtilsAfsw::getArrayValue($autocomplete, 'show_' . $section, false, 1)) {
					$options = UtilsAfsw::getArrayValue($autocomplete, $section, array(), 2);
					$limit = UtilsAfsw::getArrayValue($options, 'limit', 1, 1);
					if ($limit < 1) {
						$limit = 1;
					}
					switch ($section) {
						case 'user': 
							$terms = array_fill(0, $limit, __('Sample user history', 'advanced-fuzzy-search'));
							$samples .= $this->renderAutocompleteUsers($terms, $options);
							break;
						case 'global': 
							$terms = array_fill(0, $limit, __('Sample global history', 'advanced-fuzzy-search'));
							$samples .= $this->renderAutocompleteGlobals($terms, $options);
							break;
						default:
							$terms = DispatcherAfsw::applyFilters('sampleAutoTerms', array(), $section, $options);
							$cnt = count($terms);
							if (!empty($cnt)) {
								$keys = array_keys($terms);
								$first = $terms[$keys[0]];
								while ($limit > count($terms)) {
									$terms[] = $first;
								}
							}
							$samples .= DispatcherAfsw::applyFilters('renderAutocomplete', '', $section, $terms, $options);
					}
					if (!empty($terms)) {
						self::$firstGroup = false;
					}
				}
			}
		}
		
		$html = '';
		if (!empty($samples)) {
			$html .= '<style type="text/css" id="afsw-css-autocomplete">' . $this->generateCss('preview', $settings) . '</style>';
			$html .= '<div class="afsw-autocomplete-popup afsw-auto-popup-preview" data-viewid="preview"><div class="afsw-autocomplete-content">';
			$html .= '<ul class="afsw-auto-list">' . $samples . '</ul></div>';
			$html .= '</div>';
		}
		return $html;
	}
	
	public function renderAutocompleteUsers( $terms, $options ) {
		if (empty($terms)) {
			return '';
		}
		$html = $this->renderAutocompleteSectionTitle($options);
		$icon = '';
		if (UtilsAfsw::getArrayValue($options, 'show_images', false, 1)) {
			$icon .= '<div class="afsw-term-icon">';
			$iconType = UtilsAfsw::getArrayValue($options, 'icon_type');
			if ('custom' == $iconType) {
				$icon .= '<div class="afsw-custom-icon" style="' . esc_attr(UtilsAfsw::getArrayValue($options, 'user_icon', DispatcherAfsw::applyFilters('getDefaultIcon', '', 'user'))) . '"></div>';
			} else {
				$icon .= '<i class="fa fa-' . $iconType . '" aria-hidden="true"></i>';
			}
			$icon .= '</div>';
		}
		$cnt = count($terms);
		$i = 0;
		foreach ($terms as $term) {
			$i++;
			$html .= $this->renderAutocompleteTermStart('user', $i == $cnt) . $icon .
				'<div class="afsw-term-name">' . $term . '</div>' . 
				$this->renderAutocompleteTermEnd();
		}
		return $html;
	}
	public function renderAutocompleteGlobals( $terms, $options ) {
		if (empty($terms)) {
			return '';
		}
		$html = $this->renderAutocompleteSectionTitle($options);
		$icon = '';
		if (UtilsAfsw::getArrayValue($options, 'show_images', false, 1)) {
			$icon .= '<div class="afsw-term-icon">';
			$iconType = UtilsAfsw::getArrayValue($options, 'icon_type');
			if ('custom' == $iconType) {
				$icon .= '<div class="afsw-custom-icon" style="' . esc_attr(UtilsAfsw::getArrayValue($options, 'global_icon', DispatcherAfsw::applyFilters('getDefaultIcon', '', 'global'))) . '"></div>';
			} else {
				$icon .= '<i class="fa fa-' . $iconType . '" aria-hidden="true"></i>';
			}
			$icon .= '</div>';
		}
		$cnt = count($terms);
		$i = 0;
		foreach ($terms as $term) {
			$i++;
			$html .= $this->renderAutocompleteTermStart('global', $i == $cnt) . $icon .
				'<div class="afsw-term-name">' . $term . '</div>' . 
				$this->renderAutocompleteTermEnd();
		}
		return $html;
	}
	
	public function renderAutocompleteSectionTitle( $options ) {
		if (self::$groupSection) {
			$showTitle = UtilsAfsw::getArrayValue($options, 'show_title', false, 1) == 1;
			if (!self::$firstGroup || $showTitle) {
				return '<li class="afsw-section-group">' .
					( $showTitle ? '<div class="afsw-section-title">' . esc_html(UtilsAfsw::getArrayValue($options, 'title')) . '</div>' : '' ) .
					'</li>';
			}
		}
		return ''; 
	}
	public function renderAutocompleteTermStart( $section, $last = false ) {
		return '<li class="afsw-section-term' . ( self::$termSeparate && ( !$last || !self::$groupSection ) ? ' afsw-term-separator' : '' ) . '" data-section="' . $section . '"><div class="afsw-term-wrap">';
	}
	public function renderAutocompleteTermEnd() {
		return '</div></li>';
	}
	
	public function showStatisticsAdmin() {
		$this->assign('pro_url', FrameAfsw::_()->getProUrl());
		return parent::getContent('statisticsAdmin');
	}
	
}
