<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class OptionsViewAfsw extends ViewAfsw {
	
	public function getSettingsTabContent() {
		$frame = FrameAfsw::_();
		$module = $frame->getModule('options');

		$assets = AssetsAfsw::_();
		$assets->loadCoreJs();
		
		$frame->addScript('afsw-admin-settings', $module->getModPath() . 'assets/js/admin.settings.js');
		
		$assets->loadAdminEndCss();
		DispatcherAfsw::doAction('addOptionsAssetsContent');
	
		$this->assign('is_pro', $frame->isPro());
		$this->assign('options', $module->getModel()->get());
		
		$trStrings = array(
			'btn-run' => esc_html__('Run', 'advanced-fuzzy-search'),
			'btn-cancel' => esc_html__('Cancel', 'advanced-fuzzy-search'),
		);
		
		$this->assign('tr_strings', DispatcherAfsw::applyFilters('addLangStrings', $trStrings, 'options'));
		return parent::getContent('optionsAdmin');
	}

}
