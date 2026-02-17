<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TableFieldsAfsw extends TableAfsw {
	public function __construct() {
		$this->_table = '@__fields';
		$this->_id = 'id';
		$this->_alias = 'afsw_fields';
		$this->_addField('id', 'text', 'int')
			 ->_addField('title', 'text', 'varchar')
			 ->_addField('options', 'text', 'text')
			 ->_addField('field', 'text', 'text')
			 ->_addField('autocomplete', 'text', 'text')
			 ->_addField('search', 'text', 'text')
			 ->_addField('updated', 'text', 'free')
			 ->_addField('css', 'text', 'text')
			 ->_addField('add_css', 'text', 'text')
			 ->_addField('add_js', 'text', 'text')
			 ->_addField('is_stats', 'int', 'int')
			 ->_addField('theme_replace', 'int', 'int');
	}
}
