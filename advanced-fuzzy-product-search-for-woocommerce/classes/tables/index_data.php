<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TableIndex_DataAfsw extends TableAfsw {
	public function __construct() {
		$this->_table = '@__index_data';
		$this->_id = 'id';
		$this->_alias = 'afsw_index_data';
		$this->_addField('id', 'text', 'int')
			 ->_addField('product_id', 'text', 'int')
			 ->_addField('pr_type', 'text', 'int')
			 ->_addField('key_id', 'text', 'int')
			 ->_addField('inx_mode', 'text', 'int')
			 ->_addField('inx_id', 'text', 'int')
			 ->_addField('updated', 'text', 'text');
	}
}
