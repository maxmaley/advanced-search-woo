<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
add_filter( 'get_search_form', 'replaceSearchStorefrontAfsw', 99999999 );
if (!function_exists('storefront_product_search')) {
	function storefront_product_search() {
	}
}
remove_action('storefront_header', 'storefront_product_search', 40);

/*add_action('get_template_part', 'replaceContentNoneTemplateAfsw', 999, 5);
if (!function_exists('replaceContentNoneTemplateAfsw')) {
	function replaceContentNoneTemplateAfsw($slug, $name, $templates, $args) {
		if (is_search() && 'content' == $slug && 'none' == $name) {
			array_unshift($templates, FrameAfsw::_()->getModule('integrations')->getModDir() . 'files' . DS . 'storefront-content-none.php');
			error_log('$templates'.json_encode($templates));
		
		}
	}
}*/

if (!function_exists('replaceSearchStorefrontAfsw')) {
	function replaceSearchStorefrontAfsw() {
		global $afswThemeReplaceField;
		$s = do_shortcode( '[afsw-fields id=' . $afswThemeReplaceField . ' post_type="product"]' );
		
		print '<div class="site-search afsw-field-storefront">';
		HtmlAfsw::echoEscapedHtml($s);
		print '</div>';
		$js = 'jQuery(document).ready(function () {
		setTimeout(function () {
			jQuery(\'.storefront-handheld-footer-bar .search\').on(\'click\',function() {
				var $this = jQuery(this);
				if (!$this.hasClass(\'afsw-inited\')) {
					var searchWrap = jQuery(\'.afsw-field-storefront\');
					if (searchWrap.length) {
						$wrapper = searchWrap.find(\'.afsw-search-wrapper\');
						if ($wrapper.length == 0) {
							var id = searchWrap.attr(\'data-afsw-base\');
							if (id) $wrapper = jQuery(\'#\'+id);
						}
						if ($wrapper.length) {
							
							window.afswFields.initFloatingField($wrapper, $this, true, true);
							$this.trigger(\'click\');
						}
					}
				}
				$this.addClass(\'afsw-inited\');
			});
		}, 500);
	});';
		FrameAfsw::_()->printInlineJs($js);
		FrameAfsw::_()->printInlineCss('form.search-form{display:none;}');
	}
}
add_action('storefront_header', 'replaceSearchStorefrontAfsw', 40);
