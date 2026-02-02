<?php
/**
 * Plugin Name: Advanced Fuzzy Product Search for WooCommerce
 * Plugin URI: https://woobewoo.com/plugins/advanced-fuzzy-search/
 * Description: Powerful product search plugin with search suggestions to make the search process easy. Offer the visitor the products in the shortest possible time
 * Version: 1.0.2
 * Author: fuzzysa
 * Text Domain: advanced-fuzzy-search
 * Domain Path: /languages
 **/
/**
 * Base config constants and functions
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions.php');
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );
/**
 * Connect all required core classes
 */
importClassAfsw('DbAfsw');
importClassAfsw('InstallerAfsw');
importClassAfsw('BaseObjectAfsw');
importClassAfsw('ModuleAfsw');
importClassAfsw('ModelAfsw');
importClassAfsw('ViewAfsw');
importClassAfsw('ControllerAfsw');
importClassAfsw('HelperAfsw');
importClassAfsw('DispatcherAfsw');
importClassAfsw('FieldAfsw');
importClassAfsw('TableAfsw');
importClassAfsw('FrameAfsw');

importClassAfsw('ReqAfsw');
importClassAfsw('UriAfsw');
importClassAfsw('HtmlAfsw');
importClassAfsw('ResponseAfsw');
importClassAfsw('FieldAdapterAfsw');
importClassAfsw('ValidatorAfsw');
importClassAfsw('ErrorsAfsw');
importClassAfsw('UtilsAfsw');
importClassAfsw('ModInstallerAfsw');
importClassAfsw('InstallerDbUpdaterAfsw');
importClassAfsw('DateAfsw');
importClassAfsw('AssetsAfsw');
importClassAfsw('CacheAfsw');
importClassAfsw('UserAfsw');
/**
 * Check plugin version - maybe we need to update database, and check global errors in request
 */
InstallerAfsw::update();
ErrorsAfsw::init();
/**
 * Start application
 */
FrameAfsw::_()->parseRoute();
FrameAfsw::_()->init();
FrameAfsw::_()->exec();
