<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TableHistoryAfsw extends TableAfsw {
	public function __construct() {
		$this->_table = '@__history';
		$this->_id = 'id';
		$this->_alias = 'afsw_history';
		$this->_addField('id', 'text', 'int')
			 ->_addField('search', 'text', 'varchar')
			 ->_addField('codes', 'text', 'varchar')
			 ->_addField('field_id', 'text', 'int')
			 ->_addField('user_id', 'text', 'int')
			 ->_addField('cnt', 'text', 'free')
			 ->_addField('found', 'text', 'int')
			 ->_addField('status', 'text', 'int')
			 ->_addField('added', 'text', 'text')
			 ->_addField('last', 'text', 'free');
	}
}
