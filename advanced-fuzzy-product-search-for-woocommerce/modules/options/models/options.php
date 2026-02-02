<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class OptionsModelAfsw extends ModelAfsw {
	private $_values = array();
	private $_valuesLoaded = false;
	
	public function get( $key = '' ) {
		$this->_loadOptValues();
		return empty($key) ? $this->_values : ( isset($this->_values[$key]) ? $this->_values[$key] : false );
	}
	public function reset() {
		$this->_values = array();
	}
	public function isEmpty( $key ) {
		$value = $this->get($key);
		return ( false === $value );
	}
	public function save( $key, $val, $ignoreDbUpdate = false ) {
		$this->_loadOptValues();
		if (!isset($this->_values[$key]) || $this->_values[$key] != $val) {
			$this->_values[$key] = $val;
			if (!$ignoreDbUpdate) {
				$this->_updateOptsInDb();
			}
			return true;
		}
		return false;
	}

	public function saveOptions( $data = array() ) {
		$needSave = true;
		if (is_array($data)) {
			foreach ($data as $key => $val) {
				if ($this->save($key, $val, true)) {
					$needSave = true;
				}
			}
		}
		
		if ($needSave) {
			$this->_updateOptsInDb();
		}
		DispatcherAfsw::doAction('afterSaveOptions', $data);
		
		return true;
	}
	
	private function _updateOptsInDb() {
		update_option(AFSW_CODE . '_options', $this->_values);
	}
	private function _loadOptValues() {
		if (!$this->_valuesLoaded) {
			$this->_values = get_option(AFSW_CODE . '_options');
			if (empty($this->_values)) {
				$this->_values = array();
			}
			$this->_valuesLoaded = true;
		}
	}
}
