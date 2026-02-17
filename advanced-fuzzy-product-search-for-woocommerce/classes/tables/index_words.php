<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TableIndex_WordsAfsw extends TableAfsw {
	public function __construct() {
		$this->_table = '@__index_words';
		$this->_id = 'id';
		$this->_alias = 'afsw_index_words';
		$this->_addField('id', 'text', 'int')
			 ->_addField('value', 'text', 'text')
			 ->_addField('prefix2', 'text', 'text');
	}
}
