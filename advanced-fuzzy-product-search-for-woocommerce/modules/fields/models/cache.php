<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class CacheModelAfsw extends ModelAfsw {

	public function __construct() {
		$this->_setTbl('cache');
	}
	
	public function saveCache( $cacheId, $fieldId, $search, $html ) {
		$html = htmlentities(htmlspecialchars($html));
		if (!empty($cacheId)) {
			$query = "UPDATE @__cache SET html='" . $html . "', updated=CURRENT_TIMESTAMP";
		} else {
			$query = 'INSERT IGNORE INTO @__cache (`field_id`, `search`, `where`, `html`)' .
				' VALUES(' . $fieldId . ",'" . $search['s'] . "','" . ( empty($search['where']) ? '' : $where ) . "','" . $html . "')";
		}
		DbAfsw::query($query);
		return true;
	}
	
	public function getCache( $fieldId, $search ) {
		$query = 'SELECT id, html, TIMESTAMPDIFF(MINUTE, updated, CURRENT_TIMESTAMP) as minutes FROM @__cache' .
			' WHERE field_id=' . $fieldId . 
			" AND search='" . $search['s'] . "'" .
			" AND `where`='" . ( empty($search['where']) ? '' : $where ) . "'" .
			' LIMIT 1';
		return DbAfsw::get($query, 'row');
	}
	
	public function clearCache( $fieldId ) {
		$query = 'DELETE FROM @__cache WHERE field_id=' . $fieldId;
		DbAfsw::query($query);
		return true;
	}
}
