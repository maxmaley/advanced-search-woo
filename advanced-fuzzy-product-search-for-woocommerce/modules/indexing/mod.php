<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class IndexingAfsw extends ModuleAfsw {
	private $calculated = false;
	public static $afswPreviousProductId = -1;
	public static $afswPreviousProductIdAcf = -1;

	public function init() {
		parent::init();
		if (is_admin()) {
			add_action( 'admin_notices', array( $this, 'showAdminInfo' ) );
		}
		add_action('woocommerce_update_product', array($this, 'recalcProductsIndexes'), 99999, 1);
		add_action('acf/save_post', array($this, 'recalcProductsIndexesAcf'), 99999, 1);
		add_action('woocommerce_product_set_stock_status', array($this, 'recalcProductStockStatus'), 100, 1);
		add_action('woocommerce_variation_set_stock_status', array($this, 'recalcProductStockStatus'), 100, 1);
		add_action('afsw_calc_products_indexing', array($this->getModel(), 'recalcIndexing'), 10, 1);
		add_action('afsw_calc_indexing_shedule', array($this, 'recalcIndexingShedule'), 10, 1);
		//add_action('afsw_calc_optimizing_shedule', array($this, 'recalcOptimizingShedule'), 10, 1);

		add_filter('woocommerce_product_csv_importer_steps', array($this, 'recalcAfterImporting'));
		
		DispatcherAfsw::addAction('afterSaveOptions', array($this, 'addShedulers'), 10, 2);
	}
	public function showAdminInfo() {
		return $this->getView()->showAdminInfo();
	}
	public function isGlobalCalcRunning() {
		return FrameAfsw::_()->getModule('options')->getModel()->get('start_indexing') == 2;
	}
	public function isDisabledAutoindexing() {
		$param = FrameAfsw::_()->getModule('options')->getModel()->get('disable_autoindexing');
		return false === $param ? 0 : ( (int) $param );
	}

	public function recalcAfterImporting( $steps ) {
		$step = ReqAfsw::getVar('step');
		if (!is_null($step) && 'done' == $step && !$this->isDisabledAutoindexing()) {
			wp_schedule_single_event( time() + 1, 'afsw_calc_products_indexing' );
		}
		return $steps;
	}

	public function recalcProductsIndexes( $productId ) {
		if ( ! $this->isDisabledAutoindexing() ) {
			if (self::$afswPreviousProductId !== $productId) {
				self::$afswPreviousProductId = $productId;
				$this->getModel()->recalcIndexing( $productId );
			}
		}
	}
	public function recalcProductsIndexesAcf( $productId ) {
		if ( ! $this->isDisabledAutoindexing() ) {
			if (self::$afswPreviousProductIdAcf !== $productId) {
				self::$afswPreviousProductIdAcf = $productId;
				$this->getModel()->recalcIndexing( $productId );
			}
		}
	}

	public function recalcProductStockStatus( $productId ) {
		$this->getModel()->recalcIndexing( $productId, array( 'inx_key' => 'meta', 'inx_name' => '_stock_status' ) );
	}

	public function calcNeededIndexes( $one = false ) {
		if ( ! $this->isGlobalCalcRunning() ) {
			if ( ! $one || ! $this->calculated ) {
				$this->getModel()->recalcIndexing( 0, array( 'status' => array( 0, 2 ) ) );
			}
			$this->calculated = true;
		}
	}

	public function recalcIndexingShedule() {
		$options = FrameAfsw::_()->getModule('options');
		$daySelect = $options->get('schedule_indexing_day');
		if ( '0' !== $daySelect && gmdate( 'N' ) !== $daySelect ) {
			return false;
		}

		$hourSelect = $options->get('schedule_indexing_hour');
		$timestampShedule = mktime( $hourSelect, 0, 0 );
		if ( time() < $timestampShedule ) {
			return false;
		}

		$timestampLastIndexing = $options->get('start_indexing');
		if ( $timestampLastIndexing > $timestampShedule ) {
			return false;
		}

		$this->getModel()->recalcIndexing();

	}
	public function recalcOptimizingShedule() {
		$options = FrameAfsw::_()->getModule('options');
		$daySelect = $options->get('schedule_optimizing_day');
		if ( '0' !== $daySelect && gmdate( 'N' ) !== $daySelect ) {
			return false;
		}
		$hourSelect = $options->get('schedule_optimizing_hour');
		$timestampShedule = mktime( $hourSelect, 0, 0 );
		if ( time() < $timestampShedule ) {
			return false;
		}
		$this->getModel()->optimizeTables();
	}
	
	public function addShedulers( $options ) {
		if (isset($options['use_schedule_indexing'])) {
			if (1 == $options['use_schedule_indexing']) {
				if (!wp_next_scheduled('afsw_calc_indexing_shedule')) {
					wp_schedule_event( time(), 'hourly', 'afsw_calc_indexing_shedule' );
				}
			} else {
				wp_unschedule_hook('afsw_calc_indexing_shedule');
			}
		}
		if (isset($options['use_schedule_history'])) {
			if (1 == $options['use_schedule_history']) {
				if (!wp_next_scheduled('afsw_clear_history_shedule')) {
					wp_schedule_event( time(), 'hourly', 'afsw_clear_history_shedule' );
				}
			} else {
				wp_unschedule_hook('afsw_clear_history_shedule');
			}
		}
		
	}

}
