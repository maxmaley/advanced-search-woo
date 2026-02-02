<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class IndexingControllerAfsw extends ControllerAfsw {
	protected $_code = 'indexing';
	
	public function getNoncedMethods() {
		return array('doProductsIndexing');
	}

	public function doProductsIndexing() {
		$res = new ResponseAfsw();
		if (ReqAfsw::getVar('inCron')) {
			if (!wp_next_scheduled('afsw_calc_products_indexing')) {
				wp_schedule_single_event( time() + 3, 'afsw_calc_products_indexing' );
			}
			$result = true;
		} else {
			$result = $this->getModel()->recalcIndexing();
		}
		if ( false != $result ) {
			$res->addMessage(esc_html__('Done', 'advanced-fuzzy-search'));
		} else {
			$res->pushError(FrameAfsw::_()->getErrors());
		}
		return $res->ajaxExec();
	}

}
