<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Shell - class to work with $wpdb global object
 */
class DbAfsw {
	/**
	 * Execute query and return results
	 *
	 * @param string $query query to be executed
	 * @param string $get what must be returned - one value (one), one row (row), one col (col) or all results (all - by default)
	 * @param const $outputType type of returned data
	 * @return mixed data from DB
	 */
	public static $query = '';
	public static function get( $query, $get = 'all', $outputType = ARRAY_A ) {
		global $wpdb;
		$get = strtolower($get);
		$res = null;
		$query = self::prepareQuery($query);
		self::$query = $query;
		$wpdb->afsw_prepared_query = $query;
		switch ($get) {
			case 'one':
				$res = $wpdb->get_var($wpdb->afsw_prepared_query);
				break;
			case 'row':
				$res = $wpdb->get_row($wpdb->afsw_prepared_query, $outputType);
				break;
			case 'col':
				$res = $wpdb->get_col($wpdb->afsw_prepared_query);
				break;
			case 'all':
			default:
				$res = $wpdb->get_results($wpdb->afsw_prepared_query, $outputType);
				break;
		}
		return $res;
	}
	/**
	 * Execute one query
	 *
	 * @return query results
	 */
	public static function query( $query, $affected = false ) {
		global $wpdb;
		$wpdb->afsw_prepared_query = self::prepareQuery($query);
		return $affected ? $wpdb->query($wpdb->afsw_prepared_query) : ( $wpdb->query($wpdb->afsw_prepared_query) === false ? false : true );
	}
	/**
	 * Get last insert ID
	 *
	 * @return int last ID
	 */
	public static function insertID() {
		global $wpdb;
		return $wpdb->insert_id;
	}
	/**
	 * Get number of rows returned by last query
	 *
	 * @return int number of rows
	 */
	public static function numRows() {
		global $wpdb;
		return $wpdb->num_rows;
	}
	/**
	 * Replace prefixes in custom query. Suported next prefixes:
	 * #__  Worafswess prefix
	 * ^__  Store plugin tables prefix (@see AFSW_DB_PREF if config.php)
	 *
	 * @__  Compared of WP table prefix + Store plugin prefix (@example wp_s_)
	 * @param string $query query to be executed
	 */
	public static function prepareQuery( $query ) {
		global $wpdb;
		return str_replace(
				array('#__', '^__', '@__'), 
				array($wpdb->prefix, AFSW_DB_PREF, $wpdb->prefix . AFSW_DB_PREF),
				$query);
	}
	public static function getError() {
		global $wpdb;
		return $wpdb->last_error;
	}
	public static function lastID() {
		global $wpdb;        
		return $wpdb->insert_id;
	}
	public static function timeToDate( $timestamp = 0 ) {
		if ($timestamp) {
			if (!is_numeric($timestamp)) {
				$timestamp = dateToTimestampAfsw($timestamp);
			}
			return gmdate('Y-m-d', $timestamp);
		} else {
			return gmdate('Y-m-d');
		}
	}
	public static function dateToTime( $date ) {
		if (empty($date)) {
			return '';
		}
		if (strpos($date, AFSW_DATE_DL)) {
			return dateToTimestampAfsw($date);
		}
		$arr = explode('-', $date);
		return dateToTimestampAfsw($arr[2] . AFSW_DATE_DL . $arr[1] . AFSW_DATE_DL . $arr[0]);
	}
	public static function exist( $table, $column = '', $value = '' ) {
		if (empty($column) && empty($value)) {       //Check if table exist
			$res = self::get('SHOW TABLES LIKE "' . $table . '"', 'one');
		} elseif (empty($value)) {                   //Check if column exist
			$res = self::get('SHOW COLUMNS FROM ' . $table . ' LIKE "' . $column . '"', 'one');
		} else {                                    //Check if value in column table exist
			$res = self::get('SELECT COUNT(*) AS total FROM ' . $table . ' WHERE ' . $column . ' = "' . $value . '"', 'one');
		}
		return !empty($res);
	}
	public static function prepareHtml( $d ) {
		if (is_array($d)) {
			foreach ($d as $i => $el) {
				$d[ $i ] = self::prepareHtml( $el );
			}
		} else {
			$d = esc_html($d);
		}
		return $d;
	}
	public static function prepareHtmlIn( $d ) {
		if (is_array($d)) {
			foreach ($d as $i => $el) {
				$d[ $i ] = self::prepareHtml( $el );
			}
		} else {
			$d = wp_filter_nohtml_kses($d);
		}
		return $d;
	}
	public static function escape( $data ) {
		global $wpdb;
		return $wpdb->_escape($data);
	}
	public static function getTableColumns( $table ) {
		return self::get('SHOW COLUMNS FROM ' . $table);
	}
	public static function getAutoIncrement( $table ) {
		return (int) self::get('SELECT AUTO_INCREMENT
			FROM information_schema.tables
			WHERE table_name = "' . $table . '"
			AND table_schema = DATABASE( );', 'one');
	}
	public static function setAutoIncrement( $table, $autoIncrement ) {
		return self::query('ALTER TABLE `' . $table . '` AUTO_INCREMENT = ' . $autoIncrement . ';');
	}
	public static function createTemporaryTable( $table, $sql, $strusture = false ) {
		$resultTable = $table;
		if (!self::query('DROP TEMPORARY TABLE IF EXISTS ' . $table )) {
			return false;
		}
		if (!empty($sql)) {
			$sql = str_replace('SQL_CALC_FOUND_ROWS', '', $sql);
			$orderPos = strpos($sql, 'ORDER');
			if ($orderPos) {
				$sql = substr($sql, 0, $orderPos);
			}
		}
		$query = 'CREATE TEMPORARY TABLE ' . $table .
			' (' . ( $strusture ? $strusture : 'index my_pkey (id)' ) . ')' .
			( empty($sql) ? '' : ' AS ' . $sql );
		if (self::query($query, false) === false ) {
			$resultTable = empty($sql) ? false : '(' . $sql . ')';
		}

		return $resultTable;
	}
}
