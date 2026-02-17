<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Index_DataModelAfsw extends ModelAfsw {

	public function __construct() {
		$this->_setTbl('index_data');
	}
}
