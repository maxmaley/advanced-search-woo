<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Fields_WidgetAfsw extends ModuleAfsw {
	public $navMenuCode = 'afsw_search_field_nav_item';
	
	public function init() {
		parent::init();
		
		// Menu Nav
		if (is_admin()) {
			add_action('admin_head-nav-menus.php', array($this,'addNavMenuMetaBoxes'));
			add_action('wp_nav_menu_item_custom_fields', array($this, 'addNavMenuItemCustomFields'), 10, 2);
			add_filter('wp_setup_nav_menu_item', array($this, 'setupNavMenuItem'), 10, 2);
			add_action('wp_update_nav_menu_item', array($this, 'updateNavMenuItem'), 10, 3);
		} else {
			add_filter('walker_nav_menu_start_el', array($this, 'displaySearchFieldInMenu'), 50, 2);
			add_filter('megamenu_walker_nav_menu_start_el', array($this, 'displaySearchFieldInMenu'), 50, 2);
		}
		
		// Widget
		add_action('widgets_init', array($this, 'registerWidget'));
		// Elementor
		//if (did_action('elementor/loaded')) {
			add_action('elementor/widgets/register', array($this, 'registerElementorWidget'));
			
		//}
		//add_action( 'elementor/editor/before_enqueue_scripts', array($this, 'fieldsElementorEditorScripts') );
	
	}
	public function registerWidget() {
		return register_widget('AfswFieldsWidget');
	}
	
	public function registerElementorWidget() {
		require_once __DIR__ . '/elementor/fields.php';
		\Elementor\Plugin::instance()->widgets_manager->register(new Fields_ElementorWidgetAfsw());
	}
	
	/*public function fieldsElementorEditorScripts() {
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			$isPro = FrameWpf::_()->isPro();
			$modPath = FrameWpf::_()->getModule('woofilters')->getModPath();
			$modPathW = FrameWpf::_()->getModule('woofilters_widget')->getModPath();

			FrameWpf::_()->getModule('templates')->loadCoreJs();
			FrameWpf::_()->getModule('templates')->loadAdminCoreJs();
			wp_enqueue_style( 'wp-color-picker' );

			FrameWpf::_()->getModule('templates')->loadCoreCss();
			FrameWpf::_()->getModule('templates')->loadChosenSelects();
			FrameWpf::_()->addScript('notify-js', WPF_JS_PATH . 'notify.js', array(), false, true);
			FrameWpf::_()->addScript('chosen.order.jquery.min.js', $modPath . 'js/chosen.order.jquery.min.js');
			FrameWpf::_()->addJSVar('wp-color-picker', 'wpColorPickerL10n', array());
			FrameWpf::_()->addScript('admin.filters', $modPath . 'js/admin.woofilters.js', array('wp-color-picker'));
			FrameWpf::_()->addScript('admin.wp.colorpicker.alhpa.js', WPF_JS_PATH . 'admin.wp.colorpicker.alpha.js', array('wp-color-picker'), WPF_VERSION);

			FrameWpf::_()->addStyle('admin.filters', $modPath . 'css/admin.woofilters.css');
			FrameWpf::_()->addStyle('frontend.multiselect', $modPath . 'css/frontend.multiselect.css');
			FrameWpf::_()->addScript('frontend.multiselect', $modPath . 'js/frontend.multiselect.js');
			
			if ( $isPro ) {
				$modPathPRO = FrameWpf::_()->getModule('woofilterpro')->getModPath();
				$modDirPRO = FrameWpf::_()->getModule('woofilterpro')->getModDir();
				FrameWpf::_()->addScript('admin.filters.pro', $modPathPRO . 'js/admin.woofilters.pro.js', array('jquery'));
				FrameWpf::_()->addStyle('admin.filters.pro', $modPathPRO . 'css/admin.woofilters.pro.css');
				$jsData = file_exists($modDirPRO . 'files/fontAwesomeList.txt') ? file($modDirPRO . 'files/fontAwesomeList.txt') : array();
				if (!empty($jsData)) {
					$jsData = array_map(function( $item ) {
						return 'fa-' . trim($item); 
					}, $jsData);
				}
				FrameWpf::_()->addJSVar('admin.filters.pro', 'FONT_AWESOME_DATA', $jsData);
			}
			
			FrameWpf::_()->addStyle('admin.woofilters.elementor', $modPathW . 'css/admin.woofilters.elementor.css', false, WPF_VERSION);
			FrameWpf::_()->addScript('admin.woofilters.elementor', $modPathW . 'js/admin.woofilters.elementor.js', array('admin.filters'), WPF_VERSION, true);
			
			FrameWpf::_()->addJSVar('admin.filters', 'isElementorEditMode', '1');

			FrameWpf::_()->addJSVar('admin.filters', 'url', admin_url('admin-ajax.php'));
			list( $filtersOpts, $filtersSettings ) = $this->getFiltersSettings();
			FrameWpf::_()->addJSVar('admin.filters', 'filtersSettings', $filtersSettings);
			FrameWpf::_()->addJSVar('admin.filters', 'wpfNonce', wp_create_nonce('wpf-save-nonce'));
		}

	}*/
	
	public function addNavMenuMetaBoxes() {
		if ($this->isNavMenuScreen() ) {
			FrameAfsw::_()->addScript('afsw_nav_menu', FrameAfsw::_()->getModule('fields_widget')->getModPath() . 'assets/js/nav_menu.js');
		}
		add_meta_box('afsw_field_nav_link', __('Advanced Fuzzy Search', 'advanced-fuzzy-search'), array($this->getView(), 'displayFormNavMenuBox'), 'nav-menus', 'side', 'low');
	}
	public function addNavMenuItemCustomFields( $item_id, $item ) {
		if ($item->post_title !== $this->navMenuCode && $item->post_excerpt !== $this->navMenuCode) {
			return;
		}
		$this->getView()->displayFormNavMenuItem($item_id, $item);
	}
	public function setupNavMenuItem( $item ) {
		if ($item->post_title == $this->navMenuCode || $item->post_excerpt == $this->navMenuCode) {
			$item->type_label = __('Search Field', 'advanced-fuzzy-search');
			$item->title = __('Advanced Fuzzy Search', 'advanced-fuzzy-search');
			$item->post_excerpt = $this->navMenuCode;
		}
		return $item;
	}
	private function isNavMenuScreen() {
		$isNav  = false;
		$screen = get_current_screen();
		if (!empty( $screen->id) && ( 'nav-menus' === $screen->id )) {
			$isNav = true;
		}

		return $isNav;
	}
	public function updateNavMenuItem( $menu_id, $menu_item_db_id, $args ) {
		if (!isset($args['menu-item-attr-title']) || $args['menu-item-attr-title'] !== $this->navMenuCode) {
			return;
		}
		$params = ReqAfsw::get('post');
		$fieldId = isset( $params['menu-item-afsw-search'][ $menu_item_db_id ] ) ? sanitize_key( $params['menu-item-afsw-search'][ $menu_item_db_id ] ) : '';
		update_post_meta( $menu_item_db_id, '_menu_item_afsw_search_field_id', $fieldId );
	}
	public function displaySearchFieldInMenu( $itemOutput, $item ) {
		if (!empty( $itemOutput ) && is_string($itemOutput) && strpos($itemOutput, $this->navMenuCode) !== false) {
			return $this->getView()->displaySearchFieldInMenu($item);
		}
		return $itemOutput;
	}
}
/**
 * Maps widget class
 */
class AfswFieldsWidget extends WP_Widget {
	public function __construct() {
		$widgetOps = array(
			'classname' => 'AfswFieldsWidget',
			'description' => esc_html__('Displays Search Field', 'advanced-fuzzy-search')
		);
		parent::__construct('AfswFieldsWidget', __('Advanced Fuzzy Search', 'advanced-fuzzy-search'), $widgetOps);
	}
	public function widget( $args, $instance ) {
		if (is_array($args)) {
			extract( $args );
		}
		extract($instance);
		FrameAfsw::_()->getModule('fields_widget')->getView()->displayWidget($instance, $args);
	}
	public function form( $instance ) {
		extract($instance);
		FrameAfsw::_()->getModule('fields_widget')->getView()->displayFormWidget($instance, $this);
	}
	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}
	
}
