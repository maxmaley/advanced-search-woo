<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class IndexingModelAfsw extends ModelAfsw {
	// inx_source: 0-unknown, 1-field of wp_posts, 2-taxonomy, 3-wp_postmeta 
	// inx_type: 0-unknown, 1-string, 2-text, 7-array json, 8-serialised array, 9-list
	// status: 1-calculated, 0-need recalc, 2-lock, 9-deleted
	// start_indexing: >0 - processed
	// phrases: 0 - only by words, 1 - by phrases
	// words: 0 - only by phrases, 1 - by words
	// active: 1 - active, 0,2 - old

	public $lockLimit = 0; //minutes
	private $defaultKey = array(
		'inx_key' => '', 
		'inx_name' => '', 
		'inx_source' => 0,
		'inx_type' => 0, 
		'active' => 1, 
		'list' => array(), 
		'parent' => 0,
		'words' => 0,
		'phrases' => 0,
		'with_vars' => 0,
		'status' => 0
	);
	
	private $maxPhraseLength = 250;
	private $dbVersion = null;
	private $wordsModel = array();
	private $phrasesArray = array();
	private $existMB = false;
	public $startLockLimit = 0;
	private $limitQuery = 1000;
	public $removeChars = '';
	public $splitChars = "!\"#$%&'()*+,-./:;?\\";//  "!#$%&;+,-\"\'()*\.\?\/\\\\";//"[!#$%&;+,-\"\'()*\.\?\/\\\\]";
	private $specialChars = array(array('\\', '"', "'", '-', '.', ), array('\\\\\\\\', '\"', "\'", '\-', '\.'));
	private $replaceSQL = null;
	private $selectType = array();
	private $from = array();
	private $where = array();
	private $newPrefixes = array();
	private $recalcAllProducts = false;
	
	public function __construct() {
		$this->_setTbl('index_keys');
		$this->existMB = function_exists('mb_substr');
	}
	
	public function getKeysWithCalcControl( $params = array()) {
		if (FrameAfsw::_()->getModule('options')->getModel()->get('start_indexing') > 0) {
			return array();
		}
		$params['status'] = 1;
		$params['active'] = 1;
		return $this->getAllKeys($params);
	}
	
	public function getAllKeys( $params = array() ) {
		if (!empty($params)) {
			$this->addWhere($params);
		}
		$data = $this->addWhere("inx_name!=''")->getFromTbl();
		$keys = array();
		foreach ($data as $fields) {
			$keys[$fields['inx_key']][$fields['inx_name']] = $fields;
		}
		return $keys;
	}
	
	public function resetLockedKeys() {
		$query = 'UPDATE @__index_keys SET status=0, updated=CURRENT_TIMESTAMP WHERE status=2 AND TIMESTAMPDIFF(MINUTE, locked, CURRENT_TIMESTAMP)>' . $this->lockLimit;
		if (!DbAfsw::query($query)) { 
			return false;
		}
		return true;
	}
	public function blockAllKeys() {
		$query = 'UPDATE @__index_keys SET active=0, updated=CURRENT_TIMESTAMP';
		if (!DbAfsw::query($query)) { 
			return false;
		}
		return true;
	}
	public function isFieldUpdated() {
		return FrameAfsw::_()->getModule('options')->getModel()->get('field_updated');
	}
	
	public function setIndexKeys() {
		if (!$this->isFieldUpdated()) {
			$keys = $this->addWhere("parent=0 AND inx_name='' AND active=1")->getFromTbl();
			foreach ($keys as $key) {
				$list = empty($key['list']) ? array() : UtilsAfsw::jsonDecode($key['list']);
				foreach ($list as $k => $params) {
					if (empty($k)) {
						if (!$this->setChildIndexKeys($key, $list[''], true)) {
							return false;
						}
					} else {
						$data = $this->addWhere(array('inx_key' => $key['inx_key'], 'inx_name' => $k))->getFromTbl(array('return' => 'row'));
						if (!empty($data)) {
							$types = DispatcherAfsw::applyFilters('getIndexKeyType', $data['inx_type'], $data);
							if ($types != $data['inx_type']) {
								if (is_array($types)) {
									$data = array_merge($data, $types);
								} else {
									$data['inx_type'] = $types;
								}
								if (!$this->updateKeyData($data['id'], $data)) {
									return false;
								}
							}
						}
					}
				}
			}
			return true;
		}
		$prefixes = array();
		$newKeys = array();
		$fieldModule = FrameAfsw::_()->getModule('fields');
		
		$fieldsParams = $fieldModule->getModel()->getSettingsParams('search');
		if (!empty($fieldsParams)) {
			$isPro = FrameAfsw::_()->isPro();
			$scopes = $fieldModule->getSearchScopes();
			$defaultKey = $this->defaultKey;
			foreach ($fieldsParams as $search) {
				$fuzzyParams = $fieldModule->getFuzzyParams($search);
				$order = explode(',', UtilsAfsw::getArrayValue($search, 'order'));
				foreach ($order as $scope) {
					if (!isset($scopes[$scope]) 
						|| ( $scopes[$scope]['pro'] && !$isPro )
						|| UtilsAfsw::getArrayValue($search, 'by_' . $scope, false, 1) != 1) {
						continue;
					}
					$sc = $scopes[$scope];
					$options = UtilsAfsw::getArrayValue($search, $scope, array(), 2);
					$isMulty = !empty($sc['multy']);
					$needList = !empty($sc['list']);
					
					$mode = UtilsAfsw::getArrayValue($options, 'mode', 'fw');
					$isWord = ( 'fw' == $mode || 'pw' == $mode );
					if ($isWord && false !== $fuzzyParams && !in_array($fuzzyParams['prefix'], $prefixes)) {
						$prefixes[] = $fuzzyParams['prefix'];
					}
					$withVars = empty(UtilsAfsw::getArrayValue($options, 'for_variable')) ? 0 : 1;

					$list = UtilsAfsw::getArrayValue($options, 'list');
					if (empty($list)) {
						$list = array();
					} else if (!is_array($list)) {
						$list = explode(',', $list);
					}
					if (empty($list) && $needList) {
						continue;
					}
					$params = array('w' => $isWord ? 1 : 0, 'p' => $isWord ? 0 : 1, 'v' => $withVars);
					$listParams = array();
					
					if ($isMulty) {
						if (empty($list)) {
							$listParams[''] = $params;
						} else {
							foreach ($list as $l) {
								$listParams[trim($l)] = $params;
							}
						}
					}
					if (UtilsAfsw::getArrayValue($options, 'by_local', false, 1)) {
						$listParams['afsw_local_attributes'] = $params;
					}
					if (UtilsAfsw::getArrayValue($options, 'by_parent', false, 1)) {
						$listParams['afsw_parent_taxonomies'] = $params;
					}
					
					if (isset($newKeys[$scope])) {
						if (!empty($listParams)) {
							$curList = $newKeys[$scope]['list'];
							foreach ($listParams as $k => $params) {
								if (isset($curList[$k])) {
									foreach ($params as $i => $v) {
										if (1 == $v && 1 != $curList[$k][$i]) {
											$newKeys[$scope]['list'][$k][$i] = 1;
										}
									}
								} else {
									$newKeys[$scope]['list'][$k] = $params;
								}
							}
						}
					} else {
						$key = $defaultKey;
						$key['inx_key'] = $scope;
						foreach ($key as $k => $v) {
							if (isset($sc[$k])) {
								$key[$k] = $sc[$k];
							}
						}
						if (!empty($listParams)) {
							$key['list'] = $listParams;
						}
						$newKeys[$scope] = $key;
					}
					if ($isWord) {
						$newKeys[$scope]['words'] = 1;
					} else {
						$newKeys[$scope]['phrases'] = 1; 
					}
					if ($withVars) {
						$newKeys[$scope]['with_vars'] = 1;
					}
				}
			}
		}
		$query = 'UPDATE @__index_keys SET active=0, updated=CURRENT_TIMESTAMP';
		if (!DbAfsw::query($query)) {
			FrameAfsw::_()->pushError(DbAfsw::getError());
			FrameAfsw::_()->pushError('Error query: ' . $query); 
			return false;
		}
		
		$keys = $this->getFromTbl();
		$currentKeys = array();
		foreach ($keys as $k => $key) {
			$currentKeys[$key['inx_key']][$key['inx_name']] = $key;
		}

		foreach ($newKeys as $scope => $key) {
			$list = $key['list'];
			$key['list'] = empty($list) ? '' : UtilsAfsw::jsonEncode($list, true);
			if (isset($currentKeys[$scope][$key['inx_name']])) {
				$id = $currentKeys[$scope][$key['inx_name']]['id'];
				if (!$this->updateKeyData($id, $key)) {
					return false;
				}
			} else {
				$id = $this->insertKeyData($key);
				if (!$id) {
					return false;
				}
			}
			if (!empty($list)) {
				$all = false;
				foreach ($list as $k => $params) {
					if (empty($k)) {
						$all = true;
					} else {
						$data = array_merge($key, array('inx_name' => $k, 'parent' => $id, 'list' => '', 'words' => $params['w'], 'phrases' => $params['p'], 'with_vars' => $params['v'], 'active' => 1));
						$types = DispatcherAfsw::applyFilters('getIndexKeyType', $data['inx_type'], $data);
						if (is_array($types)) {
							$data = array_merge($data, $types);
						} else {
							$data['inx_type'] = $types;
						}
						if (isset($currentKeys[$scope][$k])) {
							if (!$this->updateKeyData($currentKeys[$scope][$k]['id'], $data)) {
								return false;
							}
						} else {
							if (!$this->insertKeyData($data)) {
								return false;
							}
						}
					}
				}
				if ($all) {
					$params = $list[''];
					$key['id'] = $id;
					if (!$this->setChildIndexKeys($key, $params)) {
						return false;
					}
				}
			}
		}
		
		$columns = $this->getNeededPrefixes();
		$exists = array();
		if (!empty($columns)) {
			foreach ($columns as $field => $num) {
				if (( 'prefix2' != $field ) && !empty($num) && !in_array($num, $prefixes)) {
					if (!DbAfsw::query('ALTER TABLE @__index_words DROP COLUMN ' . $field)) {
						FrameAfsw::_()->pushError(DbAfsw::getError());
						FrameAfsw::_()->pushError('Error query: DROP column ' . $field); 
						return false;
					}
					$isIndex = DbAfsw::get("SHOW INDEX FROM @__index_words WHERE Key_name='" . $field . "'");
					if (false === $isIndex) {
						FrameAfsw::_()->pushError(DbAfsw::getError());
						FrameAfsw::_()->pushError('Error query: SHOW INDEX for column ' . $field); 
						return false;
					}
					if (!empty($isIndex)) {
						if (!DbAfsw::query('ALTER TABLE @__index_words DROP INDEX `' . $field . '`')) {
							FrameAfsw::_()->pushError(DbAfsw::getError());
							FrameAfsw::_()->pushError('Error query: DROP INDEX ' . $field); 
							return false;
						}
					}
				} else {
					$exists[] = $field;
				}
			}
		}
		
		foreach ($prefixes as $prefix) {
			if (!empty($prefix)) {
				$field = 'prefix' . $prefix;
				if (!in_array($field, $exists)) {
					if (!DbAfsw::query('ALTER TABLE @__index_words ADD COLUMN ' . $field . ' char(' . $prefix . ') CHARACTER SET utf8 COLLATE utf8_bin')) {
						FrameAfsw::_()->pushError(DbAfsw::getError());
						FrameAfsw::_()->pushError('Error query: ADD column ' . $field); 
						return false;
					}
					$isIndex = DbAfsw::get("SHOW INDEX FROM @__index_words WHERE Key_name='" . $field . "'");
					if (false === $isIndex) {
						FrameAfsw::_()->pushError(DbAfsw::getError());
						FrameAfsw::_()->pushError('Error query: SHOW INDEX for column ' . $field); 
						return false;
					}
					if (empty($isIndex)) {
						if (!DbAfsw::query('ALTER TABLE @__index_words ADD INDEX `' . $field . '` (`' . $field . '`)')) {
							FrameAfsw::_()->pushError(DbAfsw::getError());
							FrameAfsw::_()->pushError('Error query: ADD INDEX ' . $field); 
							return false;
						}
					}
					$this->newPrefixes[$prefix] = $field;
				}
				$exists[] = $field;
			}
		}
		FrameAfsw::_()->getModule('options')->getModel()->save('field_updated', 0);
		return true;
	}
	
	public function getNeededPrefixes() {
		$columns = DbAfsw::getTableColumns("@__index_words LIKE 'prefix%'");
		if (!$columns) {
			FrameAfsw::_()->pushError(DbAfsw::getError());
			FrameAfsw::_()->pushError('Error query: get prefix% columns'); 
			return false;
		}
		$prefixes = array();
		if (!empty($columns)) {
			foreach ($columns as $column) {
				$field = $column['Field'];
				$num = str_replace('prefix', '', $field);
				if (!empty($num) && is_numeric($num)) {
					$prefixes[$field] = $num;
				}
			}
		}
		return $prefixes;
	}
	
	public function setChildIndexKeys( $parent, $params, $reset = false ) {
		$inxKey = $parent['inx_key'];
		$query = "UPDATE @__index_keys SET active=IF(active=1,2,0), updated=CURRENT_TIMESTAMP WHERE inx_key='" . $inxKey . "' AND parent!=0 AND inx_name not LIKE 'afsw_%'";
		if (!DbAfsw::query($query)) {
			FrameAfsw::_()->pushError(DbAfsw::getError());
			FrameAfsw::_()->pushError('Error query: ' . $query); 
			return false;
		}
		$parentId = $parent['id'];
						
		$keys = $this->addWhere("inx_key='" . $inxKey . "' AND inx_name!=''")->getFromTbl();
		$currentKeys = array();
		foreach ($keys as $k => $key) {
			$currentKeys[$key['inx_name']] = $key;
		}
		$newKeys = array();
		switch ($inxKey) {
			case 'attribute':
				$productAttr = function_exists('wc_get_attribute_taxonomies') ? wc_get_attribute_taxonomies() : array();
				foreach ($productAttr as $attr) {
					$newKeys['pa_' . $attr->attribute_name] = 1;
				}
				break;
			default:
				$newKeys = DispatcherAfsw::applyFilters('getChildIndexKeys', array(), $parent);
				break;
							
		}
		foreach ($newKeys as $name => $types) {
			if (isset($currentKeys[$name])) {
				$query = 'UPDATE @__index_keys SET parent=' . $parentId . ',';
				if (2 == $currentKeys[$name]['active']) {
					$query .= 'words=IF(words=1,1,' . $params['w'] . '),' .
						'phrases=IF(phrases=1,1,' . $params['p'] . '),' .
						'with_vars=IF(with_vars=1,1,' . $params['v'] . '),';
				} else {
					$query .= 'words=' . $params['w'] . ',' .
						'phrases=' . $params['p'] . ',' .
						'with_vars=' . $params['v'] . ',';
				}
				if (is_array($types)) {
					foreach ($types as $k => $v) {
						if (isset($this->defaultKey[$k])) {
							$query .= $k . "='" . $v . "',";
						}
					}
				} else {
					$query .= "inx_type='" . $types . "',";
				}
						
				$query .= 'active=1, updated=CURRENT_TIMESTAMP WHERE id=' . $currentKeys[$name]['id'];
				if (!DbAfsw::query($query)) {
					FrameAfsw::_()->pushError(DbAfsw::getError());
					FrameAfsw::_()->pushError('Error query: ' . $query); 
					return false;
				}
			} else {
				$key = array_merge($parent, array('inx_name' => $name, 'inx_type' => $inxType, 'parent' => $parentId, 'list' => '', 'words' => $params['w'], 'phrases' => $params['p'], 'with_vars' => $params['v'], 'active' => 1));
				if (is_array($types)) {
					$key = array_merge($key, $types);
				} else {
					$key['inx_type'] = $types;
				}
				if (!$this->insertKeyData($key)) {
					return false;
				}
			}
		}
		return true;
	}
	
	public function updateKeyData( $id, $data ) {
		$now = DbAfsw::get('SELECT CURRENT_TIMESTAMP', 'one');
		if (!$now) {
			FrameAfsw::_()->pushError(DbAfsw::getError());
			return false;
		}
		$data['updated'] = $now;
		
		if (isset($data['status'])) {
			if (1 == $data['status']) {	
				$data['calculated'] = $now;
			} else if (2 == $data['status']) {	
				$data['locked'] = $now;
			}
		}
		unset($data['id']);
		if (!$this->updateById($data, $id)) {
			return false;
		}
		return true;
	}
	
	public function getKeysForRecalc( $params ) {
		// $params['inx_key'] - array of ids, not set inx_name

		$where = "active=1 AND inx_name!=''";
		if (!empty($params)) {
			foreach ($params as $key => $value) {
				$where .= ' AND ' . $key . ( is_array($value) ? " IN ('" . implode("','", $value) . "')" : "='" . $value . "'" );
			}
		}
		$keys = $this->setSelectFields('*, TIMESTAMPDIFF(MINUTE, locked, CURRENT_TIMESTAMP) as lock_duration')->addWhere($where)->setOrderBy('inx_key, parent')->getFromTbl();

		return $keys;
	}
	
	public function deleteOldIndexes() {
		$keys = $this->addWhere('active!=1 AND status!=9')->getFromTbl();
		$list = '';
		foreach ($keys as $key) {
			$list .= $key['id'] . ',';
		}
		if (!empty($list)) {
			$query = 'DELETE FROM @__index_data WHERE key_id IN (' . substr($list, 0, -1) . ')';
			if (!DbAfsw::query($query)) {
				FrameAfsw::_()->pushError(DbAfsw::getError());
				FrameAfsw::_()->pushError('Error query: ' . $query); 
				return false;
			}
			$query = 'UPDATE @__index_keys SET status=9, active=0 WHERE active!=1';
			if (!DbAfsw::query($query)) {
				FrameAfsw::_()->pushError(DbAfsw::getError());
				FrameAfsw::_()->pushError('Error query: ' . $query); 
				return false;
			}
		}
	}
	
	public function insertKeyData( $data ) {
		unset($data['id'], $data['added'], $data['calculated']);
		$data['status'] = 0;
		return $this->insert($data);
	}
	
	public function checkDBVersion( $ver ) {
		if (is_null($this->dbVersion)) {
			$query = 'SELECT VERSION()';
			$custom = DbAfsw::get($query, 'one');
			if (!$custom || !is_string($custom) || empty($custom)) {
				FrameAfsw::_()->pushError(DbAfsw::getError());
				FrameAfsw::_()->pushError('Error query: ' . $query); 
				$custom = '0';
			}
			$this->dbVersion = $custom;
		}
		return version_compare($this->dbVersion, $ver) >= 0;
	}
	
	public function replaceSpecialChars() {
		if (is_null($this->replaceSQL)) {
			if (strlen($this->splitChars) > 0) {
				if ($this->checkDBVersion('8.0')) {
					$q = str_replace($this->specialChars[0], $this->specialChars[1], $this->splitChars);
					
					$query = "UPDATE @__temp_phrases SET phrase=TRIM(REPLACE(REGEXP_REPLACE(phrase,'[" . $q . "]',' '),'  ',' '))";
				} else {
					$strArr = str_split($this->splitChars);
					$q = 'UPDATE @__temp_phrases SET phrase=' . str_repeat('REPLACE(', count($strArr) + 1) . 'phrase';
					foreach ($strArr as $c) {
						$q .= ',' . ( "'" == $c ? '"\'"' : ( '\\' == $c ? "'\\\'" : "'" . $c . "'" ) ) . ",' ')";
					}
					$query = $q . ",'  ',' ')";
				}
				$this->replaceSQL = $query;
			} else {
				$this->replaceSQL = false;
			}
		}
		if (false !== $this->replaceSQL) {
			if (!DbAfsw::query($this->replaceSQL)) {
				FrameAfsw::_()->pushError(DbAfsw::getError());
				FrameAfsw::_()->pushError('Error query: ' . $this->replaceSQL); 
				return false;
			}
		}
		return true;
	}
		
	public function recalcIndexing( $productId = 0, $params = array() ) {
		$result = $this->doRecalcIndexing($productId, $params);
		if (!$result) {
			FrameAfsw::_()->saveDebugLogging();
		}
		return $result;
	}
	
	public function doRecalcIndexing( $productId, $params ) {
		if (!empty($productId) && !is_numeric($productId)) {
			return false;
		}
		$isAllProducts = empty($productId);
		$isAllKeys  = empty($params);
		$fullRecalc = $isAllProducts && $isAllKeys;
		$optModel = FrameAfsw::_()->getModule('options')->getModel();
		
		if ($fullRecalc) {
			$startIndexing = $optModel->get('start_indexing');
			if (!$startIndexing) {
				$startIndexing = 0;
			}
		
			if ($startIndexing && time() - $startIndexing < $this->startLockLimit * 60) {
				FrameAfsw::_()->pushError('Wait. The calculation is already running ...');
				return false;
			}
			$optModel->save('start_indexing', time());

			if (!$this->setIndexKeys() || !$this->resetLockedKeys()) {
				return false;
			}
		}
		$this->recalcAllProducts = $isAllProducts;
		
		$dataModel = FrameAfsw::_()->getModule('indexing')->getModel('index_data');
		$textsModel = FrameAfsw::_()->getModule('indexing')->getModel('index_texts');
		
		if ($fullRecalc) {
			// clear deleted keys
			$this->deleteOldIndexes();
		}
		
		$keys = $this->getKeysForRecalc($params);
		if (count($keys) == 0) {
			return true;
		}
		
		$productListP = '';
		$productListV = '';
		$whereProduct = '';

		if (!$isAllProducts) {
			$product = wc_get_product($productId);
			if (!$product) {
				return false;
			}
			$productListP = '=' . $productId;
			$ids = array();
			if ($product->get_type() == 'variable') {
				$ids = $product->get_children();
				if (!empty($ids)) {
					$productListV = ( count($ids) > 1 ? ' IN (' . implode(',', $ids) . ')' : '=' . $ids[0] );
				}
			}
			$ids[] = $productId;
			$whereProduct = 'product_id' . ( count($ids) > 1 ? ' IN (' . implode(',', $ids) . ')' : '=' . $ids[0] );
		}

		$whereKeys = $whereProduct;
		if (!$isAllKeys) {
			$list = '';
			foreach ($keys as $key) {
				if (!empty($key['inx_name'])) {
					$list .= $key['id'] . ',';
				}
			}
			if (!empty($list)) {
				$whereKeys .= ( empty($whereKeys) ? '' : ' AND ' ) . ' key_id IN (' . substr($list, 0, -1) . ')';
			}
		}
		
		if ($isAllProducts) {
			$textsModel->dropIndexes();
		}
		if (!$dataModel->delete($whereKeys)) {
			return false;
		}
		if (!$textsModel->delete($whereKeys)) {
			return false;
		}
		
		$wordsModel = FrameAfsw::_()->getModule('indexing')->getModel('index_words');
		$phradesModel = FrameAfsw::_()->getModule('indexing')->getModel('index_phrases');
		
		$this->wordsModel = $wordsModel;
		$this->phradesModel = $phradesModel;
		
		$this->selectType = array(
			" IF(EXISTS(SELECT 1 FROM `#__posts` as pa WHERE pa.post_parent=p.ID AND pa.post_type='product_variation' LIMIT 1), 1, 0) as pr_type",
			' 2 as pr_type'
		);
		$this->from = array(
			' FROM `#__posts` as p',
			' FROM `#__posts` as p'
		);
		$this->where = array(
			" WHERE p.post_type='product' AND p.post_status IN('publish','private')" . ( $isAllProducts ? '' : ' AND p.id' . $productListP ),
			" WHERE p.post_type='product_variation' AND p.post_status IN('publish','private')" . ( $isAllProducts ? '' : ' AND p.id' . $productListV )
		);
		$tempTableP  = false;
		$tempTableV = false;
		if ($isAllProducts) {
			$query = 'SELECT id,' . $this->selectType[0] . ' FROM `#__posts` as p' . $this->where[0];
			$tempTableP  = DbAfsw::createTemporaryTable('afsw_index_products', $query);
			$this->selectType[0] = 'p.pr_type';
			$this->from[0] = ' FROM ' . $tempTableP . ' as p ';
			$this->where[0] = ' WHERE 1=1';
		}
		
		DbAfsw::query('SET session wait_timeout=600');
		$prefixes = $this->getNeededPrefixes();
		$fields = '';
		$select = '';
		foreach ($prefixes as $field => $num) {
			$fields .= ',' . $field;
			$select .= ', IF(LENGTH(word)>' . $num . ',LEFT(word,' . $num . '),' . "'')";
		}
		$insertWords = 'INSERT IGNORE @__index_words (value' . $fields . ') SELECT word' . $select . ' FROM @__temp_words';
		$maxPhraseLength = $this->maxPhraseLength;
		$prevParent = 0;
		foreach ($keys as $key) {
			$inxId = $key['id'];
			
			$status = $key['status'];
			$needLock   = $isAllProducts;
			$needRecalc = false;
			if (2 == $status) {
				$lock = $key['lock_duration'];
				if (is_null($lock) || empty($lock) || $lock > $this->lockLimit) {
					if (!$isAllProducts) {
						$needRecalc = true;
					}
				} else {
					continue;
				}
			}
			
			if ($needRecalc || $needLock) {
				if (!$this->updateKeyData($inxId, array('status' => $needLock ? 2 : 0))) { // set new lock
					return false;
				}
			}
			
			$inxKey = $key['inx_key'];
			$inxName = $key['inx_name'];
			$inxType = $key['inx_type'];
			$isTexts = ( 2 == $inxType );
			$isWords = ( 1 == $key['words'] );
			$isPhrases = ( 1 == $key['phrases'] );
			$inxSource = $key['inx_source'];
			$withVars = $key['with_vars'];

			if ($withVars && $isAllProducts && !$tempTableV) {
				$tempTableV  = DbAfsw::createTemporaryTable('afsw_index_variations', 'SELECT id, 2 as pr_type FROM `#__posts` as p' . $this->where[1]);
				$this->selectType[1] = 'p.pr_type';
				$this->from[1] = ' FROM ' . $tempTableV . ' as p ';
				$this->where[1] = ' WHERE 1=1';
			}
			if ('afsw_parent_taxonomies' != $inxName) {
				if (!$this->resetTempTable('temp_phrases')) {
					return false;
				}
			}
			if ($isWords) {
				if (!$this->resetTempTable('temp_words')) {
					return false;
				}
			}
			$query = '';
			$func = 'fillTempPhrasesP_' . $inxName;
			$existsP = 0;
			$existsV = 0;

			if (method_exists($this, $func)) {
				$existsP = $this->$func($key);
				if (false === $existsP) {
					return false;
				}
			} else {
				switch ($inxSource) {
					case 1:
						if ($isTexts) {
							$query = 'INSERT INTO @__index_texts (product_id, pr_type, key_id, value)' .
							' SELECT p.id, ' . $this->selectType[0] . ', ' . $inxId . ', ' . $inxName;
						} else {
							$query = 'INSERT INTO @__temp_phrases (product_id, pr_type, key_id, phrase)' .
								' SELECT p.id, ' . $this->selectType[0] . ', ' . $inxId . ', LOWER(CAST(TRIM(' . $inxName . ') AS CHAR(' . $maxPhraseLength . ')))';
						}
						$query .= $this->from[0] .
							( $isAllProducts ? ' INNER JOIN #__posts pp ON (pp.ID=p.ID)' : '' ) .
							$this->where[0] . ' AND NOT ISNULL(' . $inxName . ') AND ' . $inxName . "!=''";
						break;
					case 2:
						$query = 'INSERT INTO @__temp_phrases (product_id, pr_type, key_id, phrase, term_id)' .
							' SELECT p.id, ' . $this->selectType[0] . ', ' . $inxId . ', LOWER(TRIM(te.name)), te.term_id' . 
							$this->from[0] .
							' INNER JOIN #__term_relationships tr ON (tr.object_id=p.ID)' .
							' INNER JOIN #__term_taxonomy tt ON (tt.term_taxonomy_id=tr.term_taxonomy_id) ' .
							' INNER JOIN #__terms te ON (te.term_id=tt.term_id) ' .
							$this->where[0] . " AND tt.taxonomy='" . $inxName . "'";
						break;
					case 3:
						if (9 == $inxType) {
							$existsP = $this->fillPhrasesFromMetaArray($key, $inxName, 0);
							if (false === $existsP) {
								return false;
							}
						} else {
							$query = $this->getMetaQuery($key, 0, " AND m.meta_key='" . $inxName . "'");
						}
						break;
					default:
						break;
				}
				$existsP = empty($query) ? $existsP : DbAfsw::query($query, true);
				if (false === $existsP) {
					FrameAfsw::_()->pushError(DbAfsw::getError());
					FrameAfsw::_()->pushError('Error query: ' . $query); 
					return false;
				}
			}
			if (!empty($withVars)) {
				$func = 'fillTempPhrasesV_' . $inxName;
				if (method_exists($this, $func)) {
					$existsV = $this->$func($key);
					if (false === $existsV) {
						return false;
					}
				} else {
					switch ($inxSource) {
						case 1:
							if ($isTexts) {
								$query = 'INSERT INTO @__index_texts (product_id, pr_type, key_id, value)' .
								' SELECT p.id, 2, ' . $inxId . ', ' . $inxName;
							} else {
								$query = 'INSERT INTO @__temp_phrases (product_id, p.pr_type, key_id, phrase)' .
									' SELECT p.id, 2, ' . $inxId . ', LOWER(CAST(TRIM(' . $inxName . ') AS CHAR(' . $maxPhraseLength . ')))';
							}
							$query .= $this->from[1] .
								( $isAllProducts ? ' INNER JOIN #__posts pp ON (pp.ID=p.ID)' : '' ) .
								$this->where[1] . ' AND NOT ISNULL(' . $inxName . ') AND ' . $inxName . "!=''";
							break;
						case 2:
							if ('attribute' == $inxKey) {
								$query = $this->getMetaQuery($key, 1, " AND m.meta_key='attribute_" . $inxName . "'");
							}
							break;
						case 3:
							$query = $this->getMetaQuery($key, 1, " AND m.meta_key='" . $inxName . "'");
							break;
						default:
					}
					$existsV = empty($query) ? 0 : DbAfsw::query($query, true);
					if (false === $existsV) {
						FrameAfsw::_()->pushError(DbAfsw::getError());
						FrameAfsw::_()->pushError('Error query: ' . $query); 
						return false;
					}
				}
			}
			
			if (!$isTexts && ( $existsP || $existsV )) {
				if (!$this->replaceSpecialChars()) {
					return false;
				}
				$query = 'UPDATE @__temp_phrases SET hash=MD5(phrase)';
				if ($isWords) {
					$query .= ",spaces=length(phrase)-length(replace(phrase,' ',''))";
				}
				if (!DbAfsw::query($query)) {
					FrameAfsw::_()->pushError(DbAfsw::getError());
					FrameAfsw::_()->pushError('Error query: ' . $query); 
					return false;
				}
				
				if ($isPhrases) {
					// save phrases
					$query = 'INSERT IGNORE @__index_phrases (value, hash) SELECT phrase, hash FROM @__temp_phrases';
					if (!DbAfsw::query($query)) {
						FrameAfsw::_()->pushError(DbAfsw::getError());
						FrameAfsw::_()->pushError('Error query: ' . $query); 
						return false;
					}
					$query = 'INSERT IGNORE @__index_data (product_id, pr_type, key_id, inx_mode, inx_id, updated)' .
						'SELECT t.product_id, t.pr_type, t.key_id, 0, p.id, CURRENT_TIMESTAMP' . 
						' FROM @__temp_phrases as t' . 
						' INNER JOIN @__index_phrases p ON (p.hash=t.hash)';
					if (!DbAfsw::query($query)) {
						FrameAfsw::_()->pushError(DbAfsw::getError());
						FrameAfsw::_()->pushError('Error query: ' . $query); 
						return false;
					}
				}
				
				if ($isWords) {
				
					$query = 'SELECT min(spaces), max(spaces) FROM @__temp_phrases';
					$counts = DbAfsw::get($query, 'row', ARRAY_N);
					if (!$counts) {
						FrameAfsw::_()->pushError(DbAfsw::getError());
						FrameAfsw::_()->pushError('Error query: ' . $query); 
						return false;
					}
					$minSpaces = (int) $counts[0];
					$maxSpaces = (int) $counts[1];
					$i = $minSpaces;
					$maxWords = empty($maxSpaces) ? 0 : $maxSpaces + 1;
					do {
						if (empty($i)) {
							$query = 'INSERT INTO @__temp_words SELECT id, phrase FROM @__temp_phrases WHERE spaces=0';
						} else {
							$query = 'INSERT INTO @__temp_words' .
							" SELECT id, SUBSTRING_INDEX(SUBSTRING_INDEX(phrase, ' ', " . $i . "), ' ', -1)" .
							' FROM @__temp_phrases' .
							' WHERE spaces>' . ( $i > 1 ? '=' : '' ) . ( $i - 1 );
						}
						if (!DbAfsw::query($query)) {
							FrameAfsw::_()->pushError(DbAfsw::getError());
							FrameAfsw::_()->pushError('Error query: ' . $query); 
							return false;
						}
						$i++;
					} while ($i <= $maxWords);
					// save words
					if (!DbAfsw::query($insertWords)) {
						FrameAfsw::_()->pushError(DbAfsw::getError());
						FrameAfsw::_()->pushError('Error query: ' . $insertWords); 
						return false;
					}
					$query = 'INSERT IGNORE @__index_data (product_id, pr_type, key_id, inx_mode, inx_id, updated)' .
						'SELECT tp.product_id, tp.pr_type, tp.key_id, 1, w.id, CURRENT_TIMESTAMP' . 
						' FROM @__temp_phrases as tp' . 
						' INNER JOIN @__temp_words tw ON (tw.phrase_id=tp.id)' .
						' INNER JOIN @__index_words w ON (w.value=tw.word)';
					if (!DbAfsw::query($query)) {
						FrameAfsw::_()->pushError(DbAfsw::getError());
						FrameAfsw::_()->pushError('Error query: ' . $query); 
						return false;
					}
					
				}
			}
			if (!$this->updateKeyData($inxId, array('status' => 1))) {
				return false;
			}
		}
		
		if ($fullRecalc) {
			set_time_limit(300);
			
			$query = 'DELETE w FROM @__index_phrases w' .
				' WHERE NOT EXISTS(SELECT 1 FROM @__index_data d WHERE d.inx_mode=0 AND d.inx_id=w.id LIMIT 1)';
			if (!DbAfsw::query($query)) {
				FrameAfsw::_()->pushError(DbAfsw::getError());
				FrameAfsw::_()->pushError('Error query: ' . $query); 
				return false;
			}
			$query = 'DELETE w FROM @__index_words w' .
				' WHERE NOT EXISTS(SELECT 1 FROM @__index_data d WHERE d.inx_mode=1 AND d.inx_id=w.id LIMIT 1)';
			if (!DbAfsw::query($query)) {
				FrameAfsw::_()->pushError(DbAfsw::getError());
				FrameAfsw::_()->pushError('Error query: ' . $query); 
				return false;
			}
			if (!$textsModel->addIndexes()) {
				return false;
			}
			
			$query = '';
			$where = '';
			foreach ($prefixes as $field => $num) {
				if (DbAfsw::get('SELECT 1 FROM @__index_words WHERE ' . $field . ' IS NULL LIMIT 1', 'one') == '1') {
					$query .= $field . '=IF(LENGTH(value)>' . $num . ',LEFT(value,' . $num . '),' . "''),";
					$where .= $field . ' IS NULL OR ';
				}
			}
			if (!empty($query)) {
				$query = 'UPDATE @__index_words SET ' . substr($query, 0, -1) . ' WHERE ' . substr($where, 0, -3);
				if (!DbAfsw::query($query)) {
					FrameAfsw::_()->pushError(DbAfsw::getError());
					FrameAfsw::_()->pushError('Error query: ' . $query); 
					return false;
				}
			}
			
			$this->optimizeIndexTables();
			$optModel->save('start_indexing', 0);
		}
		
		return true;
	}
	
	public function resetTempTable( $table, $where = '' ) {
		$query = 'DELETE FROM @__' . $table . $where;
		if (!DbAfsw::query($query)) {
			FrameAfsw::_()->pushError(DbAfsw::getError());
			FrameAfsw::_()->pushError('Error query: ' . $query); 
			return false;
		}
		return true;
	}
	
	public function fillTempPhrasesP_afsw_local_attributes( $key ) {
		return $this->fillPhrasesFromMetaArray($key, '_product_attributes', 0);
	}
		
	public function fillTempPhrasesV_afsw_local_attributes( $key ) {
		$query = $this->getMetaQuery($key, 1, " AND m.meta_key LIKE 'attribute_%' AND m.meta_key NOT LIKE 'attribute_pa_%'");
		$exists = DbAfsw::query($query, true);
		if (false === $exists) {
			FrameAfsw::_()->pushError(DbAfsw::getError());
			FrameAfsw::_()->pushError('Error query: ' . $query); 
			return false;
		}
		
		return $exists;
	}
	public function fillTempPhrasesV_post_content( $key ) {
		$query = $this->getMetaQuery($key, 1, " AND m.meta_key='_variation_description'");
		$exists = DbAfsw::query($query, true);
		if (false === $exists) {
			FrameAfsw::_()->pushError(DbAfsw::getError());
			FrameAfsw::_()->pushError('Error query: ' . $query); 
			return false;
		}
		
		return $exists;
	}
	public function fillTempPhrasesP_afsw_parent_taxonomies( $key ) {
		$inxId = $key['id'];
		$inxKey = $key['inx_key'];
		$tempModel = FrameAfsw::_()->getModule('indexing')->getModel('temp_phrases');
		if ($this->recalcAllProducts) {
			$tempModel->addIndexes();
		}
		$i = 0;
		$maxIter = 5;
		$exists = 0;
		do {
			$query = 'INSERT INTO @__temp_phrases (product_id, pr_type, key_id, phrase, term_id, num)' .
				' SELECT f.product_id, f.pr_type, ' . $inxId . ', LOWER(TRIM(p.name)), p.term_id, ' . ( $i + 1 ) .
				' FROM @__temp_phrases f' .
				' INNER JOIN #__term_taxonomy t ON (t.term_taxonomy_id=f.term_id)' .
				' INNER JOIN #__terms p ON (p.term_id=t.parent)' .
				' LEFT JOIN #__term_relationships r ON (r.object_id=f.product_id AND r.term_taxonomy_id=t.parent)' .
				' WHERE ISNULL(r.object_id) AND f.num=' . $i;
			$cnt = DbAfsw::query($query, true);
			if (false === $cnt) {
				FrameAfsw::_()->pushError(DbAfsw::getError());
				FrameAfsw::_()->pushError('Error query: ' . $query); 
				return false;
			}
			$exists += $cnt;
			$i++;
		} while ( $i < $maxIter && !empty($cnt) );
		
		if (!$this->resetTempTable('temp_phrases', ' WHERE key_id=' . $key['parent'])) {
			return false;
		}
		if ($this->recalcAllProducts) {
			$tempModel->dropIndexes();
		}
		return $exists;
	}
	
	public function getMetaQuery( $key, $isVar, $where ) {
		if (2 == $key['inx_type']) {
			$query = 'INSERT INTO `@__index_texts` (product_id, pr_type, key_id, value)' .
				'SELECT p.id, ' . $this->selectType[$isVar] . ', ' . $key['id'] . ', m.meta_value';
		} else {
			$query = 'INSERT INTO `@__temp_phrases` (product_id, pr_type, key_id, phrase)' .
				'SELECT p.id, ' . $this->selectType[$isVar] . ', ' . $key['id'] . ', LOWER(CAST(TRIM(m.meta_value) AS CHAR(' . $this->maxPhraseLength . ')))';
		}
		return $query . $this->from[$isVar] .
			' INNER JOIN `#__postmeta` m ON (m.post_id=p.id)' .
			$this->where[$isVar] .
			$where . " AND trim(m.meta_value)!=''";
	}
	
	public function fillPhrasesFromMetaArray( $key, $metaKey, $isVar = 0 ) {
		$query = 'SELECT post_id, ' . $this->selectType[$isVar] . ', meta_value' . 
			$this->from[$isVar] .
			' INNER JOIN `#__postmeta` m ON (m.post_id=p.id)' .
			$this->where[$isVar] . " AND m.meta_key='" . $metaKey . "'" .
			' ORDER BY meta_id LIMIT ';

		$exists = $this->saveMetaArrays($key, $query);
		if (false === $exists) {
			return false;
		}
		return $exists;
	}
	
	public function saveMetaArrays( $key, $partQuery, $isVar = 0 ) {
		$limit = $this->limitQuery;
		$offset = 0;
		$insert = 'INSERT INTO `@__temp_phrases` (product_id, pr_type, key_id, phrase)';
		$func = 'saveMetaArray_' . $key['inx_name'];
		if (!method_exists($this, $func )) {
			$func = 'saveMetaArrayDefault';
		}
		$keyId = $key['id'];
		$replace = $key['list'];
		if (empty($replace)) {
			$replace = array();
		} else {
			$replace = UtilsAfsw::jsonDecode($replace);
		}
		$exists = 0;
		do {
			$query = $partQuery . $offset . ',' . $limit;
			$data = DbAfsw::get($query);
			if (false === $data) { 
				$this->pushError(DbAfsw::getError());
				$this->pushError('Error query:' . $query);
					return false;
			}
			$j = 0;
			$lastData = count($data) - 1;
			$insertValues = '';
			foreach ($data as $k => $values) {
				$insValues = $this->$func('(' . $values['post_id'] . ',' . $values['pr_type'] . ',' . $keyId . ',', $values['meta_value'], $replace);
				if (!empty($insValues)) {
					$j++;
					$insertValues .= $insValues;
				}
				if ($j >= 100 || $k >=  $lastData) {
					if (!empty($insertValues)) {
						if (!DbAfsw::query($insert . ' VALUES ' . substr($insertValues, 0, -1))) {
							$this->pushError(DbAfsw::getError());
							return false;
						}
					}
					$exists = 1;
					$insertValues = '';
					$j = 0;
				}
			}
			$offset += $limit;
		} while ( !empty($data) && ( count($data) >= $limit ) );
		return $exists;
	}
	
	public function saveMetaArrayDefault( $pre, $values, $replace ) {
		$valuesArr = unserialize($values);
		$q = '';
		if (is_array($valuesArr)) {
			foreach ($valuesArr as $value) {
				$value = trim($value);
				if (!empty($value)) {
					if (isset($replace[$value])) {
						$value = $replace[$value];
					}
					$q .= $pre . "'" . $this->getCutPhraseValue($value) . "'),";
				}
			}
		}
		return $q;
	}
	
	public function saveMetaArray_afsw_local_attributes( $pre, $values, $replace ) {
		$valuesArr = unserialize($values);
		$q = '';
		if (is_array($valuesArr)) {
			foreach ($valuesArr as $attr => $val) {
				if (is_array($val) && isset($val['is_taxonomy']) && ( '1' != $val['is_taxonomy'] ) && !empty($val['value'])) {
					$values = explode('|', $val['value']);
					foreach ($values as $value) {
						$value = trim($value);
						if (!empty($value)) {
							$q .= $pre . "'" . $this->getCutPhraseValue($value) . "'),";
						}
					}
				}
			}
		}
		return $q;
	}

	public function getCutPhraseValue( $str, $cut = true ) {
		if ($this->existMB) {
			if (mb_strlen($str) > $this->maxPhraseLength) {
				return $cut ? mb_substr($str, 0, $this->maxPhraseLength) : '';
			}
		} else {
			if (strlen($str) > $this->maxPhraseLength) {
				return $cut ? substr($str, 0, $this->maxPhraseLength) : '';
			}
		}
		return $str;
	}

	public function optimizeIndexTables() {
		$optimizeTables = array( 'index_data', 'index_words', 'index_phrases', 'index_texts', 'history', 'cache' );
		foreach ( $optimizeTables as $table ) {
			if (!DbAfsw::query('OPTIMIZE TABLE `@__' . $table . '`')) {
				$this->pushError(DbAfsw::getError());
				return false;
			}
		}
		return true;
	}
}
