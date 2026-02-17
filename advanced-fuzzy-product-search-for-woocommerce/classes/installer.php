<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class InstallerAfsw {
	public static $update_to_version_method = '';
	private static $_firstTimeActivated = false;
	public static function init( $isUpdate = false ) {
		global $wpdb;
		$wpPrefix = $wpdb->prefix; /* add to 0.0.3 Versiom */
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$current_version = get_option($wpPrefix . AFSW_DB_PREF . 'db_version', 0);
		if (!$current_version) {
			self::$_firstTimeActivated = true;
		}
		/**
		 * Table modules 
		 */
		if (!DbAfsw::exist('@__modules')) {
			dbDelta(DbAfsw::prepareQuery("CREATE TABLE IF NOT EXISTS `@__modules` (
			  `id` smallint(3) NOT NULL AUTO_INCREMENT,
			  `code` varchar(32) NOT NULL,
			  `active` tinyint(1) NOT NULL DEFAULT '0',
			  `type_id` tinyint(1) NOT NULL DEFAULT '0',
			  `label` varchar(64) DEFAULT NULL,
			  `ex_plug_dir` varchar(255) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE INDEX `code` (`code`)
			) DEFAULT CHARSET=utf8;"));
			DbAfsw::query("INSERT INTO `@__modules` (id, code, active, type_id, label) VALUES
				(NULL, 'adminmenu',1,1,'Admin Menu'),
				(NULL, 'overview',1,1,'overview'),
				(NULL, 'options',1,1,'Options'),
				(NULL, 'fields',1,1,'Fields'),
				(NULL, 'fields_widget',1,1,'Widget'),
				(NULL, 'indexing',1,1,'Indexing'),
				(NULL, 'integrations',1,1,'Integrations');");
		}
		
		/**
		 * Table fields
		 */
		if (!DbAfsw::exist('@__fields')) {
			dbDelta(DbAfsw::prepareQuery("CREATE TABLE IF NOT EXISTS `@__fields` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`title` VARCHAR(128) NULL DEFAULT NULL,
				`options` MEDIUMTEXT NOT NULL,
				`field` MEDIUMTEXT NOT NULL,
				`autocomplete` MEDIUMTEXT NOT NULL,
				`search` MEDIUMTEXT NOT NULL,
				`updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`css` text,
				`add_css` text,
				`add_js` text,
				`is_stats` tinyint(1) NOT NULL DEFAULT '0',
				`theme_replace` tinyint(1) NOT NULL DEFAULT '0',
				PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;"));
		}
		/**
		 *  Table history 
		 */
		if (!DbAfsw::exist('@__history')) {
			dbDelta(DbAfsw::prepareQuery('CREATE TABLE IF NOT EXISTS `@__history` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`search` varchar(100) NOT NULL,
				`codes` varchar(100) NOT NULL,
				`field_id` INT(11) NOT NULL,
				`user_id` INT(11) NOT NULL,
				`cnt` INT(1) NOT NULL DEFAULT 0,
				`found` INT(11) NOT NULL DEFAULT 0,
				`status` tinyint(1) NOT NULL DEFAULT 0,
				`added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`last` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				INDEX `inx_user` (`user_id`),
				UNIQUE INDEX `inx_search` (`search`, `user_id`, `field_id`),
				INDEX `inx_codes` (`codes`, `user_id`, `field_id`)
			) DEFAULT CHARSET=utf8;'));
		}
		/**
		 *  Table history 
		 */
		if (!DbAfsw::exist('@__cache')) {
			dbDelta(DbAfsw::prepareQuery('CREATE TABLE IF NOT EXISTS `@__cache` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`field_id` INT(11) NOT NULL,
				`search` varchar(100) NOT NULL,
				`where` varchar(100) NOT NULL,
				`html` text NOT NULL,
				`updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `inx_search` (`field_id`, `search`, `where`)
			) DEFAULT CHARSET=utf8;'));
		}
		/**
		 *  Table synonyms
		 */
		if (!DbAfsw::exist('@__synonyms')) {
			dbDelta(DbAfsw::prepareQuery('CREATE TABLE IF NOT EXISTS `@__synonyms` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`field_id` INT(11) NOT NULL,
				`gr_num` INT(11) NOT NULL,
				`word` varchar(100) NOT NULL,
				PRIMARY KEY (`id`),
				INDEX `field_word` (`field_id`, `word`),
				INDEX `field_gr` (`field_id`, `gr_num`)
			) DEFAULT CHARSET=utf8;'));
		}
		
		/**
		 *  Table index_keys 
		 */
		if (!DbAfsw::exist('@__index_keys')) {
			dbDelta(DbAfsw::prepareQuery('CREATE TABLE IF NOT EXISTS `@__index_keys` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`inx_key` varchar(15) NOT NULL,
				`inx_name` varchar(255) NOT NULL,
				`inx_source` smallint(3) NOT NULL,
				`inx_type` smallint(3) NOT NULL,
				`active` smallint(3) NOT NULL,
				`list` MEDIUMTEXT NOT NULL,
				`parent` smallint(3) NOT NULL,
				`phrases` smallint(3) NOT NULL,
				`words` smallint(3) NOT NULL,
				`with_vars` smallint(3) NOT NULL,
				`status` smallint(3) NOT NULL,
				`added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated` TIMESTAMP,
				`locked` TIMESTAMP,
				`calculated` TIMESTAMP,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `inx_key_name` (`inx_key`, `inx_name`)
			) DEFAULT CHARSET=utf8;'));
		}
		/**
		 *  Table index_data 
		 */
		if (!DbAfsw::exist('@__index_data')) {
			dbDelta(DbAfsw::prepareQuery('CREATE TABLE IF NOT EXISTS `@__index_data` (
				`id` bigint NOT NULL AUTO_INCREMENT,
				`product_id` bigint NOT NULL,
				`pr_type` smallint(3) NOT NULL DEFAULT 0,
				`key_id` INT(11) NOT NULL,
				`inx_mode` smallint(3) NOT NULL DEFAULT 0,
				`inx_id` bigint,
				`updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `product_keys` (`product_id`, `key_id`, `inx_mode`, `inx_id`),
				INDEX `inx_id` (`inx_id`),
				INDEX `inx_key` (`key_id`)
			) DEFAULT CHARSET=utf8;'));
		}
		/**
		 *  Table index_words 
		 */
		if (!DbAfsw::exist('@__index_words')) {
			dbDelta(DbAfsw::prepareQuery('CREATE TABLE IF NOT EXISTS `@__index_words` (
				`id` bigint NOT NULL AUTO_INCREMENT,
				`value` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin,
				`prefix2` char(2) CHARACTER SET utf8 COLLATE utf8_bin,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `inx_value` (`value`),
				INDEX `prefix2` (`prefix2`)
			) DEFAULT CHARSET=utf8;'));
		}
		/**
		 *  Table index_phrases 
		 */
		if (!DbAfsw::exist('@__index_phrases')) {
			dbDelta(DbAfsw::prepareQuery('CREATE TABLE IF NOT EXISTS `@__index_phrases` (
				`id` bigint NOT NULL AUTO_INCREMENT,
				`value` varchar(250) CHARACTER SET utf8 COLLATE utf8_bin,
				`hash` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `inx_hash` (`hash`)
			) DEFAULT CHARSET=utf8;'));
		}
		/**
		 *  Table temp_words 
		 */
		if (!DbAfsw::exist('@__temp_words')) {
			dbDelta(DbAfsw::prepareQuery('CREATE TABLE IF NOT EXISTS `@__temp_words` (
				`phrase_id` bigint NOT NULL AUTO_INCREMENT,
				`word` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin,
				INDEX `phrase_id` (`phrase_id`)
			) DEFAULT CHARSET=utf8;'));
		}
		/**
		 *  Table index_texts 
		 */
		if (!DbAfsw::exist('@__index_texts')) {
			dbDelta(DbAfsw::prepareQuery('CREATE TABLE IF NOT EXISTS `@__index_texts` (
				`id` bigint NOT NULL AUTO_INCREMENT,
				`product_id` bigint NOT NULL,
				`pr_type` smallint(3) NOT NULL DEFAULT 0,
				`key_id` INT(11) NOT NULL,
				`value` longtext CHARACTER SET utf8 COLLATE utf8_general_ci,
				`updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				FULLTEXT `text_index` (`value`),
				INDEX `inx_key` (`key_id`),
				UNIQUE INDEX `product_keys` (`product_id`, `key_id`)
			) DEFAULT CHARSET=utf8;'));
		}
		/**
		 *  Table temp_phrases 
		 */
		if (!DbAfsw::exist('@__temp_phrases')) {
			dbDelta(DbAfsw::prepareQuery('CREATE TABLE IF NOT EXISTS `@__temp_phrases` (
				`id` bigint NOT NULL AUTO_INCREMENT,
				`product_id` INT(11) NOT NULL,
				`pr_type` smallint(3) NOT NULL DEFAULT 0,
				`key_id` INT(11) NOT NULL,
				`phrase` varchar(250) CHARACTER SET utf8 COLLATE utf8_bin,
				`hash` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin,
				`spaces` INT(11) NOT NULL,
				`term_id` BIGINT(11) NOT NULL DEFAULT 0,
				`num` INT(11) NOT NULL DEFAULT 0,
				PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;'));
		}
		
		InstallerDbUpdaterAfsw::runUpdate();
		if ($current_version && !self::$_firstTimeActivated) {
			self::setUsed();
		}
		update_option($wpPrefix . AFSW_DB_PREF . 'db_version', AFSW_VERSION);
		add_option($wpPrefix . AFSW_DB_PREF . 'db_installed', 1);
	}
	public static function setUsed() {
		update_option(AFSW_DB_PREF . 'plug_was_used', 1);
	}
	public static function isUsed() {
		return (int) get_option(AFSW_DB_PREF . 'plug_was_used');
	}
	public static function delete() {
		global $wpdb;
		$wpPrefix = $wpdb->prefix;
		$wpdb->query('DROP TABLE IF EXISTS `' . $wpdb->prefix . esc_sql(AFSW_DB_PREF) . 'modules`');
		delete_option($wpPrefix . AFSW_DB_PREF . 'db_version');
		delete_option($wpPrefix . AFSW_DB_PREF . 'db_installed');
	}
	public static function deactivate() {
		wp_clear_scheduled_hook('afsw_calc_products_indexing');
		wp_clear_scheduled_hook('afsw_calc_indexing_shedule');
		wp_clear_scheduled_hook('afsw_calc_optimizing_shedule');
	}
	public static function update() {
		global $wpdb;
		$wpPrefix = $wpdb->prefix; /* add to 0.0.3 Version */
		$currentVersion = get_option($wpPrefix . AFSW_DB_PREF . 'db_version', 0);
		if (!$currentVersion || version_compare(AFSW_VERSION, $currentVersion, '>')) {
			self::init( true );
			update_option($wpPrefix . AFSW_DB_PREF . 'db_version', AFSW_VERSION);
		}
	}
}
