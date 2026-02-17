<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TableIndex_PhrasesAfsw extends TableAfsw {
	public function __construct() {
		$this->_table = '@__index_phrases';
		$this->_id = 'id';
		$this->_alias = 'afsw_index_phrases';
		$this->_addField('id', 'text', 'int')
			 ->_addField('value', 'text', 'text')
			 ->_addField('hash', 'text', 'text');
	}
}
