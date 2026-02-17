<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class FieldsControllerAfsw extends ControllerAfsw {

	protected $_code = 'fields';

	public function getNoncedMethods() {
		return array('createField', 'saveFieldTitle', 'saveField', 'doClearHistory', 'removeField', 'cloneField');
	}

	public function createField() {
		$res = new ResponseAfsw();
		$res->ignoreShellData();
		$id = $this->getModel()->createField(ReqAfsw::get('post'));
		if ( false != $id ) {
			$res->addMessage(esc_html__('Done', 'advanced-fuzzy-search'));
			$res->addData('edit_link', FrameAfsw::_()->getModule('adminmenu')->getEditLink($id, $this->_code));
		} else {
			$res->pushError(FrameAfsw::_()->getErrors());
		}
		return $res->ajaxExec();
	}
	public function _prepareListForTbl( $data ) {
		return $this->getModel()->prepareFieldsList($data);	
	}
	public function saveFieldTitle() {
		$res = new ResponseAfsw();
		if ($this->getModel()->saveTitle(ReqAfsw::getVar('id'), ReqAfsw::getVar('title'))) {
			$res->addMessage(esc_html__('Title saved successfully', 'advanced-fuzzy-search'));
		} else {
			$res->pushError(FrameAfsw::_()->getErrors());
		}
		return $res->ajaxExec();
	}
	
	public function saveField() {
		$res = new ResponseAfsw();
		$id = $this->getModel()->saveField(ReqAfsw::get('post'));
		if ( false != $id ) {
			$res->addMessage(esc_html__('Field options saved', 'advanced-fuzzy-search'));
		} else {
			$res->pushError(FrameAfsw::_()->getErrors());
		}
		return $res->ajaxExec();
		
	}
	public function drawFieldPreview() {
		$res = new ResponseAfsw();
		$data = ReqAfsw::get('post');
		
		if (isset($data) && $data && isset($data['settings'])) {
			$data = array_merge($data, $this->getModel()->controlFieldData(UtilsAfsw::jsonDecode(stripcslashes($data['settings']))));
			unset($data['settings']);
			$html = $this->getModule('fields')->renderField($data, true);
			$res->setHtml($html);
		} else {
			$res->pushError(FrameAfsw::_()->getErrors());
		}

		$res->ajaxExec();
	}
	
	public function drawAutocompletePreview() {
		$res = new ResponseAfsw();
		$data = ReqAfsw::get('post');
		
		if (isset($data) && $data && isset($data['settings'])) {
			$data = array_merge($data, $this->getModel()->controlFieldData(UtilsAfsw::jsonDecode(stripcslashes($data['settings']))));
			unset($data['settings']);
			
			$html = $this->getModule('fields')->getView()->renderPreviewAutocomplete($data);
			$res->setHtml($html);
		} else {
			$res->pushError(FrameAfsw::_()->getErrors());
		}

		$res->ajaxExec();
	}
	
	public function getAutocomplete() {
		$res = new ResponseAfsw();
		$search = ReqAfsw::getVar('search');
		$id = ReqAfsw::getVar('id');
		
		$search = is_null($search) || empty($search) ? '' : $this->getModule('fields')->controlSearchString($search);
	
		if (!empty($search) && !empty($id)) {
			$searchParams = DispatcherAfsw::applyFilters('addAutoSearchParams', array('s' => $search));
			$this->getModule('fields')->isAfswSearchId = $id;
			$html = $this->getModule('fields')->getView()->renderAutocomplete($searchParams, $id);
			$res->setHtml($html);
		} else {
			$res->pushError(FrameAfsw::_()->getErrors());
		}

		$res->ajaxExec();
	}
	public function doClearHistory() {
		$res = new ResponseAfsw();
		if (ReqAfsw::getVar('inCron')) {
			if ( !wp_next_scheduled( 'afsw_clear_history' ) ) {
				wp_schedule_single_event( time() + 3, 'afsw_clear_history' );
			}
			$result = true;
		} else {
			$result = $this->getModel('history')->clearHistory();
		}
		if ( false != $result ) {
			$res->addMessage(esc_html__('Done', 'advanced-fuzzy-search'));
		} else {
			$res->pushError(FrameAfsw::_()->getErrors());
		}
		return $res->ajaxExec();
	}
	
	public function removeField() {
		$res = new ResponseAfsw();
		$id = ReqAfsw::getVar('fieldId');

		if (is_numeric($id)) {
			$result = $this->getModel()->delete($id);
			if ($result) {
				$res->addMessage(esc_html__('Field deleted', 'advanced-fuzzy-search'));
				$res->addData('link', FrameAfsw::_()->getModule('adminmenu')->getTabUrl());
			} else {
				$res->pushError(FrameAfsw::_()->getErrors());
			}
		} else {
			$res->pushError(esc_html__('Field Id error detected', 'advanced-fuzzy-search'));
		}
		return $res->ajaxExec();
	}
	
	public function cloneField() {
		$res = new ResponseAfsw();
		$id = ReqAfsw::getVar('fieldId');
		
		if (is_numeric($id)) {
			$newId = $this->getModel()->cloneField($id);
			if ($newId) {
				$res->addMessage(esc_html__('Field cloned', 'advanced-fuzzy-search'));
				$res->addData('link', FrameAfsw::_()->getModule('adminmenu')->getEditLink($newId, $this->_code));
			} else {
				$res->pushError(FrameAfsw::_()->getErrors());
			}
		} else {
			$res->pushError(esc_html__('Field Id error detected', 'advanced-fuzzy-search'));
		}
		return $res->ajaxExec();
	}
	
}
