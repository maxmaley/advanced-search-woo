<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TableModulesAfsw extends TableAfsw {
	public function __construct() {
		$this->_table = '@__modules';
		$this->_id = 'id';     /*Let's associate it with posts*/
		$this->_alias = 'sup_m';
		$this->_addField('label', 'text', 'varchar', 0, esc_html__('Label', 'advanced-fuzzy-search'), 128)
				->_addField('type_id', 'selectbox', 'smallint', 0, esc_html__('Type', 'advanced-fuzzy-search'))
				->_addField('active', 'checkbox', 'tinyint', 0, esc_html__('Active', 'advanced-fuzzy-search'))
				->_addField('params', 'textarea', 'text', 0, esc_html__('Params', 'advanced-fuzzy-search'))
				->_addField('code', 'hidden', 'varchar', '', esc_html__('Code', 'advanced-fuzzy-search'), 64)
				->_addField('ex_plug_dir', 'hidden', 'varchar', '', esc_html__('External plugin directory', 'advanced-fuzzy-search'), 255);
	}
}
