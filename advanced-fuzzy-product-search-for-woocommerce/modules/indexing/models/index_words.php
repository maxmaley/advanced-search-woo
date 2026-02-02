<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Index_WordsModelAfsw extends ModelAfsw {
	public function __construct() {
		$this->_setTbl('index_words');
	}
}
