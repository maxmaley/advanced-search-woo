<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Fields_WidgetViewAfsw extends ViewAfsw {
	public function displayWidget( $instance, $args ) {
		if (isset($instance['id']) && $instance['id'] ) {
			$widget = do_shortcode('[' . AFSW_SHORTCODE . ' id=' . $instance['id'] . ' mode="widget"]');
			if ('' !== $widget) {
				if (isset( $args['before_widget']) && isset($args['after_widget'])) {
					$widget = $args['before_widget'] . $widget . $args['after_widget'];
				}
				HtmlAfsw::echoEscapedHtml($widget);
			}
		}
	}

	public function displayFormWidget( $data, $widget ) {
		FrameAfsw::_()->addStyle('woofilters_widget', $this->getModule()->getModPath() . 'assets/css/gmap_widget.css');
		$this->assign('fields', $this->getFieldList());
		$this->displayWidgetForm($data, $widget, 'formWidget');
	}
	
	public function displayFormNavMenuBox() {
		parent::display('formNavMenuBox');
	}
	public function displayFormNavMenuItem( $item_id, $item ) {
		$fieldId = get_post_meta( $item_id, '_menu_item_afsw_search_field_id', true );
		
		$this->assign('fields', $this->getFieldList());
		$this->assign('field_id', $fieldId);
		$this->assign('item_id', $item_id);
		
		parent::display('formNavMenuItem');
	}
	
	public function getFieldList() {
		$fields = FrameAfsw::_()->getModule('fields')->getModel()->getFromTbl();
		$fieldsOpts = array();
		if (empty($fields)) {
			$fieldsOpts[0] = esc_html__('You have no search fields', 'advanced-fuzzy-search');
		} else {
			$fieldsOpts[0] = 'Select';
			foreach ($fields as $field) {
				$fieldsOpts[$field['id']] = $field['title'];
			}
		}
		return $fieldsOpts;
	}
	public function displaySearchFieldInMenu( $item ) {
		$args   = '';
		$style  = '';
		$fieldId = get_post_meta( $item->ID, '_menu_item_afsw_search_field_id', true );
		$itemOutput = '';
		if (!empty($fieldId)) {
			$itemOutput = do_shortcode( sprintf( '[afsw-fields id=%d]', $fieldId ) );
		}

		return $itemOutput;
	}
}
