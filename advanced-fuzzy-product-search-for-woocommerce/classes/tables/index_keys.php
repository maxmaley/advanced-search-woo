<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TableIndex_KeysAfsw extends TableAfsw {
	public function __construct() {
		$this->_table = '@__index_keys';
		$this->_id = 'id';
		$this->_alias = 'afsw_index_keys';
		$this->_addField('id', 'text', 'int')
			 ->_addField('inx_key', 'text', 'text')
			 ->_addField('inx_name', 'text', 'text')
			 ->_addField('inx_type', 'text', 'int')
			 ->_addField('inx_source', 'text', 'int')
			 ->_addField('active', 'text', 'int')
			 ->_addField('list', 'text', 'text')
			 ->_addField('parent', 'text', 'int')
			 ->_addField('phrases', 'text', 'int')
			 ->_addField('words', 'text', 'int')
			 ->_addField('with_vars', 'text', 'int')
			 ->_addField('status', 'text', 'int')
			 ->_addField('added', 'text', 'text')
			 ->_addField('updated', 'text', 'text')
			 ->_addField('locked', 'text', 'text')
			 ->_addField('calculated', 'text', 'text');
	}
}
