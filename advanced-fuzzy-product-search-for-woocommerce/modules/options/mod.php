<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class OptionsAfsw extends ModuleAfsw {
	private $_options = array();
	private $_optionsToCategoires = array();	// For faster search

	public function init() {
		DispatcherAfsw::addFilter('mainAdminTabs', array($this, 'addAdminTab'));
	}
	public function initAllOptValues() {
		// Just to make sure - that we loaded all default options values
		$this->getAll();
	}
	/**
	 * This method provides fast access to options model method get
	 *
	 * @see optionsModel::get($d)
	 */
	public function get( $key = '' ) {
		return $this->getModel()->get($key);
	}
	/**
	 * This method provides fast access to options model method get
	 *
	 * @see optionsModel::get($d)
	 */
	public function isEmpty( $key = '' ) {
		return $this->getModel()->isEmpty($key);
	}

	public function addAdminTab( $tabs ) {
		$tabs['settings'] = array(
			'label' => esc_html__('Settings', 'advanced-fuzzy-search'), 'callback' => array($this, 'getSettingsTabContent'), 'fa_icon' => 'fa-cog', 'sort_order' => 30,
		);
		if (!FrameAfsw::_()->isPro()) {
			$tabs['gopro'] = array(
				'label' => esc_html__('Go PRO', 'woo-product-filter'), 'callback' => 'https://woobewoo.com/plugins/advanced-fuzzy-search/#license', 'blank' => true, 'fa_icon' => 'fa-star', 'sort_order' => 998,
			);
		}
		return $tabs;
	}
	public function getSettingsTabContent() {
		return $this->getView()->getSettingsTabContent();
	}
	
	public function getRecalcOptions() {
		return DispatcherAfsw::applyFilters('getRecalcOptions', array());
	}
	public function getScheduleHours() {
		$hours = array();
		for ($i = 0; $i < 24; $i++) {
			$hours[] = str_pad($i, 2, '0', STR_PAD_LEFT);
		}
		return $hours;
	}
	public function getScheduleDays() {
		$days = array(
			__( 'Everyday', 'woo-product-filter' ),
			__( 'Monday', 'woo-product-filter' ),
			__( 'Tuesday', 'woo-product-filter' ),
			__( 'Wednesday', 'woo-product-filter' ),
			__( 'Thursday', 'woo-product-filter' ),
			__( 'Friday', 'woo-product-filter' ),
			__( 'Saturday', 'woo-product-filter' ),
			__( 'Sunday', 'woo-product-filter' ),
		);
		
		return $days;
	}
}
