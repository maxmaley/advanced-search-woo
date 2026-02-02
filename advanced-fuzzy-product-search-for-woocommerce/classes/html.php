<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class HtmlAfsw {
	public static $categoriesOptions = array();
	public static $productsOptions = array();
	public static $colsType = 'standart';
	public static $colClasses = array(
		'standart' => array('label' => 'col-3 col-xl-2', 'values' => 'col-8 col-sm-9 col-xl-9', 'full' => 'col-12'),
		'compact' => array('label' => 'col-3 col-xl-3 col-sm-5', 'info' => 'col-xs-2 col-sm-1', 'values' => 'col-9 col-xl-9 col-sm-7', 'full' => 'col-12'),
		'popup' => array('label' => 'col-5 col-sm-3', 'info' => 'col-1', 'values' => 'col-7 col-sm-9', 'full' => 'col-12'),
		'super' => array('label' => 'col-3 col-sm-2', 'info' => 'col-1', 'values' => 'col-8 col-sm-9', 'full' => 'col-12'),
		);
	public static function setColType( $type ) {
		if (isset(self::$colClasses[$type])) {
			self::$colsType = $type;
		}
	}
	public static function blockClasses( $type ) {
		return 'options-' . $type . ' ' . UtilsAfsw::getArrayValue(self::$colClasses[self::$colsType], $type);
	}
	public static function echoEscapedHtml( $html ) {
		add_filter('esc_html', array('HtmlAfsw', 'skipHtmlEscape'), 99, 2);
		echo esc_html($html);
		remove_filter('esc_html', array('HtmlAfsw', 'skipHtmlEscape'), 99, 2);
	}
	public static function skipHtmlEscape( $safe_text, $text ) {
		return $text;
	}
	public static function block( $name, $params = array('attrs' => '', 'value' => '') ) {
		$output .= '<p class="toe_' . self::nameToClassId($name) . '">' . $params['value'] . '</p>';
		return $output;
	}
	public static function nameToClassId( $name, $params = array() ) {
		if (!empty($params) && isset($params['attrs']) && strpos($params['attrs'], 'id="') !== false) {
			preg_match('/id="(.+)"/ui', $params['attrs'], $idMatches);
			if ($idMatches[1]) {
				return $idMatches[1];
			}
		}
		return str_replace(array('[', ']'), '', $name);
	}
	public static function textarea( $name, $params = array('attrs' => '', 'value' => '', 'rows' => 3, 'cols' => 50) ) {
		$params['attrs'] = isset($params['attrs']) ? $params['attrs'] : '';
		$params['rows'] = isset($params['rows']) ? $params['rows'] : 3;
		$params['cols'] = isset($params['cols']) ? $params['cols'] : 50;
		if (isset($params['required']) && $params['required']) {
			$params['attrs'] .= ' required ';	// HTML5 "required" validation attr
		}
		if (isset($params['placeholder']) && $params['placeholder']) {
			$params['attrs'] .= ' placeholder="' . esc_attr($params['placeholder']) . '"';	// HTML5 "required" validation attr
		}
		if (isset($params['disabled']) && $params['disabled']) {
			$params['attrs'] .= ' disabled ';
		}
		if (isset($params['readonly']) && $params['readonly']) {
			$params['attrs'] .= ' readonly ';
		}
		if (isset($params['auto_width']) && $params['auto_width']) {
			unset($params['rows']);
			unset($params['cols']);
		}
		echo '<textarea name="' . esc_attr($name) . '" ';
		if (!empty($params['attrs'])) {
			self::echoEscapedHtml($params['attrs']);
		}
		echo ( isset($params['rows']) ? ' rows="' . esc_attr($params['rows']) . '"' : '' ) .
			( isset($params['cols']) ? ' cols="' . esc_attr($params['cols']) . '"' : '' ) . '>' .
			( isset($params['value']) ? esc_html($params['value']) : '' ) .
		'</textarea>';
	}
	public static function input( $name, $params = array('attrs' => '', 'type' => 'text', 'value' => '') ) {
		$params['attrs'] = isset($params['attrs']) ? $params['attrs'] : '';
		$params['attrs'] .= self::_dataToAttrs($params);
		if (isset($params['required']) && $params['required']) {
			$params['attrs'] .= ' required ';	// HTML5 "required" validation attr
		}
		if (isset($params['placeholder']) && $params['placeholder']) {
			$params['attrs'] .= ' placeholder="' . esc_attr($params['placeholder']) . '"';	// HTML5 "required" validation attr
		}
		if (isset($params['disabled']) && $params['disabled']) {
			$params['attrs'] .= ' disabled ';
		}
		if (isset($params['readonly']) && $params['readonly']) {
			$params['attrs'] .= ' readonly ';
		}
		$params['type'] = isset($params['type']) ? $params['type'] : 'text';
		$params['value'] = isset($params['value']) ? $params['value'] : '';

		echo '<input type="' . esc_attr($params['type']) . '" name="' . esc_attr($name) . '" value="' . esc_attr($params['value']) . '" ';
		if (!empty($params['attrs'])) {
			self::echoEscapedHtml($params['attrs']);
		}
		echo ' />';
	}
	public static function inputShortcode( $name, $params = array() ) {
		$value = $params['value'];
		self::input('', array('value' => $value, 'attrs' => 'readonly class="woobewoo-flat-input woobewoo-nosave woobewoo-shortcode woobewoo-width' . ( strlen($value) <= 20 ? 200 : 300 ) . '"'));
	}
	private static function _dataToAttrs( $params ) {
		$res = '';
		foreach ($params as $k => $v) {
			if (strpos($k, 'data-') === 0) {
				$res .= ' ' . $k . '="' . $v . '"';
			}
		}
		return $res;
	}
	public static function text( $name, $params = array('attrs' => '', 'value' => '') ) {
		$params['type'] = 'text';
		self::input($name, $params);
	}
	public static function email( $name, $params = array('attrs' => '', 'value' => '') ) {
		$params['type'] = 'email';
		self::input($name, $params);
	}
	public static function reset( $name, $params = array('attrs' => '', 'value' => '') ) {
		$params['type'] = 'reset';
		self::input($name, $params);
	}
	public static function password( $name, $params = array('attrs' => '', 'value' => '') ) {
		$params['type'] = 'password';
		self::input($name, $params);
	}
	public static function hidden( $name, $params = array('attrs' => '', 'value' => '') ) {
		$params['type'] = 'hidden';
		self::input($name, $params);
	}
	public static function number( $name, $params = array('attrs' => '', 'value' => '') ) {
		$params['type'] = 'number';
		self::input($name, $params);
	}
	public static function checkbox( $name, $params = array('attrs' => '', 'value' => '', 'checked' => '') ) {
		$params['type'] = 'checkbox';
		$params['checked'] = isset($params['checked']) && $params['checked'] ? ' checked' : '';
		if ( !isset($params['value']) || null == $params['value'] ) {
			$params['value'] = 1;
		}
		if (!isset($params['attrs'])) {
			$params['attrs'] = '';
		}
		$params['attrs'] .= $params['checked'];
		self::input($name, $params);
	}
	public static function checkboxToggle( $name, $params = array('attrs' => '', 'value' => '', 'checked' => '') ) {
		$params['type'] = 'checkbox';
		$params['checked'] = isset($params['checked']) && $params['checked'] ? 'checked' : '';
		if ( !isset($params['value']) || ( null === $params['value'] ) ) {
			$params['value'] = 1;
		}
		$id = ( empty($params['id']) ? self::nameToClassId($name) . mt_rand(9, 9999) : $params['id'] );
		$params['attrs'] = 'id="' . esc_attr($id) . '" class="toggle" ' . ( isset($params['attrs']) ? $params['attrs'] . ' ' : '' ) . $params['checked'];
		
		self::input($name, $params);
		echo '<label for="' . esc_attr($id) . '" class="toggle"></label>';
	}
	public static function checkboxlist( $name, $params = array('options' => array(), 'attrs' => '', 'checked' => '', 'delim' => '<br />', 'usetable' => 5), $delim = '<br />' ) {
		if (!strpos($name, '[]')) {
			$name .= '[]';
		}
		$i = 0;
		if ($params['options']) {
			if (!isset($params['delim'])) {
				$params['delim'] = $delim;
			}
			if (!empty($params['usetable'])) {
				echo '<table><tr>';
			}
			foreach ($params['options'] as $v) {
				if (!empty($params['usetable'])) {
					if ( ( 0 != $i ) && ( 0 == $i%$params['usetable'] ) ) {
						echo '</tr><tr>';
					}
					echo '<td>';
				}
				self::checkboxToggle($name, array(
					'attrs' => !empty($params['attrs']),
					'value' => empty($v['value']) ? $v['id'] : $v['value'],
					'checked' => $v['checked'],
					'id' => $v['id'],
				));
				echo '&nbsp;';
				if (!empty($v['text'])) {
					self::echoEscapedHtml($v['text']);
				}
				if (!empty($params['delim'])) {
					self::echoEscapedHtml($params['delim']);
				}
				if (!empty($params['usetable'])) {
					echo '</td>';
				}
				$i++;
			}
			if (!empty($params['usetable'])) {
				echo '</tr></table>';
			}
		}
	}
	public static function submit( $name, $params = array('attrs' => '', 'value' => '') ) {
		$params['type'] = 'submit';
		self::input($name, $params);
	}
	public static function img( $src, $usePlugPath = 1, $params = array('width' => '', 'height' => '', 'attrs' => '') ) {
		if ($usePlugPath) {
			$src = AFSW_IMG_PATH . $src;
		}
		echo '<img src="' . esc_url($src) . '" '
				. ( isset($params['width']) ? 'width="' . esc_attr($params['width']) . '"' : '' )
				. ' '
				. ( isset($params['height']) ? 'height="' . esc_attr($params['height']) . '"' : '' )
				. ' ';
		if (!empty($params['attrs'])) {
			self::echoEscapedHtml($params['attrs']);
		}
		echo ' />';
	}
	public static function selectbox( $name, $params = array('attrs' => '', 'options' => array(), 'value' => '') ) {
		$attrs = UtilsAfsw::getArrayValue($params, 'attrs');
		if (UtilsAfsw::getArrayValue($params, 'required', false)) {
			$attrs .= ' required ';
		}
		echo '<select name="' . esc_attr($name) . '" ';
		if (!empty($attrs)) {
			self::echoEscapedHtml($attrs);
		}
		echo '>';
		$default = UtilsAfsw::getArrayValue($params, 'default');
		if (!empty($default)) {
			echo '<option value="">' . esc_html($default) . '</option>';
		}
		$existValue = isset($params['value']);
		$keyValue = UtilsAfsw::getArrayValue($params, 'key') == 'value';
		$add = isset($params['add']) ? $params['add'] : '';
		if (!empty($params['options'])) {
			foreach ($params['options'] as $k => $v) {
				$key = ( $keyValue ? $v : $k ) . $add;
				$a = '';
				if (is_array($v)) {
					$a = isset($v['attrs']) ? $v['attrs'] : '';
					$v = isset($v['label']) ? $v['label'] : '???';
				}
				echo '<option value="' . esc_attr($key) . '"' . ( $existValue && $key == $params['value'] ? ' selected="true"' : '' );
				if (!empty($a)) {
					self::echoEscapedHtml($a);
				}
				echo '>' . esc_html($v) . '</option>';
			}
		}
		echo '</select>';
	}
	public static function selectlist( $name, $params = array('attrs' => '', 'size' => 5, 'class' => '', 'options' => array(), 'value' => '') ) {
		if (!strpos($name, '[]')) {
			$name .= '[]';
		}
		if ( !isset($params['size']) || !is_numeric($params['size']) || ( '' == $params['size'] ) ) {
			$params['size'] = 5;
		}
		$params['class'] = isset($params['class']) ? $params['class'] : '';
		$params['attrs'] = isset($params['attrs']) ? $params['attrs'] : '';
		$params['attrs'] .= self::_dataToAttrs($params);

		echo '<select multiple="multiple" class="woobewoo-chosen ' . esc_attr($params['class']) . '" size="' . esc_attr($params['size']) . '" name="' . esc_attr($name) . '" ';
		if (!empty($params['attrs'])) {
			self::echoEscapedHtml($params['attrs']);
		}
		echo '>';

		$params['value'] = isset($params['value']) ? (array) $params['value'] : array();
		$keyValue = UtilsAfsw::getArrayValue($params, 'key') == 'value';
		$options = $params['options'];
		if (!empty($params['value'])) {
			foreach ($params['value'] as $v) {
				$k = ( $keyValue ? array_search($v, $options) : ( isset($options[$v]) ? $v : false ) );
				if (false !== $k) {
					echo '<option value="' . esc_attr($v) . '" selected>' . esc_html($options[$k]) . '</option>';
					unset($options[$k]);
				}
			}
		}
		if (!empty($options)) {
			foreach ($options as $k => $v) {
				$key = ( $keyValue ? $v : $k );
				echo '<option value="' . esc_attr($key) . '">' . esc_html($v) . '</option>';
			}
		}
		echo '</select>';
	}
	public static function file( $name, $params = array() ) {
		$id = ( empty($params['id']) ? self::nameToClassId($name) . mt_rand(9, 9999) : $params['id'] );
		echo '<div class="woobewoo-inputfile">
			<input type="file" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '">
			<label for="' . esc_attr($id) . '">
				<div class="woobewoo-namefile"></div>
				<div class="button buttonfile">
					<i class="fa fa-upload" aria-hidden="true"></i>' . esc_html__('Select file', 'advanced-fuzzy-search') . '
				</div>
			</label>
		</div>';
	}
	public static function button( $params = array('attrs' => '', 'value' => '') ) {
		echo '<button ';
		if (!empty($params['attrs'])) {
			self::echoEscapedHtml($params['attrs']);
		}
		echo '>' . esc_html($params['value']) . '</button>';
	}
	public static function buttonA( $params = array('attrs' => '', 'value' => '') ) {
		echo '<a href="#" ';
		if (!empty($params['attrs'])) {
			self::echoEscapedHtml($params['attrs']);
		}
		echo '>' . esc_html($params['value']) . '</a>';
	}
	public static function inputButton( $params = array('attrs' => '', 'value' => '') ) {
		if (!is_array($params)) {
			$params = array();
		}
		$params['type'] = 'button';
		self::input('', $params);
	}
	public static function radiobuttons( $name, $params = array('attrs' => '', 'options' => array(), 'value' => '', '') ) {
		if (isset($params['options']) && is_array($params['options']) && !empty($params['options'])) {
			//$params['labeled'] = isset($params['labeled']) ? $params['labeled'] : false;
			$params['attrs'] = isset($params['attrs']) ? $params['attrs'] : '';
			$params['no_br'] = isset($params['no_br']) ? $params['no_br'] : false;
			foreach ($params['options'] as $key => $val) {
				$checked = ( $key == $params['value'] ) ? 'checked' : '';
				self::input($name, array('attrs' => $params['attrs'] . ' ' . $checked, 'type' => 'radio', 'value' => $key));
				echo '<label>' . esc_html($val) . '</label>';
				if (!$params['no_br']) {
					echo '<br />';
				}
			}
		}
	}
	public static function radiobutton( $name, $params = array('attrs' => '', 'value' => '', 'checked' => '') ) {
		$params['type'] = 'radio';
		$params['attrs'] = isset($params['attrs']) ? $params['attrs'] : '';
		if (isset($params['checked']) && $params['checked']) {
			$params['attrs'] .= ' checked';
		}
		self::input($name, $params);
	}
	public static function formStart( $name, $params = array('action' => '', 'method' => 'GET', 'attrs' => '', 'hideMethodInside' => false) ) {
		$params['attrs'] = isset($params['attrs']) ? $params['attrs'] : '';
		$params['action'] = isset($params['action']) ? $params['action'] : '';
		$params['method'] = isset($params['method']) ? $params['method'] : 'GET';
		echo '<form name="' . esc_attr($name) . '" action="' . esc_attr($params['action']) . '" method="' . esc_attr($params['method']) . '" ';
		if (!empty($params['attrs'])) {
			self::echoEscapedHtml($params['attrs']);
		}
		echo '>';

		if (isset($params['hideMethodInside']) && $params['hideMethodInside']) {
			self::hidden('method', array('value' => $params['method']));
		}
	}
	public static function formEnd() {
		echo '</form>';
	}
	public static function colorPicker( $name, $params = array('value' => '', 'label' => '') ) {
		$value = isset($params['value']) ? $params['value'] : '';
		$label = isset($params['label']) ? $params['label'] : '';
		echo '<div class="woobewoo-color-picker">
			<div class="woobewoo-color-wrapper">
				<div class="woobewoo-color-preview"></div>
			</div>
			<input type="text" class="woobewoo-color-input" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '"';
		if (!empty($params['attrs'])) {
			self::echoEscapedHtml($params['attrs']);
		}
		echo '>';
		if (!empty($label)) {
			echo '<label class="right-label">' . esc_html($label) . '</label>';
		}
		echo '</div>';
	}
	public static function nonceForAction( $action ) {
		self::hidden('_wpnonce', array('value' => wp_create_nonce(strtolower($action))));
	}
	public static function selectIcon( $name, $params ) {
		echo '<div class="button chooseLoaderIcon">' . esc_html__('Choose Icon', 'advanced-fuzzy-search') . '</div>';
	}
	public static function proOptionLink( $url = '', $label = '' ) {
		echo '<a href="' . esc_url( empty($url) ? UriAfsw::generatePluginLink() : $url ) . '" target="_blank" class="woobewoo-prolink">' . ( empty($label) ? esc_html__('PRO', 'advanced-fuzzy-search') : esc_html($label) ) . '</a>';
	}
	public static function selectFontList( $name, $params = array('attrs' => '', 'value' => '') ) {
		$attrs = UtilsAfsw::getArrayValue($params, 'attrs');
		$value = UtilsAfsw::getArrayValue($params, 'value');

		echo '<select name="' . esc_attr($name) . '" ';
		if (!empty($params['attrs'])) {
			self::echoEscapedHtml($params['attrs']);
		}
		echo '><option value="">' . esc_html__('Default', 'advanced-fuzzy-search') . '</option>';

		$standart = DispatcherAfsw::applyFilters('getFontsList', array(), 'standart');
		$fonts = array_merge($standart, DispatcherAfsw::applyFilters('getFontsList', array(), ''));
		natsort($fonts);
		
		foreach ($fonts as $font) {
			echo '<option value="' . esc_attr($font) . '" data-standart="' . ( in_array($font, $standart) ? 1 : 0 ) . '" ' . ( $font == $value ? ' selected="true"' : '' ) . '>' . esc_html($font) . '</option>';
		}
		echo '</select>';
	}
}
