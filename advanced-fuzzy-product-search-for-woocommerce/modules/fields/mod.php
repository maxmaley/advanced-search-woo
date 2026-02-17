<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class FieldsAfsw extends ModuleAfsw {
	public $isAfswSearchId = false;
	
	public static $fieldOptions = array();
	public static $searchClauses = null;
	
	public $excludeArgs = array('paged', 'posts_per_page', 'post_type', 'wc_query', 'orderby', 'order', 'fields');
				
	public $indexKeys = null;
	public $indexTextKeys = null;
	public $searchTempTable = false;
	private $searchQueryParts = array();
	public $searchType = array();
	public $searchSorter = array();
	public $existMB = false;
	public $fuzzyParams = false;
	public $fuzzyWords = array();
	
	public function init() {
		DispatcherAfsw::addFilter('mainAdminTabs', array($this, 'addAdminTab'));
		
		if ( is_admin() ) {
			add_action('admin_notices', array($this, 'showAdminErrors'));
		}
		add_shortcode(AFSW_SHORTCODE, array($this, 'doShortcode'));
		add_action('afsw_clear_history', array($this->getModel('history'), 'clearHistory'), 10, 1);
		add_action('afsw_clear_history_shedule', array($this, 'clearHistoryShedule'), 10, 1);
		
		if (!is_admin()) {
			if (ReqAfsw::getVar('afsw-id')) {
				$this->isAfswSearchId = ReqAfsw::getVar('afsw-id');
			}
				
			if ($this->isAfswSearchId) {
				add_action('pre_get_posts', array($this, 'resetSearchParams'), 999, 1);
				add_filter('posts_clauses_request', array($this, 'addSearchClausesRequest'), 999, 2);
				add_filter('posts_results', array($this, 'returnSearchParams'), 999, 2);
				add_action('woocommerce_product_query', array($this, 'addSearchParams'));
				add_action('woocommerce_shortcode_products_query', array($this, 'addSearchParamsShortcode'), 999, 3);
			}
		}
		$this->existMB = function_exists('mb_substr');
	}
	public function returnSearchParams( $posts, $query ) {
		if (isset($query->query_vars['afsw-query'])) {
			if ($this->isAfswSearchId) {
				$options = $this->getFieldOptions($this->isAfswSearchId, 'options');
				if (UtilsAfsw::getArrayValue($options, 'output', 'search_page') == 'search_page') {
					$query->is_search = true;
					$s = ReqAfsw::getVar('s');
					$query->query_vars['s'] = empty($s) ? ReqAfsw::getVar('afsw-s') : $s;
				}
			}
		}
		return $posts;
	}
	
	public function getSearchQueryParts() {
		return $this->searchQueryParts;
	}
	public function setSearchQueryParts( $query, $part = '' ) {
		if (empty($part)) {
			$this->searchQueryParts = $query;
		} else {
			$this->searchQueryParts[$part] = $query;
		}
	}

	public function getFieldOptions( $id, $column = '' ) {
		if (!isset(self::$fieldOptions[$id])) {
			self::$fieldOptions[$id] = $this->getModel()->getFieldData($id);
		}
		return empty($column) ? self::$fieldOptions[$id] : ( isset(self::$fieldOptions[$id][$column]) ? self::$fieldOptions[$id][$column] : false );
	}
	
	public function getFuzzyModes() {
		return array( 
			'' => __('none', 'advanced-fuzzy-search'), 
			'soft' => __('soft', 'advanced-fuzzy-search'), 
			'normal' => __('normal', 'advanced-fuzzy-search'), 
			'hard' => __('hard', 'advanced-fuzzy-search'),
			'custom' => __('custom', 'advanced-fuzzy-search') . ( FrameAfsw::_()->isPro() ? '' : ' PRO' ) 
		);
	}
	
	public function getFuzzyParams( $options ) {
		$fuzzy = UtilsAfsw::getArrayValue($options, 'fuzzy_mode');
		switch ($fuzzy) {
			case 'normal': 
				return array('prefix' => 2, 'expansions' => 200, 'distance' => 2);
				break;
			case 'soft': 
				return array('prefix' => 2, 'expansions' => 50, 'distance' => 1);
				break;
			case 'hard': 
				return array('prefix' => 2, 'expansions' => 400, 'distance' => 3);
				break;
			case 'custom': 
				return DispatcherAfsw::applyFilters('getFuzzyParams', array('prefix' => 2, 'expansions' => 200, 'distance' => 2), $options);
				break;
			default:
				return false;
		}
	}
	
	public function validPostType( $wp_query ) {
		if ( ! isset( $wp_query->query_vars['post_type'] ) ) {
			return false;
		}
		if ( ! in_array( 'product', (array) $wp_query->query_vars['post_type'] ) ) {
			return false;
		}
		return true;
	}
	
	public function addSearchClausesRequest( $clauses, $wp_query ) {
		if (!empty( $wp_query->query_vars['afsw-query']) && !is_null(self::$searchClauses)) { // ($wp_query->is_main_query() && isset( $wp_query->query_vars['wc_query'] ) && !empty( $wp_query->query_vars['wc_query'] ) && 'product_query' === $wp_query->query_vars['wc_query'])) {
			$searchClauses = self::$searchClauses;
			foreach ($searchClauses as $key => $str) {
				if (!empty($str)) {
					if ('limits' === $key && '' === $str) {
						$clauses[$key] = '';
					} else if ('orderby' === $key) {
						$clauses[$key] = $str;
					} elseif (!empty($str)) {
						if (false === strpos($clauses[$key], $str)) {
							$clauses[$key] .= " $str";
						}
					}
				}
			}
		}
		return $clauses;
	}
	
	public function addAssetsFront() {
		if ($this->isActiveBonusProgram()) {
			if ($this->isEnableBadge() || $this->isEnableWidget()) {
				HtmlAfsw::echoEscapedHtml($this->getView()->addCustomStyles());
			}
			$this->getView()->showPointsFront();
		}
	}
	
	public function addAdminTab( $tabs ) {
		$icon = FrameAfsw::_()->isPro() ? '' : ' woobewoo-show-pro';
		$code = $this->getCode();
		$tabs[ $code ] = array(
			'label' => esc_html__('All Fields', 'advanced-fuzzy-search'), 'callback' => array($this, 'showFieldsListAdmin'), 'fa_icon' => 'fa-list', 'sort_order' => 10, 'add_bread' => $this->getCode(),
		);
		$tabs[ $code . '-edit' ] = array(
			'label' => esc_html__('Edit', 'advanced-fuzzy-search'), 'callback' => array($this, 'showEditFieldAdmin'), 'sort_order' => 20, 'child_of' => $this->getCode(), 'hidden' => 1, 'add_bread' => $this->getCode(),
		);
		$tabs[ 'statistics'] = array(
			'label' => esc_html__('Statistics', 'advanced-fuzzy-search'), 'callback' => array($this, 'showStatisticsAdmin'), 'fa_icon' => 'fa-line-chart' . $icon, 'sort_order' => 50, 'add_bread' => $this->getCode(),
		);
		return $tabs;
	}

	public function showFieldsListAdmin() {
		return $this->getView()->showFieldsListAdmin();
	}
	public function showEditFieldAdmin() {
		$id = ReqAfsw::getVar('id', 'get');
		return $this->getView()->showEditFieldAdmin($id);
	}
	public function showStatisticsAdmin() {
		return DispatcherAfsw::applyFilters('showStatisticsAdmin', $this->getView()->showStatisticsAdmin());
	}
	
	public function showAdminErrors() {
		// check WooCommerce is installed and activated
		if (!$this->isWooCommercePluginActivated()) {
			$view = $this->getView();
			/* translators: %s: plugin name */
			$s = sprintf(__('For work with %s plugin, You need to install and activate WooCommerce plugin.', 'advanced-fuzzy-search'), AFSW_WP_PLUGIN_NAME);
			
			$view->assign('errorMsg', $s);
			// check current module
			if (ReqAfsw::getVar('page') == AFSW_SHORTCODE) {
				// show message
				HtmlAfsw::echoEscapedHtml($view->getContent('showAdminNotice'));
			}
		}
	} 

	public function isWooCommercePluginActivated() {
		return class_exists('WooCommerce');
	}
	
	public function getEditFieldTabsList( $current = '' ) {
		$tabs = array(
			'options' => array(
				'icon' => 'fa-gear',
				'class' => '',
				'pro' => false,
				'label' => __('Options', 'advanced-fuzzy-search'),
			),
			'field' => array(
				'icon' => 'fa-terminal',
				'class' => '',
				'pro' => false,
				'label' => __('Field', 'advanced-fuzzy-search'),
			),
			'autocomplete' => array(
				'icon' => 'fa-refresh',
				'class' => '',
				'pro' => false,
				'label' => __('Autocomplete', 'advanced-fuzzy-search'),
			),
			'search' => array(
				'icon' => 'fa-search',
				'class' => '',
				'pro' => false,
				'label' => __('Search', 'advanced-fuzzy-search'),
			),
			'cssjs' => array(
				'icon' => 'fa-code',
				'class' => '',
				'pro' => false,
				'label' => __('CSS & JS', 'advanced-fuzzy-search'),
			),
		);

		if (empty($current) || !isset($tabs[$current])) {
			reset($tabs);
			$current = key($tabs);
		}
		$tabs[$current]['class'] .= ' current';
		
		return DispatcherAfsw::applyFilters('getEditFieldTabsList', $tabs);
	}
	public function getAutocompleteSections() {
		$pro = FrameAfsw::_()->isPro() ? '' : ' PRO';
		$sections = array(
			'user' => __('User search history', 'advanced-fuzzy-search'),
			'global' => __('Global search history', 'advanced-fuzzy-search'),
			'categories' => __('Categories', 'advanced-fuzzy-search') . $pro,
			'brands' => __('Brands', 'advanced-fuzzy-search') . $pro,
			'predicted' => __('Predicted products', 'advanced-fuzzy-search') . $pro,
			//'featured' => __('Featured products', 'advanced-fuzzy-search') . $pro,
		);
		
		return DispatcherAfsw::applyFilters('getAutocompleteSections', $sections);
	}
	public function getSearchScopes() {
		$scopes = array(
			'title' => array('name' => __('Title', 'advanced-fuzzy-search'), 'pro' => 0, 'default' => true, 'inx_source' => 1, 'inx_name' => 'post_title', 'inx_type' => 1),
			'attribute' => array('name' => __('Attributes', 'advanced-fuzzy-search'), 'pro' => 0, 'multy' => true, 'inx_source' => 2, 'inx_type' => 1),
			'category' => array('name' => __('Categories', 'advanced-fuzzy-search'), 'pro' => 0, 'inx_source' => 2, 'inx_name' => 'product_cat', 'inx_type' => 1),
			'content' => array('name' => __('Description', 'advanced-fuzzy-search'), 'pro' => 1, 'inx_source' => 1, 'inx_name' => 'post_content', 'inx_type' => 2),
			'excerpt' => array('name' => __('Short description', 'advanced-fuzzy-search'), 'pro' => 1, 'inx_source' => 1, 'inx_name' => 'post_excerpt', 'inx_type' => 2),
			'sku' => array('name' => __('SKU', 'advanced-fuzzy-search'), 'pro' => 1, 'inx_source' => 3, 'inx_name' => '_sku', 'inx_type' => 1),
			'tag' => array('name' => __('Tags', 'advanced-fuzzy-search'), 'pro' => 1, 'inx_source' => 2, 'inx_name' => 'product_tag', 'inx_type' => 1),
			'brand' => array('name' => __('Brand', 'advanced-fuzzy-search'), 'pro' => 1),
			'acf' => array('name' => __('ACF attributes', 'advanced-fuzzy-search'), 'pro' => 1),
			'ctax' => array('name' => __('Custom taxonomies', 'advanced-fuzzy-search'), 'pro' => 1),
			'meta' => array('name' => __('Meta fields', 'advanced-fuzzy-search'), 'pro' => 1),
		);
		
		return DispatcherAfsw::applyFilters('getSearchScopes', $scopes);
	}
	public function getSearchModes() {
		$modes = array(
			'fw' => __('full words', 'advanced-fuzzy-search'),
			'pw' => __('part words', 'advanced-fuzzy-search'),
			'cm' => __('complete match', 'advanced-fuzzy-search'),
			'pm' => __('phrase match', 'advanced-fuzzy-search')
		);
		
		return DispatcherAfsw::applyFilters('getSearchModes', $modes);
	}
		
	public function renderField( $params, $preview = false ) {
		return $this->getView()->renderFieldHtml( $params, $preview );
	}
	public function doShortcode( $params ) {
		return $this->getView()->renderFieldHtml($params);
	}
	
	public function resetSearchParams( $query ) {
		if ($this->isAfswSearchId) {
			if ($query->is_search() && $query->is_main_query() && get_query_var('s', false)) {
				$query->query_vars['s'] = false;
				$query->query_vars['post_type'] = 'product';
				$query->query_vars['suppress_filters'] = false;
				$query->query_vars['afsw-query'] = 1;
				$query->is_search = false;
				$this->renderSearchClauses();
			} else {
				$this->forceProductSearch( $query );
			}
		}
		return $query;
	}
	public function forceProductSearch( $query ) {
		$blocksApi = false;
		$uri = empty($_SERVER['REQUEST_URI']) ? '' : sanitize_text_field($_SERVER['REQUEST_URI']);
		$blocksApi = strpos( $uri, 'wp-json/wc/store/') && strpos( $uri, '/products?');

		$forced = false;
		if ( $blocksApi ) {
			if ( isset( $query->query_vars['post_type'] ) && 'product' === $query->query_vars['post_type'] && function_exists( 'debug_backtrace' ) ) {
				$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 10 ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
				if ( is_array( $backtrace ) ) {
					$classes = array(
						'Essential_Addons_Elementor\Elements\Product_Grid',
						'ElementorPro\Modules\QueryControl\Classes\Elementor_Post_Query',
						'DynamicContentForElementor\Widgets\DCE_Widget_DynamicPosts_Base',
						'DynamicContentForElementor\Widgets\DynamicPostsBase',
						'DCP_WooProducts',
						'Automattic\WooCommerce\Blocks\StoreApi\Utilities\ProductQuery',
						'Automattic\WooCommerce\StoreApi\Utilities\ProductQuery'
					);
					$found   = ( ( isset( $backtrace[5]['class'] ) && 'Automattic\WooCommerce\Blocks\BlockTypes\AbstractProductGrid' === $backtrace[5]['class'] ) || ( isset( $backtrace[7]['class'] ) && in_array( $backtrace[7]['class'], $classes, true ) ) )
						? true
						: false;

					if ( ! $found ) {
						$classList = array_column( $backtrace, 'class' );

						$searchList = array(
							'ElementPack\Modules\Woocommerce\Widgets\Products',
							'ElementPack\Modules\Woocommerce\Widgets\WC_Carousel',
						);
						foreach ( $searchList as $cl ) {
							if ( array_search( $cl, $classList ) > 5 ) {
								$found = true;
								break;
							}
						}
					}

					if ( $found ) {
						$this->addSearchParams($query);
						$forced = true;
					}
				}
			}
		}

		return $query;
	}
	public function addSearchParams( $q ) {
		if ($this->isAfswSearchId) {
			$search = $this->getFieldOptions($this->isAfswSearchId, 'search');
			if (UtilsAfsw::getArrayValue($search, 'all_products')) {

				foreach ($q->query as $queryVarKey => $queryVarValue) {
					if (!in_array($queryVarKey, $this->excludeArgs)) {
						if (is_string( $queryVarValue)) {
							$q->set($queryVarKey, '');
						}
						if (is_array($queryVarValue)) {
							$q->set($queryVarKey, array());
						}
					}
				}
			}
			$q->set('afsw-query', 1);
			$this->renderSearchClauses();
		}
		return $q;
	}
	public function addSearchParamsShortcode( $args, $attributes = array(), $type = '' ) {
		if ($this->isAfswSearchId) {
			$search = $this->getFieldOptions($this->isAfswSearchId, 'search');
			
			if (UtilsAfsw::getArrayValue($search, 'all_products')) {
				foreach ($args as $queryVarKey => $queryVarValue) {
					if (!in_array($queryVarKey, $this->excludeArgs)) {
						if (is_string($queryVarValue)) {
							$args[$queryVarKey] = '';
						} else if (is_array($queryVarValue) || is_object($queryVarValue)) {
							$args[$queryVarKey] = array();
						} 
					}
				}
			}
			
			$args['afsw-query'] = 1;
			$this->renderSearchClauses();
		}
		return $args;
	}
	
	public function renderSearchClauses() {
		if (!is_null(self::$searchClauses)) {
			return false;
		}
		$s = ReqAfsw::getVar('s');
		if (is_null($s) || empty($s)) {
			$s = ReqAfsw::getVar('afsw-s');
		}
		if (is_null($s) || empty($s)) {
			return false;
		}
		$s = $this->controlSearchString($s);
		if (empty($s)) {
			return false;
		}
		
		$search = $this->getFieldOptions($this->isAfswSearchId, 'search');
		$searchParams = DispatcherAfsw::applyFilters('addSearchParams', array('s' => $s));
		$this->renderSearchQuery($search, $searchParams);
	}
	/*
	 * $key = inx_key
	 * $name = inx_name (
	 * 		false - return all for inx_key=$key
	 * 		string - return inx_name=$name and inx_key=$key
	 * 		empty array() - return all childs for inx_key=$key
	 * 		filled array() - return selected childs for inx_key=$key
	 */
	public function getIndexKey( $key, $name = false, $field = false, $exlude = array() ) {
		if (is_null($this->indexKeys)) {
			$this->indexKeys = FrameAfsw::_()->getModule('indexing' )->getModel()->getKeysWithCalcControl();
			if (is_null($this->indexTextKeys)) {
				$this->indexTextKeys = array();
				foreach ($this->indexKeys as $k => $keys) {
					foreach ($keys as $n => $data) {
						if (2 == $data['inx_type']) {
							$this->indexTextKeys[] = $data['id'];
						}
					}
				}
			}
		}
		if (isset($this->indexKeys[$key])) {
			if (false === $name) {
				if (empty($field)) {
					return $this->indexKeys[$key];
				} else {
					$keys = array();
					foreach ($this->indexKeys[$key] as $data) {
						$keys[] = $data[$field];
					}
					return $keys;
				}
			}
			if (is_array($name)) {
				$keys = array();
				$all = empty($name);
				foreach ($this->indexKeys[$key] as $data) {
					$n = $data['inx_name'];
					if (( ( $all && !empty($n) ) || in_array($n, $name) ) && !in_array($n, $exlude)) {
						$keys[] = $data[$field];
					}
				}
				return $keys;
			}
			return isset($this->indexKeys[$key][$name]) ? ( empty($field) ? $this->indexKeys[$key][$name] : $this->indexKeys[$key][$name][$field] ) : false;
		}
		return false;
	}
		
	public function controlSearchString( $str ) {
		$s = str_replace('\\', '', str_replace('"', '', str_replace("'", '', str_replace('  ', ' ', trim($str)))));
		$s = $this->existMB ? mb_strtolower($s) : strtolower($s);
		return $s;
	}

	public function renderSearchQuery( $settings, $search, $limit = 0 ) {
		if (!is_null(self::$searchClauses)) {
			return false;
		}
		
		self::$searchClauses = array();
		$s = $search['s'];
		$sLen = $this->existMB ? mb_strlen($s) : strlen($s);
			
		if (UtilsAfsw::getArrayValue($settings, 'min_chars', 0, 1) > $sLen) {
			return;
		}
		$isLimit = !empty($limit);
		
		$order = UtilsAfsw::getArrayValue($settings, 'order');
		if (!empty($order)) {
			$order = explode(',', $order);
			
			$phrase = $s;
			$words = explode(' ', $s);
			$flip = array();
			foreach ($words as $word) {
				$flip[$word] = false;
			}
			$words = DispatcherAfsw::applyFilters('conrolSearchWords', $flip, $settings);
			$wordsOR = array();
			foreach ($words as $word => $synonyms) {
				if (!empty($synonyms)) {
					$wordsOR = array_merge($wordsOR, $synonyms);
				} else {
					$wordsOR[] = $word;
				}
			}
			$wordsOR = array_unique($wordsOR);
			$cntWords = count($words);
			$oneWord = ( 1 == $cntWords );
			
			$tResults = array(); // text keys
			$nResults = array(); // not-text keys
			$fResults = array(); // fuzzy keys
			$relevats = array();
			
			$this->fuzzyParams = $this->getFuzzyParams($settings);
			$sortResult = UtilsAfsw::getArrayValue($settings, 'sorter');
			$isRelevant = ( 'relevant' == $sortResult );
			$isFuzzy = false !== $this->fuzzyParams;
			$modelWords = FrameAfsw::_()->getModule('indexing')->getModel('index_words');
			$modelPhrases = FrameAfsw::_()->getModule('indexing')->getModel('index_phrases');
			if ($isFuzzy) {
				$column = 'prefix' . $this->fuzzyParams['prefix'];
				if (!DbAfsw::exist('@__index_words', $column)) {
					$isFuzzy = false;
				}
			}
		
			$i = 0;
			$cnt = 0;
			$this->searchTempTable = DbAfsw::createTemporaryTable('afsw_search_temp', '', 'id int, sorter int, PRIMARY KEY (id)');
			$distinct = '';
			$this->searchQueryParts['select'] = array(
				$distinct . 'p.ID,',
				$distinct . 'IF(d.pr_type=2,p.post_parent,p.ID), '
				);
			$stockstatuses = UtilsAfsw::getArrayValue($settings, 'stockstatuses', array(), 2);
			$join = '';
			if (!empty($stockstatuses)) {
				$join = " INNER JOIN #__postmeta as m ON (m.post_id=p.id AND m.meta_key='_stock_status' AND m.meta_value" . $this->generateInStrQuery($stockstatuses) . ')';
			}
			$this->searchQueryParts['join'] = array(
				$join,
				$join,
				);
			$where = " WHERE p.post_status IN('publish','private')";
			$this->searchQueryParts['where'] = array(
				$where . " AND p.post_type='product'",
				$where . " AND p.post_type IN ('product','product_variation')"
				);
			$this->searchType = array(
				'' => ' AND d.pr_type in (0,1)',
				'var' => ' AND d.pr_type in (0,2)',
				'all' => ''
				);
			DispatcherAfsw::doAction('addSearchQuery', $settings, $search);
			$this->fuzzyWords = array();
			
			$this->searchSorter = array();
			foreach ($order as $scope) {
				$clauses = false;
				if (UtilsAfsw::getArrayValue($settings, 'by_' . $scope, false, 1)) {
					$options = UtilsAfsw::getArrayValue($settings, $scope, array(), 2);
					$mode = UtilsAfsw::getArrayValue($options, 'mode', 'fw');
					$isWord = ( 'fw' == $mode || 'pw' == $mode );
					if ($isWord && empty($cntWords)) {
						continue;
					}
					$fullWords = ( 'fw' == $mode );
						
					$forTypes = UtilsAfsw::getArrayValue($options, 'for_variable', '', 0, array('var', 'all')); 
					$isAnd = $isWord && !$oneWord && ( UtilsAfsw::getArrayValue($options, 'logic', 'or') == 'and' );
					$indexKeyIds = false;
					switch ($scope) {
						case 'title': 
							$indexKeyIds = $this->getIndexKey($scope, false, 'id');
							break;
						case 'attribute':
							$withLocal = UtilsAfsw::getArrayValue($options, 'by_local', false, 1) == 1;
							$list = UtilsAfsw::getArrayValue($options, 'list', array(), 2);
							if (empty($list)) {
								$list = array();
							} else {
								if (!is_array($list)) {
									$list = explode(',', $list);
								}
								if ($withLocal) {
									$list[] = 'afsw_local_attributes';
								}
							}
							$indexKeyIds = $this->getIndexKey('attribute', $list, 'id', ( $withLocal ? array() : array('afsw_local_attributes') ));
							break;
						case 'category': 
							$withParent = UtilsAfsw::getArrayValue($options, 'by_parent', false, 1) == 1;
							$indexKeyIds = $this->getIndexKey('category', array(), 'id', ( $withParent ? array() : array('afsw_parent_taxonomies') ));
							break;
						default:
							$indexKeyIds = DispatcherAfsw::applyFilters('getScopeIndexKeyIds', $indexKeyIds, $scope, $options);
					}
					if (!empty($indexKeyIds)) {
						if (!is_array($indexKeyIds)) {
							$indexKeyIds = array($indexKeyIds);
						}
						$splitedKeyIds = $this->splitTextKeys($indexKeyIds);
						if ($isLimit || $isAnd) {
							foreach ($splitedKeyIds as $keyType => $keyIds) {
								if (!empty($keyIds)) {
									$isText = 'text' == $keyType;
									if (!$isWord) {
										$query = $isText 
											? $this->generateQuerySearchPhrasesForTexts($phrase, $keyIds, $i, ( 'cm' == $mode ), $forTypes)
											: $this->generateQuerySearchPhrases($phrase, $keyIds, $i, ( 'cm' == $mode ), $forTypes);
									} else {
										$query = $isText 
											? $this->generateQuerySearchWordsForTexts(( $isAnd ? $words : $wordsOR ), $keyIds, $i, $fullWords, $forTypes, $isAnd)
											: $this->generateQuerySearchWords(( $isAnd ? $words : $wordsOR ), $keyIds, $i, $fullWords, $forTypes, $isAnd);
									}
									if ($isLimit) {
										$selected = DbAfsw::get('SELECT ID FROM ' . $this->searchTempTable, 'col');
										if (!empty($selected)) {
											$query .= ' AND p.ID NOT IN (' . implode(',', $selected) . ')';
										}
										$query .= ' LIMIT ' . ( $limit - $cnt ) * 2;
									}
									$r = DbAfsw::query($query, true);
									if (false === $r) {
										FrameAfsw::_()->pushError(DbAfsw::getError());
										FrameAfsw::_()->pushError('Error query: ' . $query); 
										return false;
									}
									$cnt += $r;
									if ($isLimit && $limit <= $cnt) {
										break 2;
									}
								}
							}
							if ($isWord && $isFuzzy && !empty($splitedKeyIds['notText'])) {
								$query = $this->generateFuzzyQuery($words, $splitedKeyIds['notText'], ( $i + 1 ), $forTypes, $isAnd, ( $limit - $cnt ) * 2);
								if (false === $query) {
									return false;
								}
								if (true !== $query) {
									$r = DbAfsw::query($query, true);
									if (false === $r) {
										FrameAfsw::_()->pushError(DbAfsw::getError());
										FrameAfsw::_()->pushError('Error query: ' . $query); 
										return false;
									}
									$cnt += $r;
									if ($isLimit && $limit <= $cnt) {
										break;
									}
								}
							}
						} else {
							if (!empty($splitedKeyIds['text'])) {
								if (!isset($tResults[$mode])) {
									$tResults[$mode] = array();
								}
								if (!isset($tResults[$mode][$forTypes])) {
									$tResults[$mode][$forTypes] = array();
								}
								$tResults[$mode][$forTypes] = array_merge($tResults[$mode][$forTypes], $splitedKeyIds['text']);
							}
							if (!empty($splitedKeyIds['notText'])) {
								if (!isset($nResults[$mode])) {
									$nResults[$mode] = array();
								}
								if (!isset($nResults[$mode][$forTypes])) {
									$nResults[$mode][$forTypes] = array();
								}
								$nResults[$mode][$forTypes] = array_merge($nResults[$mode][$forTypes], $splitedKeyIds['notText']);
							
								if ($isWord && $isFuzzy) {
									if (isset($fResults[$forTypes])) {
										$fResults[$forTypes] = array_merge($fResults[$forTypes], $splitedKeyIds['notText']);
									} else {
										$fResults[$forTypes] = $splitedKeyIds['notText'];
									}
								}
							}
						}
						foreach ($indexKeyIds as $id) {
							$this->searchSorter[$id] = $i;
						}
					}
					$i += 2;
				}
			}

			if (!$isLimit) {
				foreach ($tResults as $mode => $data) {
					$isWord = ( 'fw' == $mode || 'pw' == $mode );
					foreach ($data as $forTypes => $keys) {
						if (!empty($keys)) {
							$i = $isRelevant ? $this->generateRelevantSorter($keys) : 0;
							
							if ($isWord) {
								$query = $this->generateQuerySearchWordsForTexts($wordsOR, $keys, $i, ( 'fw' == $mode ), $forTypes, false);
							} else {
								$query = $this->generateQuerySearchPhrasesForTexts($phrase, $keys, $i, ( 'cm' == $mode ), $forTypes);
							}
							if (!DbAfsw::query($query)) {
								FrameAfsw::_()->pushError(DbAfsw::getError());
								FrameAfsw::_()->pushError('Error query: ' . $query); 
								return false;
							}
						}
					}
				}
				foreach ($nResults as $mode => $data) {
					$isWord = ( 'fw' == $mode || 'pw' == $mode );
					foreach ($data as $forTypes => $keys) {
						if (!empty($keys)) {
							$i = $isRelevant ? $this->generateRelevantSorter($keys) : 0;
							
							if ($isWord) {
								$query = $this->generateQuerySearchWords($wordsOR, $keys, $i, ( 'fw' == $mode ), $forTypes, false);
							} else {
								$query = $this->generateQuerySearchPhrases($phrase, $keys, $i, ( 'cm' == $mode ), $forTypes);
							}
							if (!DbAfsw::query($query)) {
								FrameAfsw::_()->pushError(DbAfsw::getError());
								FrameAfsw::_()->pushError('Error query: ' . $query); 
								return false;
							}
						}
					}
				}
				if ($isFuzzy) {
					foreach ($fResults as $forTypes => $keys) {
						if (!empty($keys)) {
							$i = $isRelevant ? $this->generateRelevantSorter($keys, 1) : 0;
							$query = $this->generateFuzzyQuery($words, $keys, $i, $forTypes, false);
							if (false === $query) {
								return false;
							}
							if (true !== $query) {
								if (!DbAfsw::query($query)) {
									FrameAfsw::_()->pushError(DbAfsw::getError());
									FrameAfsw::_()->pushError('Error query: ' . $query); 
									return false;
								}
							}
						}
					}
				}
				
				global $wpdb;
				
				self::$searchClauses = array(
					'join' => ' INNER JOIN ' . $this->searchTempTable . ' AS afsw_temp ON (afsw_temp.id=' . $wpdb->posts . '.ID)'
				);
				$sort = '';
				switch ($sortResult) {
					case 'relevant': 
						$sort = 'afsw_temp.sorter';
						break;
					case 'title': 
						$sort = $wpdb->posts . '.post_title';
						break;
				}
				if (!empty($sort)) {
					self::$searchClauses['orderby'] = $sort;
				}
			}
		}
		
		if (!$isLimit && $this->isAfswSearchId) {
			$options = $this->getFieldOptions($this->isAfswSearchId, 'options');
			if (UtilsAfsw::getArrayValue($options, 'save_history') == 1) {
				$found = ( DbAfsw::get('SELECT 1 FROM ' . $this->searchTempTable . ' LIMIT 1', 'one') == 1 ? 1 : 0 );
				if (!$this->getModel('history')->saveHistory($this->isAfswSearchId, $search, $found)) {
					return false;
				}
				
			}
		}
		
		return true;
	}
	public function generateRelevantSorter( $keys, $add = 0 ) {
		if (count($keys) == 1) {
			$i = ( isset($this->searchSorter[$keys[0]]) ? $this->searchSorter[$keys[0]] : 0 ) + $add;
		} else {
			$i = '';
			$lastSort = -1;
			$oneSort = true;
			foreach ($keys as $k) {
				if (isset($this->searchSorter[$k])) {
					$sort = $this->searchSorter[$k];
					$i .= ' WHEN d.key_id=' . $k . ' THEN ' . ( $sort + $add );
					if ($lastSort < 0) {
						$lastSort = $sort;
					} else if ($oneSort && $lastSort != $sort) {
						$oneSort = false;
					}
				}
			}
			if (empty($i)) {
				$i = 0 + $add;
			} else if ($oneSort) {
				$i = $lastSort + $add;
			} else {
				$i = '(CASE' . $i . ' ELSE ' . $add . '  END)';
			}
		}
		return $i;
	}
	
	public function generateFuzzyQuery( $words, $keyIds, $i, $forTypes, $isAnd = false, $limit = 0 ) {
		$withVars = !empty($forTypes);
		$isLimit = !empty($limit);
		
		$prefix = $this->fuzzyParams['prefix'];
		$column = 'prefix' . $prefix;
		$distance = $this->fuzzyParams['distance'];
		$cntWords = 0;
		$fussyIds = array();
		$cnt = 0;
		$isBreak = false;
		foreach ( $words as $word => $synonyms ) {
			$ids = array();
			$len = $this->existMB ? mb_strlen($word) : strlen($word);
			if ($len > $prefix) {
				$query = 'SELECT DISTINCT w.id, w.value' .
					' FROM #__posts as p' . $this->searchQueryParts['join'][$withVars] .
					' INNER JOIN @__index_data as d ON (d.product_id=p.ID AND d.inx_mode=1 AND d.key_id' . $this->generateInIdsQuery($keyIds) . ')' .
					' INNER JOIN @__index_words as w ON (w.id=d.inx_id)' .
					$this->searchQueryParts['where'][$withVars] . $this->searchType[$forTypes] .
					' AND w.' . $column . "='" . substr($word, 0, $prefix) . "'" .
					" AND w.value!='" . $word . "'" .
					' LIMIT ' . $this->fuzzyParams['expansions'];
				$variants = DbAfsw::get($query);
				if (false === $variants) {
					FrameAfsw::_()->pushError(DbAfsw::getError());
					FrameAfsw::_()->pushError('Error query: ' . $query); 
					return false;
				}
				if (!empty($variants)) {
					foreach ($variants as $var) {
						if (levenshtein($word, $var['value']) <= $distance) {
							$ids[] = $var['id'];
							$cnt++;
							if ($isLimit && !$isAnd && $limit <= $cnt) {
								$isBreak = true;
								break;
							}
						}
					}
				}
			}
			if (!empty($ids)) {
				$fussyIds[$word] = $ids;
				$cntWords++;
			}
		}
		if (empty($cntWords)) {
			return true;
		}
		if ($isAnd && $cntWords <= 1) {
			$isAnd = false;
		}
		if (!$isAnd) {
			$temp = array();
			foreach ($fussyIds as $ids) {
				$temp = array_merge($temp, $ids);
			}
			$fussyIds = $temp;
		}
		
		$query = 'INSERT IGNORE INTO ' . $this->searchTempTable .
			' SELECT ' . $this->searchQueryParts['select'][$withVars] . $i .
			' FROM #__posts as p' . $this->searchQueryParts['join'][$withVars];
		if ($isAnd) {
			//$query .= ' INNER JOIN @__index_data as d ON (d.product_id=p.ID AND d.inx_mode=1 AND d.key_id' . $this->generateInIdsQuery($keyIds) . ')';
		
			$j = 0;
			$first = true;
			$d = '';
			foreach ($fussyIds as $ids) {
				$j++;
				if (!$first) {
					$d = $j;
				}
				$query .= ' INNER JOIN @__index_data as d' . $d . ' ON (d' . $d . '.product_id=p.ID AND d' . $d . '.inx_mode=1 AND d' . $d . '.key_id' . $this->generateInIdsQuery($keyIds) . ' AND d' . $d . '.inx_id' . $this->generateInIdsQuery($ids) . ')';
				
				//$query .= ' INNER JOIN @__index_words as w' . $j . ' ON (w' . $j . '.id' . $this->generateInIdsQuery($ids) . ')';
				$first = false;
			}
		} else {
			$query .= ' INNER JOIN @__index_data as d ON (d.product_id=p.ID AND d.inx_mode=1 AND d.key_id' . $this->generateInIdsQuery($keyIds) . ' AND d.inx_id' . $this->generateInIdsQuery($fussyIds) . ')';
		}
		
		$query .= $this->searchQueryParts['where'][$withVars] . $this->searchType[$forTypes];
		return $query;
	}
	
	public function generateInIdsQuery( $keys ) {
		return ( count($keys) > 1 ? ' IN (' . implode(',', $keys) . ')' : '=' . $keys[0] );
	}
	public function generateInStrQuery( $keys ) {
		return ( count($keys) > 1 ? " IN ('" . implode("','", $keys) . "')" : "='" . $keys[0] . "'" );
	}
	
	public function generateLikeString( $str, $like = true ) {
		return ( $like ? " LIKE '%" : "='" ) . $str . ( $like ? "%'" : "'" );
	}

	public function generateLikesString( $field, $words, $like = true ) {
		if ($like) {
			$str = '';
			foreach ($words as $word) {
				$str .= $field . " LIKE '%" . $word . "%' OR ";
			}
			return '(' . substr($str, 0, -3) . ')';
		}
		return $field . $this->generateInStrQuery($words);
	}
	
	public function splitTextKeys( $keyIds ) {
		$text = array();
		$notText = array();
		foreach ($keyIds as $id) {
			if (in_array($id, $this->indexTextKeys)) {
				$text[] = $id;
			} else {
				$notText[] = $id;
			}
		}
		return array('text' => $text, 'notText' => $notText);
	}
	
	public function generateQuerySearchWords( $words, $keyIds, $i, $fullWords, $forTypes, $isAnd ) {
		$withVars = !empty($forTypes);
		$query = 'INSERT IGNORE INTO ' . $this->searchTempTable .
			' SELECT ' . $this->searchQueryParts['select'][$withVars] . $i .
			' FROM #__posts as p' . $this->searchQueryParts['join'][$withVars] .
			' INNER JOIN @__index_data as d ON (d.product_id=p.ID AND d.inx_mode=1 AND d.key_id' . $this->generateInIdsQuery($keyIds) . ')';
		$j = 0;
		if ($isAnd) {
			$first = true;
			$d = '';
			foreach ($words as $word => $synonyms) {
				$j++;
				if (!$first) {
					$d = $j;
					$query .= ' INNER JOIN @__index_data as d' . $d . ' ON (d' . $d . '.product_id=p.ID AND d' . $d . '.inx_mode=1 AND d' . $d . '.key_id' . $this->generateInIdsQuery($keyIds) . ')';
				}
				if (empty($synonyms)) {
					$query .= ' INNER JOIN @__index_words as w' . $j . ' ON (w' . $j . '.id=d' . $d . '.inx_id AND w' . $j . '.value' . $this->generateLikeString($word, !$fullWords) . ')';
				} else {
					$query .= ' INNER JOIN @__index_words as w' . $j . ' ON (w' . $j . '.id=d' . $d . '.inx_id AND w' . $j . '.value' . $this->generateLikesString('w' . $j . '.value', $synonyms, !$fullWords) . ')';
				}
				$first = false;
			}
			$query .= $this->searchQueryParts['where'][$withVars] . $this->searchType[$forTypes];
		} else {
			$query .= ' INNER JOIN @__index_words as w' . $j . ' ON (w' . $j . '.id=d.inx_id)' .
				$this->searchQueryParts['where'][$withVars] . $this->searchType[$forTypes];
			if ($fullWords) {
				$query .= ' AND w' . $j . '.value' . $this->generateInStrQuery($words);
			} else {
				$w = ' AND (';
				foreach ($words as $word) {
					$w .= ' w' . $j . ".value LIKE '%" . $word . "%' OR ";
				}
				$query .= substr($w, 0, -3) . ')';
			}
		}
		return $query;
	}
	public function generateQuerySearchWordsForTexts( $words, $keyIds, $i, $fullWords, $forTypes, $isAnd ) {
		$withVars = !empty($forTypes);
		
		$query = 'INSERT IGNORE INTO ' . $this->searchTempTable .
			' SELECT ' . $this->searchQueryParts['select'][$withVars] . $i .
			' FROM #__posts as p' . $this->searchQueryParts['join'][$withVars] .
			' INNER JOIN @__index_texts as d ON (d.product_id=p.ID AND d.key_id' . $this->generateInIdsQuery($keyIds) . ')' .
			$this->searchQueryParts['where'][$withVars] . $this->searchType[$forTypes];
		$str = '';
		foreach ($words as $word) {
			$str .= ( $isAnd ? '+' : '' ) . $word . ( $fullWords ? '' : '*' ) . ' ';
		}
		$query .= " AND MATCH (value) AGAINST ('" . substr($str, 0, -1) . "'  IN BOOLEAN MODE)";
		
		return $query;
	}
	
	public function generateQuerySearchPhrases( $phrase, $keyIds, $i, $fullPhrase, $forTypes ) {
		$withVars = !empty($forTypes);
		$query = 'INSERT IGNORE INTO ' . $this->searchTempTable .
			' SELECT ' . $this->searchQueryParts['select'][$withVars] . $i .
			' FROM #__posts as p' . $this->searchQueryParts['join'][$withVars] .
			' INNER JOIN @__index_data as d ON (d.product_id=p.ID AND d.inx_mode=0 AND d.key_id' . $this->generateInIdsQuery($keyIds) . ')' .
			' INNER JOIN @__index_phrases as f ON (f.id=d.inx_id)' .
			$this->searchQueryParts['where'][$withVars] . $this->searchType[$forTypes];
		if ($fullPhrase) {
			$query .= " AND f.hash=MD5('" . $phrase . "')";
		} else {
			$query .= " AND f.value LIKE '%" . $phrase . "%'";
		}
		return $query;
	}
	
	public function generateQuerySearchPhrasesForTexts( $phrase, $keyIds, $i, $fullPhrase, $forTypes ) {
		$withVars = !empty($forTypes);
		$query = 'INSERT IGNORE INTO ' . $this->searchTempTable .
			' SELECT ' . $this->searchQueryParts['select'][$withVars] . $i .
			' FROM #__posts as p' . $this->searchQueryParts['join'][$withVars] .
			' INNER JOIN @__index_texts as d ON (d.product_id=p.ID AND d.key_id' . $this->generateInIdsQuery($keyIds) . ')' .
			$this->searchQueryParts['where'][$withVars] . $this->searchType[$forTypes] . 
			" AND MATCH (value) AGAINST ('\"" . $phrase . "\"'  IN BOOLEAN MODE)";
		return $query;
	}
	public function clearHistoryShedule() {
		$options = FrameAfsw::_()->getModule('options');
		$daySelect = $options->get('schedule_history_day');
		if ( '0' !== $daySelect && gmdate('N') !== $daySelect ) {
			return false;
		}

		$hourSelect = $options->get('schedule_history_hour');
		$timestampShedule = mktime( $hourSelect, 0, 0 );
		if (time() < $timestampShedule) {
			return false;
		}

		$this->getModel('history')->clearHistory();

	}
}
