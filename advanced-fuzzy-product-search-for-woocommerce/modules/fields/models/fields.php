<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class FieldsModelAfsw extends ModelAfsw {
	// status: 9 - deleted
	
	private $encodedFields = array('css', 'add_css', 'add_js');
	private $jsonedFields = array('options', 'field', 'autocomplete', 'search');
	private $stringJsonFields = [];
	
	private static $_cntParents = 0;
	private static $_termParents = [];
	
	public function getCountParents() {
		return self::$_cntParents;
	}
	public function getTermParents() {
		return self::$_termParents;
	}
	
	public function __construct() {
		$this->_setTbl('fields');
	}
	
	public function createField( $data = array() ) {
		$field = array('title' => empty($data['title']) ? gmdate('Y-m-d-h-i-s') : $data['title']);
		
		$id = $this->insert($field);
		if ($id) {
			if (empty($data['title'])) {
				$this->updateById(array('title' => 'Field ' . $id), $id);
			}
		}
		return $id;
	}
	public function prepareFieldsList( $fields ) {
		$rows = array();
		$editUrl = FrameAfsw::_()->getModule('adminmenu')->getTabUrl('fields-edit');
		$btnDelete = __('Are you sure to delete this?', 'advanced-fuzzy-search') . '<div class="buttons"><button>' . __('Cancel', 'advanced-fuzzy-search') . '</button><button class="afsw-delete">' . __('Confirm', 'publish-your-table') . '</button></div>';
		foreach ($fields as $field) {
			$id = $field['id'];
			$rows[] = array(
				'<input type="checkbox" class="afswCheckOne" data-id="' . $id . '">', 
				$id, 
				'<a href="' . esc_url($editUrl . '&id=' . $id) . '" class="afsw-edit-link">' . $field['title'] . '</a>',
				'<input type="text" class="woobewoo-shortcode woobewoo-flat-input" readonly value="[afsw-fields id=' . $id . ']">',
				'<div class="woobewoo-list-actions" data-id="' . $id . '"><i class="fa fa-fw fa-sign-in afsw-edit woobewoo-tooltip" title="' . esc_attr__('Edit', 'advanced-fuzzy-search') .
					'"></i><i class="fa fa-fw fa-gear afsw-options woobewoo-tooltip" title="' . esc_attr__('Settings', 'advanced-fuzzy-search') .
					'"></i>' . DispatcherAfsw::applyFilters('addFieldsListActions', '', $field) . 
					'<i class="fa fa-fw fa-copy afsw-clone woobewoo-tooltip" title="' . esc_attr__('Clone', 'advanced-fuzzy-search') .
					'"></i><i class="fa fa-fw fa-trash-o woobewoo-tooltip afsw-delete" title="' . esc_attr($btnDelete . '<div class="afswHidden">' . $id . '</div>') .
					'"></i></div>'
			);
		}
		return $rows;
	}
	
	public function getFieldData( $id, $column = false, $raw = false ) {
		$field = $this->getById($id);
		if (!$field) {
			FrameAfsw::_()->pushError(esc_html__('Field not found.', 'advanced-fuzzy-search'));
			return false;
		}
		if ($column) {
			if (!$raw ) {
				if (in_array($column, $this->encodedFields)) {
					return stripslashes(base64_decode($field[$column]));
				}
				if (in_array($column, $this->jsonedFields)) {
					return UtilsAfsw::jsonDecode($field[$column]);
				}
			}
			return $field[$column];
		}
		if (!$raw) {
			foreach ($this->encodedFields as $key) {
				$field[$key] = stripslashes(base64_decode($field[$key]));
			}
			foreach ($this->jsonedFields as $key) {
				$field[$key] = UtilsAfsw::jsonDecode($field[$key]);
			}
		}

		return $field;
	}
	
	public function getSettingsParams( $column = '', $id = 0 ) {
		$this->setSelectFields('id,' . $column);
		if (!empty($id)) {
			$this->setWhere(array('id' => $id));
		}
		$data = array();
		$fields = $this->getFromTbl();
		if ($this->getLastGetCount() > 0) {
			foreach ($fields as $options) {
				$data[$options['id']] = UtilsAfsw::jsonDecode($options[$column]);
			}
		}
		return $data;
	}
	
	public function saveTitle( $id, $title ) {
		if (empty($id)) {
			FrameAfsw::_()->pushError(esc_html__('Id can\'t be empty', 'advanced-fuzzy-search'));
			return false;
		}
		if (empty($title)) {
			FrameAfsw::_()->pushError(esc_html__('Title can\'t be empty', 'advanced-fuzzy-search'));
			return false;
		}
		return $this->updateById(array('title' => $title), $id);
	}
	
	public function controlFieldData( $data ) {
		foreach (DispatcherAfsw::applyFilters('addStringJsonFields', $this->stringJsonFields) as $block => $settings) {
			if (isset($data[$block])) {
				foreach ($settings as $key) {
					if (!empty($data[$block][$key]) && !is_array($data[$block][$key])) {
						$data[$block][$key] = UtilsAfsw::jsonDecode(stripcslashes($data[$block][$key]));
					}
				}
			}
		}
		return DispatcherAfsw::applyFilters('controlFieldData', $data);
	}
	
	public function saveField( $data = array(), $clone = false ) {
		$id = isset($data['id']) ? $data['id'] : false;
		if (empty($id)) {
			FrameAfsw::_()->pushError(esc_html__('Field not found', 'advanced-fuzzy-search'));
			return false;
		}
		$data = $this->controlFieldData($data);
		$data['css'] = $this->getModule()->getView()->generateCss($id, $data);
		$options = UtilsAfsw::getArrayValue($data, 'options', array(), 2);
		$data['theme_replace'] = UtilsAfsw::getArrayValue($options, 'replace_theme_field', 0, 1) == 1 ? 1 : 0;
		
		$columns = array();
		foreach ($data as $col => $value) {
			if (in_array($col, $this->encodedFields)) {
				$columns[$col] = empty($value) ? '' : base64_encode(stripslashes($value));
			} else if (in_array($col, $this->jsonedFields) && is_array($value)) {
				$columns[$col] = UtilsAfsw::jsonEncode($value, true);
			} else {
				$columns[$col] = $value;
			}
		}

		if (count($columns) > 0) {
			$columns['updated'] = 'NOW()';
			$this->updateById($columns, $id);
			$this->setFieldUpdatedFlag();
		}
		DispatcherAfsw::doAction('updateFieldData', $data);
		if (!$clone) {
			FrameAfsw::_()->getModule('fields')->getModel('cache')->clearCache($id);
		}
		
		return true;
	}
	
	public function getThemeReplaceField() {
		return DbAfsw::get('SELECT id FROM @__fields WHERE theme_replace=1 LIMIT 1', 'one');
	}
	
	public function getTaxonomyHierarchy( $taxonomy, $argsIn, $parent = true, $add = '-', $r = 0 ) {
		if (empty($r)) {
			self::$_cntParents = 0;
			self::$_termParents = [];
		} 
		$taxonomy = is_array( $taxonomy ) ? array_shift( $taxonomy ) : $taxonomy;
		$args = array(
			'hide_empty' => $argsIn['hide_empty'],
		);
		if (isset($argsIn['order'])) {
			$args['orderby'] = !empty($argsIn['orderby']) ? $argsIn['orderby'] : 'name';
			$args['order'] = $argsIn['order'];
		}

		if (!empty($argsIn['parent']) && 0 !== $argsIn['parent']) {
			$args['parent'] = $argsIn['parent'];
		} else if ('' !== $argsIn['parent']) {
			$args['parent'] = 0;
		}
		
		if (!empty($argsIn['include'])) {
			$args['include'] = $argsIn['include'];
		}

		if ('' === $taxonomy) {
			return false;
		}

		if ( 'product_cat' === $taxonomy && $parent ) {
			$args['parent'] = 0;
		}

		$terms = get_terms( $taxonomy, $args );
		$children = array();
		if (!is_wp_error($terms)) {
			foreach ( $terms as $term ) {
				$term->children = array();
				if (empty($argsIn['only_parent']) && '' !== $argsIn['parent']) {
					if (!empty($term->term_id)) {
						$args = array(
							'hide_empty' => $argsIn['hide_empty'],
							'parent' => $term->term_id,
						);
						if (isset($argsIn['order'])) {
							$args['order']   = $argsIn['order'];
							$args['orderby'] = !empty($argsIn['orderby']) ? $argsIn['orderby'] : 'name';
						}
						$term->children = $this->getTaxonomyHierarchy( $taxonomy, $args, false, $add, $r + 1 );
					}
				}
				
				if (false === $add) {
					$children[ $term->term_id ] = $term->name;
					if (empty($r)) {
						self::$_cntParents++;
					}
					if (count($term->children) > 0) {
						self::$_termParents[$term->term_id] = count($term->children);
						$children[ $term->term_id . '_f' ] = $r;
						foreach ($term->children as $k => $t) {
							$children[ $k ] = $t;
						}
						$children[ $term->term_id . '_e' ] = $r;
					}
				} else {
					$children[ $term->term_id ] = str_repeat($add, $r) . $term->name;
					foreach ($term->children as $k => $t) {
						$children[ $k ] = str_repeat($add, $r) . $t;
					}
				}
			}
		}
		return $children;
	}
	
	public function getAttributesDisplay() {
		$productAttr = function_exists('wc_get_attribute_taxonomies') ? wc_get_attribute_taxonomies() : array();
		$attrs = array();
		foreach ($productAttr as $attr) {
			$attrs['pa_' . $attr->attribute_name] = $attr->attribute_label;
		}

		return $attrs;
	}
	
	protected function setFieldUpdatedFlag() {
		FrameAfsw::_()->getModule('options')->getModel()->save('field_updated', time());
		return true;
	}
	
	protected function _dataRemove( $ids ) {
		foreach ($ids as $id) {
			$this->getModule()->getModel('cache')->clearCache($id);
			$this->getModule()->getModel('history')->clearHistory($id);
			DispatcherAfsw::doAction('removeField', $id);
			$this->setFieldUpdatedFlag();
		}
		return false;
	}
	
	public function cloneField( $id ) {
		$field = $this->getFieldData($id);
		if (!$field) {
			return false;
		}
		$field['title'] .= '-clone';
		$newId = $this->createField(array('title' => $field['title']));
		if ($newId) {
			$field['id'] = $newId;
			unset($field['css'], $field['updated']);
			$field = $this->saveField($field, true);
		}
		return $newId;
	}
}
