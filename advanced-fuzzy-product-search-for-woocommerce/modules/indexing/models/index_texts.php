<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Index_TextsModelAfsw extends ModelAfsw {

	public function __construct() {
		$this->_setTbl('index_texts');
		$this->setIndexes(array(
			'text_index' => 'FULLTEXT `text_index` (`value`)',
			'inx_key' => 'INDEX `inx_key` (`key_id`)',
			'product_keys' => 'UNIQUE INDEX `product_keys` (`product_id`, `key_id`)'
		));
	}
}
