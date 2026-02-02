<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $wpdb;
if (!defined('WPLANG') || WPLANG == '') {
	define('AFSW_WPLANG', 'en_GB');
} else {
	define('AFSW_WPLANG', WPLANG);
}
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

define('AFSW_PLUG_NAME', basename(dirname(__FILE__)));
define('AFSW_DIR', WP_PLUGIN_DIR . DS . AFSW_PLUG_NAME . DS);
define('AFSW_CLASSES_DIR', AFSW_DIR . 'classes' . DS);
define('AFSW_TABLES_DIR', AFSW_CLASSES_DIR . 'tables' . DS);
define('AFSW_HELPERS_DIR', AFSW_CLASSES_DIR . 'helpers' . DS);
define('AFSW_LANG_DIR', AFSW_DIR . 'languages' . DS);
define('AFSW_ASSETS_DIR', AFSW_DIR . 'common' . DS);
define('AFSW_IMG_DIR', AFSW_ASSETS_DIR . 'img' . DS);
define('AFSW_JS_DIR', AFSW_ASSETS_DIR . 'js' . DS);
define('AFSW_LIB_DIR', AFSW_ASSETS_DIR . 'lib' . DS);
define('AFSW_MODULES_DIR', AFSW_DIR . 'modules' . DS);
define('AFSW_ADMIN_DIR', ABSPATH . 'wp-admin' . DS);

define('AFSW_PLUGINS_URL', plugins_url());
define('AFSW_SITE_URL', get_bloginfo('wpurl') . '/');
define('AFSW_LIB_PATH', AFSW_PLUGINS_URL . '/' . AFSW_PLUG_NAME . '/common/lib/');
define('AFSW_JS_PATH', AFSW_PLUGINS_URL . '/' . AFSW_PLUG_NAME . '/common/js/');
define('AFSW_CSS_PATH', AFSW_PLUGINS_URL . '/' . AFSW_PLUG_NAME . '/common/css/');
define('AFSW_IMG_PATH', AFSW_PLUGINS_URL . '/' . AFSW_PLUG_NAME . '/common/img/');
define('AFSW_MODULES_PATH', AFSW_PLUGINS_URL . '/' . AFSW_PLUG_NAME . '/modules/');

define('AFSW_URL', AFSW_SITE_URL);

define('AFSW_LOADER_IMG', AFSW_IMG_PATH . 'loading.gif');
define('AFSW_TIME_FORMAT', 'H:i:s');
define('AFSW_DATE_DL', '/');
define('AFSW_DATE_FORMAT', 'm/d/Y');
define('AFSW_DATE_FORMAT_HIS', 'm/d/Y (' . AFSW_TIME_FORMAT . ')');
//define('AFSW_DATE_FORMAT', 'YY-MM-DD');
//define('AFSW_DATE_FORMAT_HIS', 'YY-MM-DD (' . AFSW_TIME_FORMAT . ')');
define('AFSW_DB_PREF', 'afsw_');
define('AFSW_MAIN_FILE', 'advanced-fuzzy-search.php');

define('AFSW_DEFAULT', 'default');

define('AFSW_VERSION', '1.0.2');

define('AFSW_CLASS_PREFIX', 'afswc');
define('AFSW_TEST_MODE', true);

define('AFSW_ADMIN', 'admin');
define('AFSW_LOGGED', 'logged');
define('AFSW_GUEST', 'guest');

define('AFSW_METHODS', 'methods');
define('AFSW_USERLEVELS', 'userlevels');
/**
 * Framework instance code
 */
define('AFSW_CODE', 'afsw');
/**
 * Plugin name
 */
define('AFSW_WP_PLUGIN_NAME', 'Advanced Fuzzy Product Search for WooCommerce');
/**
 * Custom defined for plugin
 */
define('AFSW_SHORTCODE', 'afsw-fields');
