<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Temp_PhrasesModelAfsw extends ModelAfsw {

	public function __construct() {
		$this->_setTbl('temp_phrases');
		$this->setIndexes(array(
			//'term_id' => 'INDEX `term_id` (`term_id`)',
			//'product_id' => 'INDEX `product_id` (`product_id`)'
			'num' => 'INDEX `num` (`num`)'
		));
	}
}
