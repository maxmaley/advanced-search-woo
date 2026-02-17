<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class OptionsControllerAfsw extends ControllerAfsw {
	public function getNoncedMethods() {
		return array('saveOptions');
	}
	public function saveOptions() {
		$res = new ResponseAfsw();
		if ($this->getModel()->saveOptions(ReqAfsw::get('post'))) {
			$res->addMessage(esc_html__('Done', 'advanced-fuzzy-search'));
		} else {
			$res->pushError(FrameAfsw::_()->getErrors());
		}
		return $res->ajaxExec();
	}
	
}
