<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class AssetsAfsw {
	protected $_styles = array();
	private $_cdnUrl = '';

	public function init() {
		$this->getCdnUrl();
		if (is_admin()) {
			$isAdminPlugOptsPage = FrameAfsw::_()->isAdminPlugOptsPage();
			if ($isAdminPlugOptsPage) {
				$this->loadAdminCoreJs();
				$this->loadCoreCss();
				$this->loadBootstrap();
				$this->loadFontAwesome();
				$this->loadJqueryUi();
				FrameAfsw::_()->addScript('afsw-admin-options', AFSW_JS_PATH . 'admin.options.js', array(), false, true);
				add_action('admin_enqueue_scripts', array($this, 'loadMediaScripts'));
				add_action('init', array($this, 'connectAdditionalAdminAssets'));
			}
			// Some common styles - that need to be on all admin pages - be careful with them
			FrameAfsw::_()->addStyle('woobewoo-for-all-admin-' . AFSW_CODE, AFSW_CSS_PATH . 'woobewoo-for-all-admin.css');
		}
	}
	public static function getInstance() {
		static $instance;
		if (!$instance) {
			$instance = new AssetsAfsw();
		}
		return $instance;
	}
	public static function _() {
		return self::getInstance();
	}
	public function getCdnUrl() {
		if (empty($this->_cdnUrl)) {
			if ((int) FrameAfsw::_()->getModule('options')->get('use_local_cdn')) {
				$uploadsDir = wp_upload_dir( null, false );
				$this->_cdnUrl = $uploadsDir['baseurl'] . '/' . AFSW_CODE . '/';
				if (UriAfsw::isHttps()) {
					$this->_cdnUrl = str_replace('http://', 'https://', $this->_cdnUrl);
				}
			} else {
				$this->_cdnUrl = ( UriAfsw::isHttps() ? 'https' : 'http' ) . '://woobewoo-14700.kxcdn.com/';
			}
		}
		return $this->_cdnUrl;
	}

	public function connectAdditionalAdminAssets() {
		if (is_rtl()) {
			FrameAfsw::_()->addStyle('afsw-style-rtl', AFSW_CSS_PATH . 'style-rtl.css');
		}
	}
	public function loadMediaScripts() {
		if (function_exists('wp_enqueue_media')) {
			wp_enqueue_media();
		}
	}
	public function loadAdminCoreJs() {
		FrameAfsw::_()->addScript('jquery-ui-dialog');
		FrameAfsw::_()->addScript('jquery-ui-slider');
	}
	public function loadCoreJs( $nonce = true) {
		static $loaded = false;
		if (!$loaded) {
			FrameAfsw::_()->addScript('jquery');
			FrameAfsw::_()->addScript('afsw-core', AFSW_JS_PATH . 'core.js');
			FrameAfsw::_()->addScript('afsw-notify-js', AFSW_JS_PATH . 'notify.js', array(), false, true);

			$ajaxurl = admin_url('admin-ajax.php');
			$jsData = array(
				'siteUrl' => AFSW_SITE_URL,
				'imgPath' => AFSW_IMG_PATH,
				'cssPath' => AFSW_CSS_PATH,
				'loader' => AFSW_LOADER_IMG,
				'close'	=> AFSW_IMG_PATH . 'cross.gif',
				'ajaxurl' => $ajaxurl,
				'AFSW_CODE' => AFSW_CODE,
				'jsPath' => AFSW_JS_PATH,
				'libPath' => AFSW_LIB_PATH,
				'isPro' => FrameAfsw::_()->isPro()
			);
			if ($nonce) {
				$jsData['afswNonce'] = wp_create_nonce('afsw-nonce');
			}
			$jsData = DispatcherAfsw::applyFilters('jsInitVariables', $jsData);
			FrameAfsw::_()->addJSVar('afsw-core', 'AFSW_DATA', $jsData);
			$this->loadTooltipster();
			$loaded = true;
		}
	}
	public function loadTooltipster() {
		$path = AFSW_LIB_PATH . 'tooltipster/';
		FrameAfsw::_()->addScript('tooltipster', $path . 'jquery.tooltipster.min.js');
		FrameAfsw::_()->addStyle('tooltipster', $path . 'tooltipster.css');
	}
	public function loadSlimscroll() {
		FrameAfsw::_()->addScript('jquery.slimscroll', AFSW_JS_PATH . 'slimscroll.min.js');
	}
	public function loadLoaders() {
		FrameAfsw::_()->addStyle('afsw-loaders', AFSW_CSS_PATH . 'loaders.css');
	}
	public function loadCoreCss() {
		$this->_styles = array(
			'afsw-style'			=> array('path' => AFSW_CSS_PATH . 'style.css', 'for' => 'admin'),
			'afsw-woobewoo-ui'	=> array('path' => AFSW_CSS_PATH . 'woobewoo-ui.css', 'for' => 'admin'),
			'dashicons'			=> array('for' => 'admin'),
			'bootstrap-alerts'	=> array('path' => AFSW_CSS_PATH . 'bootstrap-alerts.css', 'for' => 'admin'),
		);
		foreach ($this->_styles as $s => $sInfo) {
			if (!empty($sInfo['path'])) {
				FrameAfsw::_()->addStyle($s, $sInfo['path']);
			} else {
				FrameAfsw::_()->addStyle($s);
			}
		}
		$this->loadFontAwesome();
	}
	public function loadAdminEndCss() {
		FrameAfsw::_()->addStyle('afsw-admin-options', AFSW_CSS_PATH . 'admin.options.css');
	}
	public function loadColorPicker() {
		$path = AFSW_LIB_PATH . 'colorpicker/';
		FrameAfsw::_()->addScript('afsw-colorpicker', $path . 'colorpicker.js');
		FrameAfsw::_()->addStyle('afsw-colorpicker', $path . 'colorpicker.css');
	}
	public function loadJqueryUi() {
		static $loaded = false;
		if (!$loaded) {
			//Includes: widget.js, position.js, data.js, disable-selection.js, effect.js, effects/effect-blind.js, effects/effect-bounce.js, effects/effect-clip.js, effects/effect-drop.js, effects/effect-explode.js, effects/effect-fade.js, effects/effect-fold.js, effects/effect-highlight.js, effects/effect-puff.js, effects/effect-pulsate.js, effects/effect-scale.js, effects/effect-shake.js, effects/effect-size.js, effects/effect-slide.js, effects/effect-transfer.js, focusable.js, form-reset-mixin.js, jquery-1-7.js, keycode.js, labels.js, scroll-parent.js, tabbable.js, unique-id.js, widgets/accordion.js, widgets/autocomplete.js, widgets/button.js, widgets/checkboxradio.js, widgets/controlgroup.js, widgets/datepicker.js, widgets/dialog.js, widgets/draggable.js, widgets/droppable.js, widgets/menu.js, widgets/mouse.js, widgets/progressbar.js, widgets/resizable.js, widgets/selectable.js, widgets/selectmenu.js, widgets/slider.js, widgets/sortable.js, widgets/spinner.js, widgets/tabs.js, widgets/tooltip.js
			//FrameAfsw::_()->addScript('jquery-ui', AFSW_JS_PATH . 'jquery-ui.min.js');
			$this->loadDatePicker();
			FrameAfsw::_()->addScript('jquery-ui');
			FrameAfsw::_()->addStyle('jquery-ui', AFSW_CSS_PATH . 'jquery-ui.min.css');
			$loaded = true;
		}
	}
	public function loadDataTables( $extensions = array(), $jqueryui = false ) {
		$frame = FrameAfsw::_();
		$path = AFSW_LIB_PATH . 'datatables/';
		$frame->addScript('afsw-dt-js', $path . 'js/jquery.dataTables.min.js');
		$frame->addStyle('afsw-dt-css', $path . 'css/jquery.dataTables.min.css');

		foreach ($extensions as $ext) {
			$frame->addScript('afsw-dt-' . $ext, $path . 'js/dataTables.' . $ext . '.min.js');
			$frame->addStyle('afsw-dt-' . $ext, $path . 'css/' . $ext . '.dataTables.min.css');
		}
	}
	public function loadFontAwesome() {
		FrameAfsw::_()->addStyle('afsw-font-awesome', AFSW_CSS_PATH . 'font-awesome.min.css');
	}
	public function loadChosenSelects() {
		$path = AFSW_LIB_PATH . 'chosen/';
		FrameAfsw::_()->addStyle('afsw-jquery-chosen', $path . 'chosen.min.css');
		FrameAfsw::_()->addScript('afsw-jquery-chosen', $path . 'chosen.jquery.min.js');
	}
	public function loadDateTimePicker() {
		$path = AFSW_LIB_PATH . 'datetimepicker/';
		FrameAfsw::_()->addScript('jquery-ui-datepicker');
		FrameAfsw::_()->addStyle('afsw-jquery-datetime', $path . 'jquery-ui-timepicker-addon.css');
		FrameAfsw::_()->addScript('afsw-jquery-datetime', $path . 'jquery-ui-timepicker-addon.js');
	}
	public function loadDatePicker() {
		FrameAfsw::_()->addScript('jquery-ui-datepicker');
	}
	public function loadSortable() {
		static $loaded = false;
		if (!$loaded) {
			FrameAfsw::_()->addScript('jquery-ui-core');
			FrameAfsw::_()->addScript('jquery-ui-widget');
			FrameAfsw::_()->addScript('jquery-ui-mouse');

			FrameAfsw::_()->addScript('jquery-ui-draggable');
			FrameAfsw::_()->addScript('jquery-ui-sortable');
			$loaded = true;
		}
	}
	public function loadBootstrap() {
		static $loaded = false;
		if (!$loaded) {
			FrameAfsw::_()->addStyle('bootstrap.min', AFSW_CSS_PATH . 'bootstrap.min.css');
			$loaded = true;
		}
	}
}
