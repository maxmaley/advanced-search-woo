<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
abstract class ControllerAfsw {
	protected $_models = array();
	protected $_views = array();
	protected $_task = '';
	protected $_defaultView = '';
	protected $_code = '';
	public function __construct( $code ) {
		$this->setCode($code);
		$this->_defaultView = $this->getCode();
	}
	public function init() {
		/*load model and other preload data goes here*/
	}
	protected function _onBeforeInit() {

	}
	protected function _onAfterInit() {

	}
	public function setCode( $code ) {
		$this->_code = $code;
	}
	public function getCode() {
		return $this->_code;
	}
	public function exec( $task = '' ) {
		if (method_exists($this, $task)) {
			$this->_task = $task;   //For multicontrollers module version - who know, maybe that's will be?))
			return $this->$task();
		}
		return null;
	}
	public function getView( $name = '' ) {
		if (empty($name)) {
			$name = $this->getCode();
		}
		if (!isset($this->_views[$name])) {
			$this->_views[$name] = $this->_createView($name);
		}
		return $this->_views[$name];
	}
	public function getModel( $name = '' ) {
		if (!$name) {
			$name = $this->_code;
		}
		if (!isset($this->_models[$name])) {
			$this->_models[$name] = $this->_createModel($name);
		}
		return $this->_models[$name];
	}
	protected function _createModel( $name = '' ) {
		if (empty($name)) {
			$name = $this->getCode();
		}
		$parentModule = FrameAfsw::_()->getModule( $this->getCode() );
		$className = '';
		if (importAfsw($parentModule->getModDir() . 'models' . DS . $name . '.php')) {
			$className = toeGetClassNameAfsw($name . 'Model');
		}
		
		if ($className) {
			$model = new $className();
			$model->setCode( $this->getCode() );
			return $model;
		}
		return null;
	}
	protected function _createView( $name = '' ) {
		if (empty($name)) {
			$name = $this->getCode();
		}
		$parentModule = FrameAfsw::_()->getModule( $this->getCode() );
		$className = '';
		
		if (importAfsw($parentModule->getModDir() . 'views' . DS . $name . '.php')) {
			$className = toeGetClassNameAfsw($name . 'View');
		}
		
		if ($className) {
			$view = new $className();
			$view->setCode( $this->getCode() );
			return $view;
		}
		return null;
	}
	public function display( $viewName = '' ) {
		$view = $this->getView($viewName);
		if (null === $view) {
			$view = $this->getView();   //Get default view
		}
		if ($view) {
			$view->display();
		}
	}
	public function __call( $name, $arguments ) {
		$model = $this->getModel();
		if (method_exists($model, $name)) {
			return $model->$name($arguments[0]);
		} else {
			return false;
		}
	}
	/**
	 * Retrive permissions for controller methods if exist.
	 * If need - should be redefined in each controller where it required.
	 *
	 * @return array with permissions
	 * Can be used on of sub-array - AFSW_METHODS or AFSW_USERLEVELS
	 */
	public function getPermissions() {
		return array();
	}
	/**
	 * Methods that require nonce to be generated
	 * If need - should be redefined in each controller where it required.
	 *
	 * @return array
	 */
	public function getNoncedMethods() {
		return array();
	}
	public function getModule() {
		return FrameAfsw::_()->getModule( $this->getCode() );
	}
	protected function _prepareTextLikeSearch( $val ) {
		return '';	 // Should be re-defined for each type
	}
	protected function _prepareModelBeforeListSelect( $model ) {
		return $model->setSelectFields('*');
	}
	/**
	 * Common method for list table data
	 */
	public function getListForTbl() {
		$res = new ResponseAfsw();
		$res->ignoreShellData();
		$model = $this->getModel();

		$params = ReqAfsw::get('post');

		$length = UtilsAfsw::getArrayValue($params, 'length', 10, 1);
		$start = UtilsAfsw::getArrayValue($params, 'start', 0, 1);
		$search = UtilsAfsw::getArrayValue(UtilsAfsw::getArrayValue($params, 'search', array(), 2), 'value');

		if (!empty($search)) {
			$model->addWhere(array('additionalCondition' => "title like '%" . $search . "%'"));
		}
		$order = UtilsAfsw::getArrayValue($params, 'order', array(), 2);
		$orderBy = 'id';
		$sortOrder = 'DESC';
		if (isset($order[0])) {
			$orderBy = UtilsAfsw::getArrayValue($order[0], 'column', $orderBy, 1);
			$sortOrder = UtilsAfsw::getArrayValue($order[0], 'dir', $sortOrder);
		}

		// Get total pages count for current request
		$totalCount = $model->getCount(array('clear' => array('selectFields')));
		if ($length > 0) {
			if ($start >= $totalCount) {
				$start = 0;
			}
			$model->setLimit($start . ', ' . $length);
		}

		$model->setOrderBy($orderBy)->setSortOrder($sortOrder);
		$data = $this->_prepareModelBeforeListSelect($model)->getFromTbl();
		
		$data = empty($data) ? array() : $this->_prepareListForTbl($data);
		$res->data = $data;

		$res->recordsFiltered = $totalCount;
		$res->recordsTotal = $totalCount;
		$res->draw = UtilsAfsw::getArrayValue($params, 'draw', 0, 1);

		$res = DispatcherAfsw::applyFilters($this->getCode() . '_getListForTblResults', $res);
		$res->ajaxExec();

	}
	public function removeGroup() {
		$res = new ResponseAfsw();
		if ($this->getModel()->removeGroup(ReqAfsw::getVar('ids', 'post'))) {
			$res->addMessage(esc_html__('Done', 'advanced-fuzzy-search'));
		} else {
			$res->pushError($this->getModel()->getErrors());
		}
		$res->ajaxExec();
	}
	public function clear() {
		$res = new ResponseAfsw();
		if ($this->getModel()->clear()) {
			$res->addMessage(esc_html__('Done', 'advanced-fuzzy-search'));
		} else {
			$res->pushError($this->getModel()->getErrors());
		}
		$res->ajaxExec();
	}
	protected function _prepareListForTbl( $data ) {
		return $data;
	}
	protected function _prepareSearchField( $searchField ) {
		return $searchField;
	}
	protected function _prepareSearchString( $searchString ) {
		return $searchString;
	}
	protected function _prepareSortOrder( $sortOrder ) {
		return $sortOrder;
	}
}
