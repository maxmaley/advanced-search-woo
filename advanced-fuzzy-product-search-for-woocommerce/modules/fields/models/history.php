<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class HistoryModelAfsw extends ModelAfsw {
	// status - 0 -active, 9 - deleted (for history, but not for statisctics)

	public function __construct() {
		$this->_setTbl('history');
	}
	
	public function saveHistory( $fieldId, $search, $f = 0 ) {
		$data = array(
			'field_id' => $fieldId, 
			'user_id' => get_current_user_id(), 
			'search' => empty($search['s']) ? '' : $search['s']
		);
		$found = $this->setSelectFields('id')->addWhere($data)->getFromTbl(array('return' => 'one'));
		if (empty($found)) {
			$data['codes'] = empty($search['codes']) ? $data['search'] : $search['codes'];
			$data['found'] = $f;
			$data['cnt'] = 1;
			$this->insert($data);
		} else {
			$data = array('last' => 'NOW()', 'status' => 0, 'cnt' => 'cnt+1');
			if ($f) {
				$data['found'] = $f;
			}
			$this->updateById($data, $found);
		}
		return true;
	}
	
	public function getHistory( $search, $params, $exclude, $limit ) {
		/*$query = 'SELECT DISTINCT search FROM @__history WHERE ' .
				" search LIKE '" . $search . "%'";*/
		$query = 'SELECT DISTINCT search FROM @__history WHERE status=0 AND' . 
			( empty($search['codes']) || $search['s'] == $search['codes'] ? " search LIKE '" . $search['s'] . "%'" : " codes LIKE '" . $search['codes'] . "%' AND found=1" );
		foreach ($params as $f => $v) {
			$query .= ' AND ' . $f . "='" . $v . "'"; 
		}
		if (!empty($exclude)) {
			$query .= " AND search NOT IN ('" . implode("','", $exclude) . "')";
		}
		$query .= ' ORDER BY found DESC, cnt DESC LIMIT ' . $limit;
		return DbAfsw::get($query, 'col');
	}
	
	/*public function getSearchStringByAscii( $params, $fieldId ) {
		if (!empty($params['codes'])) {
			$query = 'SELECT search, field_id FROM @__history WHERE ' . 
				" codes LIKE '" . $params['codes'] . "%'" . 
				' AND found=1';
			$results = DbAfsw::get($query);
			$replace = '';
			$s = $params['s'];
			foreach ($results as $row) {
				if ($row['search'] == $s) {
					return $params;
				}
				if ($row['field_id'] == $fieldId) {
					$params['replace_s'] = ;
				}
				
			}
	}*/
	
	public function clearHistory() {
		$result = $this->doClearHistory();
		if (!$result) {
			FrameAfsw::_()->saveDebugLogging();
		}
		return $result;
	}
	
	public function doClearHistory() {
		$model = $this->getModule()->getModel();
		$fieldsParams = $model->getSettingsParams('options');
		if (!empty($fieldsParams)) {
			$forClear = array();
			$forHide = array();
			
			foreach ($fieldsParams as $id => $options) {
				if (UtilsAfsw::getArrayValue($options, 'save_history', false, 1) == 1) {
					$keep = UtilsAfsw::getArrayValue($options, 'keep_history');
					$isHide = ( $model->getFieldData($id, 'is_stats', true) == 1 );
					if ($isHide) {
						if (isset($forHide[$keep])) {
							$forHide[$keep][] = $id; 
						} else {
							$forHide[$keep] = array($id);
						}
					} else {
						if (isset($forClear[$keep])) {
							$forClear[$keep][] = $id; 
						} else {
							$forClear[$keep] = array($id);
						}
					}
				}
			}
			if (!empty($forClear)) {
				$cleared = array();
				foreach ($forClear as $keep => $ids) {
					$query = 'DELETE FROM @__history' .
						' WHERE field_id' . ( count($ids) > 1 ? ' IN (' . implode(',', $ids) . ')' : '=' . $ids[0] ) .
						' AND TIMESTAMPDIFF(' . strtoupper($keep) . ',last,CURRENT_TIMESTAMP) > 0';
					if (!DbAfsw::query($query)) {
						FrameAfsw::_()->pushError(DbAfsw::getError());
						return false;
					}
					$cleared = array_merge($cleared, $ids);
				}
				foreach ($forHide as $keep => $ids) {
					$query = 'UPDATE @__history' .
						' SET status=9 ' .
						' WHERE field_id' . ( count($ids) > 1 ? ' IN (' . implode(',', $ids) . ')' : '=' . $ids[0] ) .
						' AND TIMESTAMPDIFF(' . strtoupper($keep) . ',last,CURRENT_TIMESTAMP) > 0';
					if (!DbAfsw::query($query)) {
						FrameAfsw::_()->pushError(DbAfsw::getError());
						return false;
					}
					$cleared = array_merge($cleared, $ids);
				}
				$query = 'DELETE FROM @__history WHERE field_id NOT IN (' . implode(',', $cleared) . ')';
				if (!DbAfsw::query($query)) {
					FrameAfsw::_()->pushError(DbAfsw::getError());
					return false;
				}
			}
		}
		return true;
	}
}
