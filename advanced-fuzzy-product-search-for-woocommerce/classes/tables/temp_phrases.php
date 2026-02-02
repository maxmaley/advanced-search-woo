<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TableTemp_PhrasesAfsw extends TableAfsw {
	public function __construct() {
		$this->_table = '@__temp_phrases';
		$this->_id = 'id';
		$this->_alias = 'afsw_temp_phrases';
		$this->_addField('id', 'text', 'int')
			 ->_addField('product_id', 'text', 'int')
			 ->_addField('pr_type', 'text', 'int')
			 ->_addField('key_id', 'text', 'int')
			 ->_addField('phrase', 'text', 'text')
			 ->_addField('hash', 'text', 'text')
			 ->_addField('spaces', 'text', 'int')
			 ->_addField('term_id', 'text', 'int')
			 ->_addField('num', 'text', 'int');
	}
}
