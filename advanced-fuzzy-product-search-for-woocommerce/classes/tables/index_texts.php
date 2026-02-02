<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TableIndex_TextsAfsw extends TableAfsw {
	public function __construct() {
		$this->_table = '@__index_texts';
		$this->_id = 'id';
		$this->_alias = 'afsw_index_texts';
		$this->_addField('id', 'text', 'int')
			 ->_addField('product_id', 'text', 'int')
			 ->_addField('pr_type', 'text', 'int')
			 ->_addField('key_id', 'text', 'int')
			 ->_addField('value', 'text', 'text')
			 ->_addField('updated', 'text', 'text');
	}
}
